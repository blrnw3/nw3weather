import json,sys,numpy as np,collections,calendar
sys.path.insert(0,'.'); import evalmod as E
from sun_model import classify as C
from sklearn.linear_model import LogisticRegression
from sklearn.preprocessing import StandardScaler
t=json.load(open('/workspace/nw3sun/datm/sunhr.json'))
fl=json.load(open('flags.json')); LAB=set(fl['label']); TUN=set(fl['tune'])
ERR=set('2022-04-01 2026-08-28 2026-03-15 2025-12-10 2024-04-07 2022-11-08 2022-06-01 2024-05-25'.split())
HAZY='2022-04-17 2024-04-13 2023-06-17 2023-09-06 2026-03-20 2026-05-09 2024-08-18 2023-04-30 2022-06-16 2026-10-03 2022-04-10'.split()
WEAK=HAZY+['2022-10-13']      # Ben: datm right on all of these
V1=E.spec('nw3_sunny_lr_v1'); FEATS=V1['features']; m1=E.Lin(V1); sky=E.Lin(E.spec('nw3_skyclear_lr_v1'))
base=[r for r in json.load(open('/workspace/nw3sun/m2_feats.json')) if r['label']!='u']
def weak(day,margin=0.1):
    fr=E.load(day); ks=[k for k in sorted(fr) if fr[k]['el']>=max(5,C.horizon(fr[k]['az']))]
    ts=sorted(ks); cad=np.median(np.diff([int(k[-4:-2])*60+int(k[-2:]) for k in ts]))*60 if len(ts)>2 else 900
    p=m1.probs([fr[k] for k in ks]); order=np.argsort(-p); N=len(ks)
    K=int(round(min(t[day]*3600/cad,N))); m=int(round(margin*N))
    out=[]
    for rank,i in enumerate(order):
        if rank<K-m: out.append((fr[ks[i]],1))
        elif rank>=K+m: out.append((fr[ks[i]],0))
    return out
W={d:weak(d) for d in WEAK}
def fit(extra_days,C_=0.1,w=1.0):
    X=[[r['f'][k] for k in FEATS] for r in base]; y=[int(r['label']) for r in base]; sw=[1.0]*len(y)
    for d in extra_days:
        for f,l in W[d]: X.append([f[k] for k in FEATS]); y.append(l); sw.append(w)
    X=np.array(X); y=np.array(y); sc=StandardScaler().fit(X)
    m=LogisticRegression(C=C_,max_iter=5000,class_weight='balanced').fit(sc.transform(X),y,sample_weight=np.array(sw))
    return dict(version='nw3_sunny_lr_v2',features=FEATS,mean=sc.mean_.tolist(),scale=sc.scale_.tolist(),coef=m.coef_[0].tolist(),intercept=float(m.intercept_[0]))
days=[l.strip() for l in open('days.txt') if l.strip() in t]
def run(model,T,sel):
    r={}
    for d in sel:
        x=E.day_total(d,model,sky,T)
        if x and x['sun_h'] is not None: r[d]=x['sun_h']
    return r
def st(r):
    e=np.array([r[d]-t[d] for d in r]); a=np.array(list(r.values())); b=np.array([t[d] for d in r])
    return 'n=%3d MAE %.2f bias %+.2f r %.3f'%(len(e),abs(e).mean(),e.mean(),np.corrcoef(a,b)[0,1])
if __name__=='__main__':
    print('weak labels:',{d:(sum(l for _,l in W[d]),len(W[d])) for d in W})
    clean=[d for d in days if d not in LAB and d not in TUN and d not in ERR and d not in WEAK]
    tune=[d for d in days if d in TUN and d not in ERR and d not in WEAK]
    # hazy CV: 4 folds over the weak days
    folds=[WEAK[i::4] for i in range(4)]
    res={}
    for w in (0.5,1.0,2.0):
        full=E.Lin(fit(WEAK,w=w))
        hz={}
        for fo in folds:
            mo=E.Lin(fit([d for d in WEAK if d not in fo],w=w))
            for T in (0.7,0.8,0.85):
                hz.setdefault(T,{}).update(run(mo,T,[d for d in fo if d in HAZY]))
        for T in (0.7,0.8,0.85):
            res[(w,T)]=(run(full,T,clean),run(full,T,tune),hz[T])
            print('v2 w=%.1f T=%.2f | clean %s | tune %s | hazy(CV) %s'%(w,T,st(res[(w,T)][0]),st(res[(w,T)][1]),st(res[(w,T)][2])))
    for T in (0.6,0.7,0.8,0.85):
        print('v1      T=%.2f | clean %s | tune %s | hazy %s'%(T,st(run(m1,T,clean)),st(run(m1,T,tune)),st(run(m1,T,HAZY))))
