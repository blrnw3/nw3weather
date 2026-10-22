import sys,json,os
sys.path.insert(0,'/workspace/nw3weather/site')
from sun_model.features import veg_feats,load_small
for day in sys.argv[1:]:
    out='/workspace/nw3sun/evaltime/feats3/%s.json'%day
    if os.path.exists(out): continue
    d=json.load(open('/workspace/nw3sun/evaltime/feats/%s.json'%day)); mo=int(day[4:6]); r={}
    for k,f in d.items():
        try: r[k]=veg_feats(load_small(f['_path']),mo)
        except Exception: pass
    json.dump(r,open(out,'w'))
