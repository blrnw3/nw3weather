from v2 import *
import csv
Wt,Cc=4,0.1
clean=[d for d in days if d not in LAB and d not in TUN and d not in ERR and d not in WEAK]
spec=fit(WEAK,C_=Cc,w=Wt); nw=sum(len(W[d]) for d in WEAK)
spec['description']='nw3 main sunny LR v2: %d hand labels + %d weak hazy-sun labels from 12 Ben-reviewed datm-right days (weight %g, C=%g)'%(len(base),nw,Wt,Cc)
json.dump(spec,open('/workspace/nw3weather/site/sun_model/models/nw3_sunny_lr_v2.json','w'))
m2=E.Lin(spec)
folds=[WEAK[i::4] for i in range(4)]; hz={}
for fo in folds:
    mo=E.Lin(fit([d for d in WEAK if d not in fo],C_=Cc,w=Wt)); hz.update(run(mo,0.85,[d for d in fo if d in HAZY]))
r1=run(m1,.85,clean); r2=run(m2,.85,clean); h1=run(m1,.85,HAZY)
print('clean v1',st(r1)); print('clean v2',st(r2)); print('hazy v1',st(h1)); print('hazy v2(CV)',st(hz))
rows=[]
for mo in range(1,13):
    ds=[d for d in r1 if int(d[5:7])==mo and d in r2]
    e1=np.array([r1[d]-t[d] for d in ds]); e2=np.array([r2[d]-t[d] for d in ds])
    rows.append(dict(month=calendar.month_abbr[mo],n=len(ds),v1_mae=round(abs(e1).mean(),2),v1_bias=round(e1.mean(),2),v2_mae=round(abs(e2).mean(),2),v2_bias=round(e2.mean(),2)))
    print(rows[-1])
for nm,sel in (('summer MJJA',[5,6,7,8]),('winter NDJF',[11,12,1,2])):
    ds=[d for d in r1 if int(d[5:7]) in sel]
    print(nm,'v1',st({d:r1[d] for d in ds}),'| v2',st({d:r2[d] for d in ds}))
with open('/workspace/nw3sun/v2_eval_months.csv','w') as f:
    w=csv.DictWriter(f,rows[0].keys()); w.writeheader(); w.writerows(rows)
with open('/workspace/nw3sun/v2_eval_days.csv','w') as f:
    f.write('date,datm,v1,v2,set\n')
    for d in sorted(set(r1)|set(HAZY)|set(ERR)):
        a=run(m1,.85,[d]).get(d); b=hz.get(d) if d in HAZY else run(m2,.85,[d]).get(d)
        s='hazy_cv' if d in HAZY else 'datm_error' if d in ERR else 'clean'
        f.write('%s,%s,%s,%s,%s\n'%(d,t.get(d),a,b,s))
for d in HAZY: print(d,'datm %.1f v1 %.2f v2cv %.2f'%(t[d],h1[d],hz[d]))
print('ERR'); 
for d in sorted(ERR): print(d,'datm',t[d],'v1',run(m1,.85,[d]).get(d),'v2',run(m2,.85,[d]).get(d))
