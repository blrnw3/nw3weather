#!/usr/bin/env python3
"""Regenerate site/config/azure_cidrs.php and the Apache Require-not-ip snippet.

Source: Microsoft Azure Service Tags (Public cloud), tag AzureCloud, minus
Bing/Microsoft search-crawler ranges from bingbot.json plus the well-known
40.77.0.0/16, 157.55.0.0/16 and 207.46.0.0/16 prefixes.

Usage:
  python3 scripts/generate_azure_blocklist.py
  python3 scripts/generate_azure_blocklist.py --service-tags /path/ServiceTags_Public_YYYYMMDD.json
"""
from __future__ import print_function

import argparse
import datetime
import ipaddress
import json
import os
import sys
import urllib.request

REPO = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
DEFAULT_OUT_PHP = os.path.join(REPO, "site", "config", "azure_cidrs.php")
DEFAULT_OUT_CONF = os.path.join(REPO, "scripts", "hardening", "nw3-azure-block.conf")
DEFAULT_OUT_INC = os.path.join(REPO, "scripts", "hardening", "nw3-azure-block.inc")

# Official download GUID for "Azure IP Ranges and Service Tags – Public Cloud"
# (Microsoft download id 56519). Filenames are ServiceTags_Public_YYYYMMDD.json.
MS_DOWNLOAD_BASE = (
    "https://download.microsoft.com/download/7/1/D/"
    "71D86715-5596-4529-9B13-DA13A5DE5B63/"
)
BINGBOT_URL = "https://www.bing.com/toolbox/bingbot.json"

# Task-requested Bing/Microsoft crawler supernets, in addition to bingbot.json.
BING_SUPERNETS = [
    "40.77.0.0/16",
    "157.55.0.0/16",
    "207.46.0.0/16",
]


def log(msg):
    print(msg, file=sys.stderr)


def fetch(url, dest=None, timeout=60):
    log("Fetching %s" % url)
    req = urllib.request.Request(url, headers={"User-Agent": "nw3weather-azure-blocklist/1.0"})
    with urllib.request.urlopen(req, timeout=timeout) as resp:
        data = resp.read()
    if dest:
        with open(dest, "wb") as f:
            f.write(data)
    return data


def discover_service_tags(explicit_path, cache_dir):
    if explicit_path:
        with open(explicit_path, "rb") as f:
            raw = f.read()
        return raw, os.path.basename(explicit_path), os.path.abspath(explicit_path)

    # Walk recent dates; tags are typically published weekly (often Monday).
    today = datetime.date.today()
    last_err = None
    for delta in range(0, 21):
        d = today - datetime.timedelta(days=delta)
        name = "ServiceTags_Public_%s.json" % d.strftime("%Y%m%d")
        url = MS_DOWNLOAD_BASE + name
        try:
            raw = fetch(url)
            # A 200 can still be an error page; require JSON object with values.
            json.loads(raw)
            cached = os.path.join(cache_dir, name) if cache_dir else None
            if cached:
                with open(cached, "wb") as f:
                    f.write(raw)
            return raw, name, url
        except Exception as e:
            last_err = e
            continue
    raise SystemExit("Could not download Service Tags JSON from %s: %s" % (MS_DOWNLOAD_BASE, last_err))


def parse_bingbot(raw):
    data = json.loads(raw.decode("utf-8") if isinstance(raw, bytes) else raw)
    prefixes = []
    for item in data.get("prefixes", []):
        for key in ("ipv4Prefix", "ipv6Prefix", "prefix"):
            if item.get(key):
                prefixes.append(item[key])
    return prefixes, data.get("creationTime")


def nets_of(cidrs):
    out = []
    for c in cidrs:
        try:
            out.append(ipaddress.ip_network(c, strict=False))
        except ValueError as e:
            log("Skipping bad CIDR %r: %s" % (c, e))
    return out


def subtract_holes(bases, holes):
    """Return address ranges in bases that are not covered by any hole."""
    remaining = list(bases)
    for hole in holes:
        nxt = []
        for net in remaining:
            if net.version != hole.version:
                nxt.append(net)
                continue
            if net.network_address > hole.broadcast_address or net.broadcast_address < hole.network_address:
                nxt.append(net)
                continue
            try:
                nxt.extend(net.address_exclude(hole))
            except ValueError:
                # hole is not a subnet of net; handle overlap by expanding to
                # the intersection and excluding via covering.
                if hole.supernet_of(net) or hole == net:
                    continue
                # Partial overlap: convert to collapse of leftover addresses
                # by enumerating via cidr difference of overlapping intersection.
                # Use the generic range-split below via ip_network of overlap.
                start = max(int(net.network_address), int(hole.network_address))
                end = min(int(net.broadcast_address), int(hole.broadcast_address))
                leftover = []
                if int(net.network_address) < start:
                    leftover.extend(ipaddress.summarize_address_range(
                        net.network_address,
                        ipaddress.ip_address(start - 1),
                    ))
                if end < int(net.broadcast_address):
                    leftover.extend(ipaddress.summarize_address_range(
                        ipaddress.ip_address(end + 1),
                        net.broadcast_address,
                    ))
                nxt.extend(leftover)
        remaining = nxt
    v4 = [n for n in remaining if n.version == 4]
    v6 = [n for n in remaining if n.version == 6]
    return (
        list(ipaddress.collapse_addresses(v4)),
        list(ipaddress.collapse_addresses(v6)),
    )


def php_escape(s):
    return s.replace("\\", "\\\\").replace("'", "\\'")


def write_php(path, meta, v4, v6):
    lines = [
        "<?php",
        "/**",
        " * Generated Azure public-cloud CIDRs to block at the application layer.",
        " * Do not edit by hand. Regenerate with:",
        " *   python3 scripts/generate_azure_blocklist.py",
        " *",
        " * Source: Microsoft Service Tags Public, tag AzureCloud, changeNumber %s," % meta["changeNumber"],
        " * file %s" % meta["source_file"],
        " * %s" % meta["source_url"],
        " * Bing/Microsoft crawler ranges excluded (bingbot.json + 40.77/16, 157.55/16, 207.46/16).",
        " * DigitalOcean uptime probes are not in AzureCloud and are not blocked.",
        " */",
        "return array(",
        "\t'meta' => array(",
        "\t\t'source_file' => '%s'," % php_escape(meta["source_file"]),
        "\t\t'source_url' => '%s'," % php_escape(meta["source_url"]),
        "\t\t'changeNumber' => %s," % int(meta["changeNumber"]),
        "\t\t'cloud' => 'Public',",
        "\t\t'generated_at' => '%s'," % php_escape(meta["generated_at"]),
        "\t\t'bingbot_creationTime' => '%s'," % php_escape(str(meta.get("bingbot_creationTime") or "")),
        "\t\t'azurecloud_v4' => %d," % meta["azurecloud_v4"],
        "\t\t'azurecloud_v6' => %d," % meta["azurecloud_v6"],
        "\t\t'excluded_bing_cidrs' => %d," % meta["excluded_bing_cidrs"],
        "\t\t'blocked_v4' => %d," % len(v4),
        "\t\t'blocked_v6' => %d," % len(v6),
        "\t\t'blocked_total' => %d," % (len(v4) + len(v6)),
        "\t),",
        "\t'v4' => array(",
    ]
    for n in v4:
        lines.append("\t\t'%s'," % n)
    lines.append("\t),")
    lines.append("\t'v6' => array(")
    for n in v6:
        lines.append("\t\t'%s'," % n)
    lines.append("\t),")
    lines.append(");")
    lines.append("")
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, "w") as f:
        f.write("\n".join(lines))
    log("Wrote %s (%d v4, %d v6)" % (path, len(v4), len(v6)))


def write_apache(conf_path, inc_path, meta, v4, v6):
    header_inc = (
        "# Generated Require-not-ip lines for Azure public-cloud ranges.\n"
        "# Source: %s (AzureCloud, changeNumber %s).\n"
        "# Bingbot / Microsoft search crawlers excluded.\n"
        "# Do not apply blindly: include these lines inside an existing\n"
        "# Apache 2.4 <RequireAll> that already has `Require all granted`.\n"
        "# A bare `Require not ip` list without `Require all granted` will deny everyone.\n"
        "#\n" % (meta["source_file"], meta["changeNumber"])
    )
    inc_lines = [header_inc]
    for n in list(v4) + list(v6):
        inc_lines.append("    Require not ip %s\n" % n)
    os.makedirs(os.path.dirname(inc_path), exist_ok=True)
    with open(inc_path, "w") as f:
        f.writelines(inc_lines)

    conf = """# nw3-azure-block.conf
# Apache-level equivalent of site/AzureBlock.php (Azure public-cloud CIDRs,
# Bingbot excluded). PREPARE ONLY — do not apply without Ben's review.
#
# Generated: {generated_at}
# Source: {source_file} (Microsoft Service Tags Public, AzureCloud, changeNumber {changeNumber})
# {source_url}
# Blocked CIDRs: {blocked_total} ({blocked_v4} IPv4, {blocked_v6} IPv6)
# Bing exclusions: official bingbot.json plus 40.77.0.0/16, 157.55.0.0/16, 207.46.0.0/16.
#
# IMPORTANT:
#  - This file is NOT in the live docroot (it lives in the repo under scripts/hardening/).
#  - `Require not ip` must sit inside <RequireAll> together with `Require all granted`.
#    A standalone `Require not ip` list will deny all clients.
#  - Review AuthMerging / existing <Directory /var/www/html> Require rules (geofence,
#    cam aliases) before enabling, then configtest, then reload. Do not restart first.
#  - The application-level block in AzureBlock.php is the primary defence and can
#    ship without this Apache snippet.
#
# Commands on nw3weather-v2 (after copying the file from a checkout of this PR):
#
#   sudo install -m 644 scripts/hardening/nw3-azure-block.conf \\
#     /etc/apache2/conf-available/nw3-azure-block.conf
#   sudo apache2ctl configtest
#   sudo a2enconf nw3-azure-block
#   sudo apache2ctl configtest
#   sudo systemctl reload apache2
#
# To roll back:
#
#   sudo a2disconf nw3-azure-block
#   sudo apache2ctl configtest
#   sudo systemctl reload apache2
#
# If configtest or the vhost authz merge looks wrong, do not enable this conf;
# instead copy the `Require not ip` lines from nw3-azure-block.inc into the
# existing <RequireAll> block for /var/www/html.

<IfModule mod_authz_core.c>
  <Directory /var/www/html>
    <RequireAll>
      Require all granted
""".format(
        generated_at=meta["generated_at"],
        source_file=meta["source_file"],
        changeNumber=meta["changeNumber"],
        source_url=meta["source_url"],
        blocked_total=len(v4) + len(v6),
        blocked_v4=len(v4),
        blocked_v6=len(v6),
    )
    parts = [conf]
    for n in list(v4) + list(v6):
        parts.append("      Require not ip %s\n" % n)
    parts.append("    </RequireAll>\n  </Directory>\n</IfModule>\n")
    with open(conf_path, "w") as f:
        f.write("".join(parts))
    log("Wrote %s and %s" % (conf_path, inc_path))


def main():
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--service-tags", help="Path to a ServiceTags_Public_*.json file")
    ap.add_argument("--bingbot", help="Path to bingbot.json")
    ap.add_argument("--cache-dir", default="/tmp/azure-ranges")
    ap.add_argument("--out-php", default=DEFAULT_OUT_PHP)
    ap.add_argument("--out-conf", default=DEFAULT_OUT_CONF)
    ap.add_argument("--out-inc", default=DEFAULT_OUT_INC)
    args = ap.parse_args()

    os.makedirs(args.cache_dir, exist_ok=True)
    raw, source_file, source_url = discover_service_tags(args.service_tags, args.cache_dir)
    tags = json.loads(raw.decode("utf-8"))
    azure = None
    for value in tags.get("values", []):
        if value.get("name") == "AzureCloud":
            azure = value["properties"]["addressPrefixes"]
            break
    if not azure:
        raise SystemExit("AzureCloud tag not found in %s" % source_file)

    if args.bingbot:
        with open(args.bingbot, "rb") as f:
            bing_raw = f.read()
    else:
        bing_path = os.path.join(args.cache_dir, "bingbot.json")
        bing_raw = fetch(BINGBOT_URL)
        with open(bing_path, "wb") as f:
            f.write(bing_raw)
    bing_prefixes, bing_ctime = parse_bingbot(bing_raw)
    holes = nets_of(bing_prefixes + BING_SUPERNETS)
    # Unique holes
    holes = list({n.with_prefixlen: n for n in holes}.values())

    azure_nets = nets_of(azure)
    azure_v4 = [n for n in azure_nets if n.version == 4]
    azure_v6 = [n for n in azure_nets if n.version == 6]
    v4, v6 = subtract_holes(azure_nets, holes)

    meta = {
        "source_file": source_file,
        "source_url": source_url if source_url.startswith("http") else MS_DOWNLOAD_BASE + source_file,
        "changeNumber": tags.get("changeNumber"),
        "generated_at": datetime.datetime.now(datetime.timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ"),
        "bingbot_creationTime": bing_ctime,
        "azurecloud_v4": len(azure_v4),
        "azurecloud_v6": len(azure_v6),
        "excluded_bing_cidrs": len(holes),
    }
    write_php(args.out_php, meta, v4, v6)
    write_apache(args.out_conf, args.out_inc, meta, v4, v6)
    print(json.dumps({
        "source_file": source_file,
        "changeNumber": tags.get("changeNumber"),
        "azurecloud_v4": len(azure_v4),
        "azurecloud_v6": len(azure_v6),
        "blocked_v4": len(v4),
        "blocked_v6": len(v6),
        "blocked_total": len(v4) + len(v6),
        "excluded_bing_cidrs": len(holes),
    }, indent=2))


if __name__ == "__main__":
    main()
