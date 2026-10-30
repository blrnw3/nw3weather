"""v4: full-res LLM relabels (2022-06-11 per-frame, 8 summer + 4 winter days @30min, 15 datm-right days @60min,
summer boundary re-check of the 919 hand set) replace datm-matched weak labels; season-split main model/thresholds."""
import sys,os,json; sys.path.insert(0,'/workspace/nw3sun/v3'); os.chdir('/workspace/nw3sun/v3')
from ev3 import *
os.chdir('/workspace/nw3sun/v4')
RC4=json.load(open('rc4.json'))
SUMMER=(5,6,7,8,9)
def season(day): return 'summer' if int(day[5:7]) in SUMMER else 'winter'
# hand rows with provenance
HAND=[]  # (f,label,day,w,id,prov)
for r in M2:
    if r['label']=='u': continue
    f=dict(r['f']); f.update(M3[r['id']]); add_interactions(f)
    l=int(r['label']); prov='hand_v1'
    if r['id'] in RL: l=RL[r['id']]; prov='relabel_v3'
    if r['id'] in RC4:
        prov='relabel_v4' if RC4[r['id']]!=l else prov+'+checked_v4'; l=RC4[r['id']]
    HAND.append((f,l,r['day'],1.0,r['id'],prov))
L4=json.load(open('/workspace/nw3sun/lab4/labels.json')); MAN=json.load(open('/workspace/nw3sun/lab4/manifest.json'))
NEW=[]; MISS=0
for sh,s in L4.items():
    for (d,k),c in zip(MAN[sh],s):
        fr=load(d)
        if k not in fr: MISS+=1; continue
        NEW.append((fr[k],int(c),d,1.0,k,'relabel_v4:'+sh.split('_')[0]))
NEWDAYS=sorted(set(r[2] for r in NEW))
srt=sorted(NEWDAYS,key=lambda d:(season(d),d)); NFOLDS=[srt[i::4] for i in range(4)]
CLEAN4=[d for d in CLEAN if d not in NEWDAYS]
def rows(exclude=(),wnew=1.0):
    return [r[:4] for r in HAND if r[2] not in exclude]+[(r[0],r[1],r[2],wnew) for r in NEW if r[2] not in exclude]
class Reg4(Reg):
    """lowmorning shared; main optionally split by season. T: float or {season:T}."""
    def __init__(s,parts,el_max,T): s.p={k:Lin(v) for k,v in parts.items()}; s.el=el_max; s.az=180; s.T=T; s.day=None
    def reg(s,f):
        if f['az']<s.az and f['el']<s.el: return 'lowmorning'
        k='main_'+season(s.day); return k if k in s.p else 'main'
    def t_for(s,day): return s.T[season(day)] if isinstance(s.T,dict) else s.T
def fit4(rs,el_max,Cc,split):
    lm=[r for r in rs if r[0]['az']<180 and r[0]['el']<el_max]; mn=[r for r in rs if not (r[0]['az']<180 and r[0]['el']<el_max)]
    P=dict(lowmorning=fitlr(lm,FEATS,Cc))
    if split:
        for se in ('summer','winter'): P['main_'+se]=fitlr([r for r in mn if season(r[2])==se],FEATS,Cc)
    else: P['main']=fitlr(mn,FEATS,Cc)
    return P
def total(day,m,obs_ev=False,grid_out=False):
    m.day=day; return day_total(day,m,m.t_for(day),obs_ev,grid_out)
COV={}
def frame_acc(day,m,obs_ev=False):
    r,grid,p=total(day,m,obs_ev,True); g={x.t.strftime('%Y%m%d%H%M'):x for x in grid}
    out=[]
    for x in NEW:
        if x[2]!=day: continue
        k=x[4]; mi=g.get(k[:-2]+k[-2:]) 
        if mi is None:
            from datetime import datetime,timedelta
            tt=datetime.strptime(k,'%Y%m%d%H%M').replace(second=0); mi=g.get(tt.strftime('%Y%m%d%H%M'))
        out.append((k,x[1],float(p.get(k,np.nan)),None if mi is None else mi.value))
    if r: COV[day]=(round(r['sun_min']/60.,2),r['coverage'],r['scaled'])
    return r['sun_h'] if r else None,out
