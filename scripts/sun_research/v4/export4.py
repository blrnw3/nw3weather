import json,csv,calendar,pickle
from ev4 import *
P=fit4(rows(wnew=3.0),25,0.1,False)
for k in P: P[k]['version']='nw3_sunny_lr_v4/'+k
sp=dict(version='nw3_sunny_lr_v4',type='regime',decision_t=0.45,
 description='nw3 v4: v3 features/regimes (low morning: before noon & el<25; main: rest), trained on 734 hand labels (13 relabel_v3, 6 relabel_v4 after a full-res summer boundary re-check) + 537 full-res LLM per-frame labels (relabel_v4: 2022-06-11 @15min, 8 summer + 4 winter large-error days @30min, 15 datm-right days @60min; weight 3, C=0.1). No datm-matched weak labels. Season split of main model / thresholds tested and not adopted.',
 regime=dict(lowmorning_el_max=25,lowmorning_az_max=180),rules=dict(obstruct_evening=False),parts=P)
json.dump(sp,open('/workspace/nw3weather/site/sun_model/models/nw3_sunny_lr_v4.json','w'))
# provenance CSV
LAB=csv.DictReader(open('/workspace/nw3sun/m2_labels.csv')); PATH={r['id']:r for r in LAB}
CHK=set(x[0] for x in json.load(open('/workspace/nw3sun/lab4/recheck_sel.json')))
with open('/workspace/nw3sun/v4_labels.csv','w') as f:
    w=csv.writer(f); w.writerow(['id','day','utc_or_local_time','path','el','az','label','provenance','in_v4_training','note'])
    for r in M2:
        if r['label']=='u': continue
    for (fe,l,d,_,i,pv) in HAND:
        note='rechecked_v4' if i in CHK else ''
        w.writerow([i,d,PATH[i]['t_local']+' local',PATH[i]['path'],round(fe['el'],1),round(fe['az']),l,pv,1,note])
    for (fe,l,d,_,k,pv) in NEW:
        w.writerow([k,d,k+' UTC',fe['_path'],round(fe['el'],1),round(fe['az']),l,'relabel_v4',1,pv.split(':')[1]+' sheet; '+('per-frame truth day' if d=='2022-06-11' else 'datm-right day (replaces auto)' if d in WEAK else 'large-v3-error day')])
    # old v3 auto labels for the record (not used in v4)
    import os; os.chdir('/workspace/nw3sun/v3')
    stage1=Reg(fitreg(base,25,0.1),25)
    for d in WEAK:
        for fe,l,dd,_ in weak(d,stage1,w=4):
            k=[x for x,v in load(d).items() if v is fe][0]
            w.writerow([k,d,k+' UTC',fe['_path'],round(fe['el'],1),round(fe['az']),l,'auto',0,'v3 datm-matched weak label, superseded'])
R={o['name']:o for o in pickle.load(open('/workspace/nw3sun/v4/run4.pkl','rb'))}
K=['v3','v4i_single_w3','v4j_seasonT_w3','v4c_split','v4d_split_seasonT']
with open('/workspace/nw3sun/v4_eval_months.csv','w') as f:
    w=csv.writer(f); w.writerow(['month','n']+[k+s for k in K for s in ('_mae','_bias')])
    for mo in range(1,13):
        row=[calendar.month_abbr[mo]]
        for k in K:
            s=st({d:v for d,v in R[k]['clean'].items() if int(d[5:7])==mo})
            if len(row)==1: row.append(s['n'])
            row+=[s['mae'],s['bias']]
        w.writerow(row)
with open('/workspace/nw3sun/v4_eval_days.csv','w') as f:
    w=csv.writer(f); w.writerow(['date','set','datm']+K)
    for d in sorted(CLEAN4): w.writerow([d,'clean',t[d]]+[R[k]['clean'][d] for k in K])
    for d in NEWDAYS: w.writerow([d,'hazy' if d in HAZY else 'over' if d in OVER else 'newlab_cv',t[d]]+[R[k]['new'][d] for k in K])
print(open('/workspace/nw3sun/v4_eval_months.csv').read())
