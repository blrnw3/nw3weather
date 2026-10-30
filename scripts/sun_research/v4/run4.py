import ev4 as E, numpy as np, pickle, time, sys
from ev4 import *
TG=[0.35,0.4,0.45,0.5,0.55,0.6,0.65,0.7,0.75,0.8,0.85]
def tune(parts,seasonal):
    res={T:{d:total(d,Reg4(parts,25,T)) for d in TUNE} for T in TG}
    def score(T,se): s=st({d:v for d,v in res[T].items() if season(d)==se} if se else res[T]); return s['mae']+0.5*abs(s['bias'])
    if seasonal: return {se:min(TG,key=lambda T:score(T,se)) for se in ('summer','winter')}
    return min(TG,key=lambda T:score(T,None))
def run(name,split,seasonal,wnew=1.0,Cc=0.1):
    t0=time.time(); parts=fit4(rows(wnew=wnew),25,Cc,split); T=tune(parts,seasonal); full=Reg4(parts,25,T)
    out=dict(name=name,T=T,parts=parts,clean={d:total(d,full) for d in CLEAN4},tune={d:total(d,full) for d in TUNE},new={},fa={})
    for fo in NFOLDS:
        p=fit4(rows(exclude=fo,wnew=wnew),25,Cc,split); m=Reg4(p,25,T)
        for d in fo: out['new'][d],out['fa'][d]=frame_acc(d,m); out.setdefault('cov',{})[d]=E.COV.get(d)
    print(name,'T',T,'%.0fs'%(time.time()-t0),flush=True); return out
def v3ref():
    import json
    sp=json.load(open('/workspace/nw3weather/site/sun_model/models/nw3_sunny_lr_v3.json'))
    full=Reg4(sp['parts'],25,0.7); out=dict(name='v3',T=0.7,clean={d:total(d,full) for d in CLEAN4},tune={d:total(d,full) for d in TUNE},new={},fa={})
    for fo in FOLDS:
        m=Reg4(build(25,0.1,4,exclude=fo),25,0.7)
        for d in fo: out['new'][d],out['fa'][d]=frame_acc(d,m); out.setdefault('cov',{})[d]=E.COV.get(d)
    for d in NEWDAYS:
        if d not in WEAK: out['new'][d],out['fa'][d]=frame_acc(d,full); out.setdefault('cov',{})[d]=E.COV.get(d)
    print('v3 done',flush=True); return out
if __name__=='__main__':
    R=[v3ref()]
    for nm,sp,se,w in (('v4a_single',False,False,1.0),('v4b_seasonT',False,True,1.0),('v4c_split',True,False,1.0),('v4d_split_seasonT',True,True,1.0),('v4e_split_seasonT_w2',True,True,2.0),('v4f_single_w2',False,False,2.0),('v4g_seasonT_w2',False,True,2.0),('v4h_split_w2',True,False,2.0),('v4i_single_w3',False,False,3.0),('v4j_seasonT_w3',False,True,3.0)):
        R.append(run(nm,sp,se,w)); pickle.dump(R,open('run4.pkl','wb'))
    pickle.dump(R,open('run4.pkl','wb'))
