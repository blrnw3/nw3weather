"""v3 experiments: vegetation features + regime LRs + relabels + weak labels from Ben-reviewed datm-right days."""
import sys,json,numpy as np,calendar,collections
sys.path.insert(0,'/workspace/nw3weather/site'); sys.path.insert(0,'/workspace/nw3sun/evaltime')
from datetime import datetime
from sklearn.linear_model import LogisticRegression
from sklearn.preprocessing import StandardScaler
from sun_model import classify as C
from sun_model.rules import Frame, classify_day
from sun_model.localtime import local_day_bounds_utc
from sun_model.solar import sun_times_utc
from sun_model.features import add_interactions
t=json.load(open('/workspace/nw3sun/datm/sunhr.json'))
fl=json.load(open('/workspace/nw3sun/evaltime/flags.json')); LAB=set(fl['label']); TUN=set(fl['tune'])
ERR=set('2022-04-01 2026-08-28 2026-03-15 2025-12-10 2024-04-07 2022-11-08 2022-06-01 2024-05-25'.split())
HAZY='2022-04-17 2024-04-13 2023-06-17 2023-09-06 2026-03-20 2026-05-09 2024-08-18 2023-04-30 2022-06-16 2026-10-03 2022-04-10'.split()
OVER='2022-06-11 2022-10-13 2022-07-29 2024-02-11 2026-07-01'.split()
WEAK=HAZY+OVER
_F={}
def load(day):
    if day not in _F:
        k=day.replace('-',''); a=json.load(open('/workspace/nw3sun/evaltime/feats/%s.json'%k)); b=json.load(open('/workspace/nw3sun/evaltime/feats3/%s.json'%k))
        for x in a:
            if x in b: a[x].update(b[x]); add_interactions(a[x])
        _F[day]={x:a[x] for x in a if x in b}
    return _F[day]
V1=json.load(open('/workspace/nw3weather/site/sun_model/models/nw3_sunny_lr_v1.json'))
F1=V1['features']
VEG=sorted(json.load(open('m2_feats3.json'))['n0000'])+['x_rveg_h','x_hirel_h','x_crown_h']
FEATS=F1+VEG
M2=json.load(open('/workspace/nw3sun/m2_feats.json')); M3=json.load(open('m2_feats3.json')); RL=json.load(open('relabels.json'))
base=[]
for r in M2:
    if r['label']=='u': continue
    f=dict(r['f']); f.update(M3[r['id']]); add_interactions(f)
    base.append((f,RL.get(r['id'],int(r['label'])),r['day']))
class Lin:
    def __init__(s,sp): s.f=sp['features']; s.m=np.array(sp['mean']); s.s=np.array(sp['scale']); s.c=np.array(sp['coef']); s.b=sp['intercept']
    def probs(s,fs):
        X=np.array([[f[k] for k in s.f] for f in fs],float); return 1/(1+np.exp(-np.clip(((X-s.m)/s.s)@s.c+s.b,-50,50)))
class Reg:
    def __init__(s,parts,el_max,az_max=180): s.p={k:Lin(v) for k,v in parts.items()}; s.el=el_max; s.az=az_max
    def reg(s,f): return 'lowmorning' if f['az']<s.az and f['el']<s.el else 'main'
    def probs(s,fs):
        out=np.zeros(len(fs)); g=collections.defaultdict(list)
        for i,f in enumerate(fs): g[s.reg(f)].append(i)
        for k,ix in g.items(): out[ix]=s.p[k].probs([fs[i] for i in ix])
        return out
SKY=Lin(json.load(open('/workspace/nw3weather/site/sun_model/models/nw3_skyclear_lr_v1.json')))
def fitlr(rows,feats,Cc):
    X=np.array([[r[0][k] for k in feats] for r in rows]); y=np.array([r[1] for r in rows]); w=np.array([r[3] if len(r)>3 else 1.0 for r in rows])
    sc=StandardScaler().fit(X); m=LogisticRegression(C=Cc,max_iter=5000,class_weight='balanced').fit(sc.transform(X),y,sample_weight=w)
    return dict(features=feats,mean=sc.mean_.tolist(),scale=sc.scale_.tolist(),coef=m.coef_[0].tolist(),intercept=float(m.intercept_[0]))
def fitreg(rows,el_max,Cc,feats=FEATS):
    lm=[r for r in rows if r[0]['az']<180 and r[0]['el']<el_max]; mn=[r for r in rows if not (r[0]['az']<180 and r[0]['el']<el_max)]
    return dict(lowmorning=fitlr(lm,feats,Cc),main=fitlr(mn,feats,Cc))
def day_total(day,model,T,obs_ev=True,grid_out=False):
    fr=load(day); keys=sorted(fr)
    if not keys: return None
    fs=[fr[k] for k in keys]; p=model.probs(fs); ps=SKY.probs(fs)
    frames=[Frame(datetime.strptime(k,'%Y%m%d%H%M'),C.shift_p(float(a),T),float(b),f['Smean'],stale=f['_stale'],glitch=f.get('pink_bot',0)>C.GLITCH_PINK,pink=f.get('pink_bot')) for k,f,a,b in zip(keys,fs,p,ps)]
    d=datetime.strptime(day,'%Y-%m-%d').date(); t0,t1=local_day_bounds_utc(d); sr,ss=sun_times_utc(t0,t1)
    ts=[x.t for x in frames]; gaps=sorted((b-a).total_seconds() for a,b in zip(ts,ts[1:])); cad=gaps[len(gaps)//2] if gaps else 60
    grid=classify_day(frames,sr,ss,tolerance_s=max(300,cad/2),obstruct_evening=obs_ev)
    for m in grid:
        if m.value==1 and m.el<C.horizon(m.az): m.value=0; m.flags.add('horizon')
    r=C.rollup(grid,None)
    if grid_out: return r,grid,dict(zip(keys,p))
    return r['sun_h']
def weak(day,model,margin=0.1,w=1.0):
    fr=load(day); ks=[k for k in sorted(fr) if fr[k]['el']>=max(5,C.horizon(fr[k]['az']))]
    mins=[int(k[-4:-2])*60+int(k[-2:]) for k in ks]; cad=np.median(np.diff(mins))*60 if len(ks)>2 else 900
    p=model.probs([fr[k] for k in ks]); order=np.argsort(-p); N=len(ks); K=int(round(min(t[day]*3600/cad,N))); m=int(round(margin*N))
    return [(fr[ks[i]],1 if r<K-m else 0,day,w) for r,i in enumerate(order) if r<K-m or r>=K+m]
days=[l.strip() for l in open('/workspace/nw3sun/evaltime/days.txt') if l.strip() in t]
CLEAN=[d for d in days if d not in LAB and d not in TUN and d not in ERR and d not in WEAK]
TUNE=[d for d in days if d in TUN and d not in ERR and d not in WEAK]
def st(r):
    r={d:v for d,v in r.items() if v is not None}; e=np.array([r[d]-t[d] for d in r]); a=np.array(list(r.values())); b=np.array([t[d] for d in r])
    return dict(n=len(e),mae=round(float(abs(e).mean()),2),bias=round(float(e.mean()),2),r=round(float(np.corrcoef(a,b)[0,1]),3) if len(e)>2 else None)
FOLDS=[WEAK[i::4] for i in range(4)]
def build(el_max,Cc,w,exclude=()):
    stage1=Reg(fitreg(base,el_max,Cc),el_max)
    rows=list(base)
    for d in WEAK:
        if d not in exclude and w>0: rows+=weak(d,stage1,w=w)
    return fitreg(rows,el_max,Cc)
def evaluate(el_max,Cc,w,T,obs_ev,sets=('tune','weakcv')):
    out={}
    full=Reg(build(el_max,Cc,w),el_max)
    if 'tune' in sets: out['tune']=st({d:day_total(d,full,T,obs_ev) for d in TUNE})
    if 'clean' in sets: out['clean_r']={d:day_total(d,full,T,obs_ev) for d in CLEAN}; out['clean']=st(out['clean_r'])
    if 'weakcv' in sets:
        r={}
        for fo in FOLDS:
            mo=Reg(build(el_max,Cc,w,exclude=fo),el_max)
            for d in fo: r[d]=day_total(d,mo,T,obs_ev)
        out['weak_r']=r; out['hazy']=st({d:r[d] for d in HAZY}); out['over']=st({d:r[d] for d in OVER})
    return out,full
