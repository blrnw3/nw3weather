import sys,json,os,gc
sys.path.insert(0,'/workspace/nw3weather/site')
from datetime import datetime
from sun_model.features import feats,load_small
from sun_model.solar import solar_pos
from sun_model import classify as C
d=datetime.strptime(sys.argv[1],'%Y-%m-%d').date()
out='feats/%s.json'%d.strftime('%Y%m%d')
if os.path.exists(out): sys.exit()
F=None
for base in ('/workspace/nw3sun/valdays','/workspace/nw3sun/evaltime/frames'):
    fr=C.list_frames(base,d)
    if fr: F=fr; break
res={}
prev=None
for t in sorted(F or {}):
    try: f=feats(load_small(F[t]))
    except Exception: continue
    f['el'],f['az']=solar_pos(t)
    g=[round(f[k],4) for k in C.GRID]
    f['_stale']=prev is not None and max(abs(a-b) for a,b in zip(g,prev))<C.STALE_EPS; prev=g
    f['_path']=F[t]; res[t.strftime('%Y%m%d%H%M')]=f
json.dump(res,open(out,'w'))
