from ev3 import *
import csv,sys as _s
sys.path.insert(0,'/workspace/nw3sun/evaltime'); import evalmod as E1
m1=E1.Lin(E1.spec('nw3_sunny_lr_v1')); m2=E1.Lin(E1.spec('nw3_sunny_lr_v2')); sk=E1.Lin(E1.spec('nw3_skyclear_lr_v1'))
EL,CC,W,OBS=25,0.1,4,False
def v12(model,ds): return {d:(E1.day_total(d,model,sk) or {}).get('sun_h') for d in ds}
R={'v1':{},'v2':{}}
for nm,mo in (('v1',m1),('v2',m2)):
    R[nm]['clean']=v12(mo,CLEAN); R[nm]['hazy']=v12(mo,HAZY); R[nm]['over']=v12(mo,OVER)
# v2 trained on hazy+2022-10-13: use its CV numbers for hazy from previous report only; flag
for T in (0.7,0.8):
    o,full=evaluate(EL,CC,W,T,OBS,sets=('clean','weakcv','tune'))
    R['v3_T%.2f'%T]=dict(clean=o['clean_r'],hazy={d:o['weak_r'][d] for d in HAZY},over={d:o['weak_r'][d] for d in OVER},tune=o['tune'])
    print('v3 T',T,'tune',o['tune'])
import pickle; pickle.dump(R,open('final3.pkl','wb'))
for nm in R:
    print(nm,'clean',st(R[nm]['clean']),'hazy',st(R[nm]['hazy']),'over',st(R[nm]['over']))
    for lab,ms in (('summer MJJA',(5,6,7,8)),('winter NDJF',(11,12,1,2))):
        print('   ',lab,st({d:v for d,v in R[nm]['clean'].items() if int(d[5:7]) in ms}))
rows=[]
for mo in range(1,13):
    row=dict(month=calendar.month_abbr[mo])
    for nm in R:
        s=st({d:v for d,v in R[nm]['clean'].items() if int(d[5:7])==mo}); row['n']=s['n']; row[nm+'_mae']=s['mae']; row[nm+'_bias']=s['bias']
    rows.append(row)
with open('/workspace/nw3sun/v3_eval_months.csv','w') as f:
    w=csv.DictWriter(f,rows[0].keys()); w.writeheader(); w.writerows(rows)
for r in rows: print(r)
with open('/workspace/nw3sun/v3_eval_days.csv','w') as f:
    ks=list(R); f.write('date,set,datm,'+','.join(ks)+'\n')
    for st_ in ('clean','hazy','over'):
        for d in sorted(R['v1'][st_]): f.write('%s,%s,%s,%s\n'%(d,st_,t[d],','.join(str(R[k][st_].get(d)) for k in ks)))
for d in HAZY+OVER: print(d,'datm',t[d],' '.join('%s=%s'%(k,R[k]['hazy' if d in HAZY else 'over'][d]) for k in R))
