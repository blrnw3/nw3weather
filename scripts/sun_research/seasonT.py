from ev3 import *
full=Reg(build(25,0.1,4),25); fm={}
for fo in FOLDS:
    mo=Reg(build(25,0.1,4,exclude=fo),25)
    for d in fo: fm[d]=mo
SUM=(5,6,7,8,9)
for Ts,Tw in ((0.7,0.7),(0.6,0.7),(0.5,0.7),(0.6,0.8),(0.5,0.8)):
    T=lambda d:Ts if int(d[5:7]) in SUM else Tw
    c={d:day_total(d,full,T(d),False) for d in CLEAN}; tu={d:day_total(d,full,T(d),False) for d in TUNE}
    w={d:day_total(d,fm[d],T(d),False) for d in WEAK}
    print('Tsum %.2f Twin %.2f | tune %s | clean %s | summer %s | winter %s | hazy %s | over %s'%(Ts,Tw,st(tu),st(c),st({d:v for d,v in c.items() if int(d[5:7]) in SUM}),st({d:v for d,v in c.items() if int(d[5:7]) not in SUM}),st({d:w[d] for d in HAZY}),st({d:w[d] for d in OVER})),flush=True)
