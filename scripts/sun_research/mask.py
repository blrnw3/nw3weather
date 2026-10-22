import json,glob,random,numpy as np,sys
sys.path.insert(0,'/workspace/nw3weather/site')
from sun_model.features import load_small
random.seed(1)
acc={}
for f in sorted(glob.glob('/workspace/nw3sun/evaltime/feats/*.json')):
    day=f.split('/')[-1][:8]; mo=int(day[4:6])
    d=json.load(open(f)); ks=[k for k in d if d[k]['el']>15]
    for k in random.sample(ks,min(4,len(ks))):
        a=np.asarray(load_small(d[k]['_path']),np.float32)/255.
        R,G,B=a[...,0],a[...,1],a[...,2]; L=.299*R+.587*G+.114*B
        sky=((B>=R)&(B>=G-0.02)&(L>0.35))|(L>0.75)
        acc.setdefault(mo,[]).append((sky,L,G-(R+B)/2))
out={}
for mo in range(1,13):
    s=np.mean([x[0] for x in acc[mo]],0); out[mo]=s
    print(mo,len(acc[mo]),'veg frac(sky<0.15) %.3f'%(s<0.15).mean())
np.save('skyfrac_by_month.npy',np.array([out[m] for m in range(1,13)]))
