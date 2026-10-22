from ev3 import *
EL,CC,W,T=25,0.1,4,0.7
parts=build(EL,CC,W)
nb=len(base); nw=sum(len(weak(d,Reg(fitreg(base,EL,CC),EL))) for d in WEAK)
spec=dict(version='nw3_sunny_lr_v3',type='regime',decision_t=T,
  description='nw3 v3: vegetation-mask features + regime LRs (low morning: before noon & el<%d; main: rest). %d hand labels (13 relabelled to Ben rule: dark near vegetation = not sunny) + %d weak labels from 16 Ben-reviewed datm-right days (weight %d, C=%g)'%(EL,nb,nw,W,CC),
  regime=dict(lowmorning_el_max=EL,lowmorning_az_max=180),rules=dict(obstruct_evening=False),
  parts={k:dict(v,version='nw3_sunny_lr_v3_'+k) for k,v in parts.items()})
json.dump(spec,open('/workspace/nw3weather/site/sun_model/models/nw3_sunny_lr_v3.json','w'))
print('exported',nb,nw)
full=Reg(parts,EL)
for d in ('2023-01-12','2025-07-03','2022-06-11'): print(d,day_total(d,full,T,False))
