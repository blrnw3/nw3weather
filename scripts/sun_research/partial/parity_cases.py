"""Random partial-day cases + Python expected results -> /tmp/pt_cases.json; then: php parity_test.php. Run from repo root."""
import sys, json, random
sys.path.insert(0, "site")
from sun_model.rules import partial_total
random.seed(1); cases = []
for c in range(300):
    n = random.randint(200, 960); rows = []
    for i in range(n):
        w = 1.0 if 0 < i < n - 1 else random.random(); pw = w if random.random() < 0.9 else 0.0
        rows.append([w, pw, random.choice([0, 1])])
    for _ in range(random.randint(0, 4)):
        a = random.randint(0, n - 1); b = min(n, a + random.randint(1, 300))
        for i in range(a, b): rows[i][2] = None
    if random.random() < 0.4:
        k = random.randint(1, n // 2)
        for i in range(n - k, n): rows[i][2] = None
    cases.append(dict(rows=rows, exp=partial_total([tuple(r) for r in rows])))
json.dump(cases, open("/tmp/pt_cases.json", "w"))
