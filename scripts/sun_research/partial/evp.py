import sys,json,csv,numpy as np
sys.path.insert(0,'/workspace/nw3sun/v4'); import ev4 as E
from sun_model import classify as C
from sun_model.rules import summary
def old_roll(g):
    s=summary(g); cov=s['coverage']; h=s['sun_min']/60.
    if s['daylight_min']<=0 or cov<0.5: return None,cov
    return round(h/cov if cov<0.9 else h,2),cov
sp=json.load(open('/workspace/nw3weather/site/sun_model/models/nw3_sunny_lr_v4.json')); full=E.Reg4(sp['parts'],25,0.45)
fold={}
for fo in E.NFOLDS:
    m=E.Reg4(E.fit4(E.rows(exclude=fo,wnew=3.0),25,0.1,False),25,0.45)
    for d in fo: fold[d]=m
rows=[]
for d in E.days:
    if d in E.ERR: continue
    m=fold.get(d,full); m.day=d
    r=E.day_total(d,m,0.45,False,True)
    if not r: continue
    new,grid,_=r; o,oc=old_roll(grid)
    st='clean' if d in E.CLEAN4 else 'newlab_cv' if d in E.NEWDAYS else 'tune' if d in E.TUNE else 'labelled'
    rows.append(dict(date=d,set=st,datm=E.t[d],old=o,new=new['sun_h'],old_cov=round(oc,3),new_cov=new['coverage'],measured=new['measured_h'],filled=new['filled_h'],unknown=new['unknown_h'],partial=new['partial'],scaled=new['scaled']))
with open('partial_eval.csv','w') as f:
    w=csv.DictWriter(f,list(rows[0])); w.writeheader(); w.writerows(rows)
def s(rs,k):
    rs=[r for r in rs if r[k] is not None]; e=np.array([r[k]-r['datm'] for r in rs])
    return 'n=%d MAE %.2f bias %+.2f'%(len(rs),abs(e).mean(),e.mean()) if len(rs) else 'n=0'
for nm,sel in (('clean',lambda r:r['set']=='clean'),('newlab CV 28',lambda r:r['set']=='newlab_cv'),('all',lambda r:True)):
    rs=[r for r in rows if sel(r)]; print('%-14s old %s | new %s'%(nm,s(rs,'old'),s(rs,'new')))
P=[r for r in rows if r['old_cov']<0.9 or r['new_cov']<0.9]
print('\npartial-coverage days (old or new cov<0.9):',len(P))
for r in P: print(r)
both=[r for r in P if r['old'] is not None and r['new'] is not None]
print('partial days with totals in both:',s(both,'old'),'|',s(both,'new'))
ch=[r for r in rows if r['old']!=r['new']]; print('days whose total changed',len(ch),'of',len(rows))
