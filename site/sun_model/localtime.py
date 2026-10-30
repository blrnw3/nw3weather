"""UK local <-> naive UTC via the system tz database (TZ=Europe/London)."""
import os
import time
from datetime import datetime, timedelta

os.environ.setdefault("NW3_SUN_TZ", "Europe/London")


def _with_tz(fn):
    old = os.environ.get("TZ")
    os.environ["TZ"] = os.environ["NW3_SUN_TZ"]
    time.tzset()
    try:
        return fn()
    finally:
        if old is None:
            del os.environ["TZ"]
        else:
            os.environ["TZ"] = old
        time.tzset()


def local_to_utc(dt):
    """Naive UK local -> naive UTC. Ambiguous autumn hour resolves to the first (BST) instance."""
    ts = _with_tz(lambda: time.mktime(dt.timetuple()[:8] + (-1,)))
    return datetime(1970, 1, 1) + timedelta(seconds=ts)


def utc_to_local(dt):
    ts = (dt - datetime(1970, 1, 1)).total_seconds()
    return _with_tz(lambda: datetime.fromtimestamp(ts))


def local_day_bounds_utc(d):
    a = local_to_utc(datetime(d.year, d.month, d.day))
    nd = d + timedelta(days=1)
    return a, local_to_utc(datetime(nd.year, nd.month, nd.day))
