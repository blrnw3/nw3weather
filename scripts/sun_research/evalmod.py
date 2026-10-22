"""Offline day rollup with the production rules for any (main, sky, T) model combo, using cached per-frame features."""
import sys,json,math,glob,os
sys.path.insert(0,'/workspace/nw3weather/site')
from datetime import datetime
import numpy as np
from sun_model import classify as C
from sun_model.rules import Frame, classify_day
from sun_model.localtime import local_day_bounds_utc
from sun_model.solar import sun_times_utc
_F={}
def load(day):
    if day not in _F: _F[day]=json.load(open('/workspace/nw3sun/evaltime/feats/%s.json'%day.replace('-','')))
    return _F[day]
class Lin:
    def __init__(s,spec):
        s.f=spec['features']; s.m=np.array(spec['mean']); s.s=np.array(spec['scale']); s.c=np.array(spec['coef']); s.b=spec['intercept']
    def probs(s,fs):
        X=np.array([[f[k] for k in s.f] for f in fs],float); z=((X-s.m)/s.s)@s.c+s.b; return 1/(1+np.exp(-np.clip(z,-50,50)))
def spec(name): return json.load(open('/workspace/nw3weather/site/sun_model/models/%s.json'%name))
def day_total(day,main,sky,T=0.85,return_grid=False):
    fr=load(day); keys=sorted(fr)
    if not keys: return None
    fs=[fr[k] for k in keys]; p=main.probs(fs); ps=sky.probs(fs)
    frames=[]
    for k,f,pp,qq in zip(keys,fs,p,ps):
        t=datetime.strptime(k,'%Y%m%d%H%M')
        frames.append(Frame(t,C.shift_p(float(pp),T),float(qq),f['Smean'],stale=f['_stale'],glitch=f.get('pink_bot',0)>C.GLITCH_PINK,pink=f.get('pink_bot')))
    d=datetime.strptime(day,'%Y-%m-%d').date(); t0,t1=local_day_bounds_utc(d); sr,ss=sun_times_utc(t0,t1)
    ts=[datetime.strptime(k,'%Y%m%d%H%M') for k in keys]; gaps=sorted((b-a).total_seconds() for a,b in zip(ts,ts[1:]))
    cad=gaps[len(gaps)//2] if gaps else 60
    grid=classify_day(frames,sr,ss,tolerance_s=max(300,cad/2))
    for m in grid:
        if m.value==1 and m.el<C.horizon(m.az): m.value=0; m.flags.add('horizon')
    r=C.rollup(grid,None); r['cadence_s']=cad
    if return_grid: return r,grid,dict(zip(keys,zip(p,ps)))
    return r
