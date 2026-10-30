"""NOAA solar position (~0.1 deg) and sunrise/sunset for the nw3 station (Hampstead, London).

Input times are naive UTC datetimes. Port of rwcwx/sun/solar.py with London coordinates."""
import math
from datetime import datetime, timedelta

LAT, LON = 51.556, -0.177
SUN_CENTRE_EL = -0.833   # sunrise/sunset convention (upper limb + refraction), same as rwc


def solar_pos(t, lat=LAT, lon=LON):
    r = math.radians
    jd = (t - datetime(2000, 1, 1, 12)).total_seconds() / 86400.0 + 2451545.0
    jc = (jd - 2451545.0) / 36525.0
    gml = (280.46646 + jc * (36000.76983 + jc * 0.0003032)) % 360
    gma = 357.52911 + jc * (35999.05029 - 0.0001537 * jc)
    ecc = 0.016708634 - jc * (0.000042037 + 0.0000001267 * jc)
    ceq = (math.sin(r(gma)) * (1.914602 - jc * (0.004817 + 0.000014 * jc)) + math.sin(r(2 * gma)) * (0.019993 - 0.000101 * jc)
           + math.sin(r(3 * gma)) * 0.000289)
    sal = gml + ceq - 0.00569 - 0.00478 * math.sin(r(125.04 - 1934.136 * jc))
    moe = 23 + (26 + (21.448 - jc * (46.815 + jc * (0.00059 - jc * 0.001813))) / 60) / 60
    oc = moe + 0.00256 * math.cos(r(125.04 - 1934.136 * jc))
    dec = math.degrees(math.asin(math.sin(r(oc)) * math.sin(r(sal))))
    y = math.tan(r(oc / 2)) ** 2
    eot = 4 * math.degrees(y * math.sin(2 * r(gml)) - 2 * ecc * math.sin(r(gma)) + 4 * ecc * y * math.sin(r(gma)) * math.cos(2 * r(gml))
                           - 0.5 * y * y * math.sin(4 * r(gml)) - 1.25 * ecc * ecc * math.sin(2 * r(gma)))
    mins = t.hour * 60 + t.minute + t.second / 60
    tst = (mins + eot + 4 * lon) % 1440
    ha = tst / 4 + 180 if tst / 4 < 0 else tst / 4 - 180
    cz = math.sin(r(lat)) * math.sin(r(dec)) + math.cos(r(lat)) * math.cos(r(dec)) * math.cos(r(ha))
    zen = math.degrees(math.acos(max(-1., min(1., cz))))
    el = 90 - zen
    sz = math.sin(r(zen))
    ca = ((math.sin(r(lat)) * math.cos(r(zen))) - math.sin(r(dec))) / (math.cos(r(lat)) * sz) if sz > 1e-9 else 1.0
    az = math.degrees(math.acos(max(-1., min(1., ca))))
    az = (az + 180) % 360 if ha > 0 else (540 - az) % 360
    return el, az


def _cross(t0, t1, rising):
    """Bisect the SUN_CENTRE_EL crossing between t0 and t1 (naive UTC) to ~1 s."""
    for _ in range(30):
        mid = t0 + (t1 - t0) / 2
        above = solar_pos(mid)[0] > SUN_CENTRE_EL
        if above == rising:
            t1 = mid
        else:
            t0 = mid
    return t0 + (t1 - t0) / 2


def sun_times_utc(day_start_utc, day_end_utc):
    """(sunrise, sunset) naive UTC within [day_start, day_end) of a local day; None if no crossing."""
    step = timedelta(minutes=10)
    sr = ss = None
    t = day_start_utc
    prev = solar_pos(t)[0] > SUN_CENTRE_EL
    while t < day_end_utc:
        n = min(t + step, day_end_utc)
        cur = solar_pos(n)[0] > SUN_CENTRE_EL
        if cur and not prev and sr is None:
            sr = _cross(t, n, True)
        if prev and not cur:
            ss = _cross(t, n, False)
        prev, t = cur, n
    return sr, ss
