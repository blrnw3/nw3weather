import json,numpy as np,csv
from sklearn.linear_model import LogisticRegression
from sklearn.preprocessing import StandardScaler
from sklearn.model_selection import GroupKFold
D={r['id']:r for r in json.load(open('/workspace/nw3sun/m2_feats.json'))}; V=json.load(open('m2_feats3.json'))
P={x['id']:x for x in csv.DictReader(open('/workspace/nw3sun/m2_labels.csv'))}
ids=[i for i in D if D[i]['label']!='u']
VF=sorted(V[ids[0]]); 
X=np.array([[V[i][k] for k in VF]+[D[i]['f']['el']] for i in ids]); y=np.array([int(D[i]['label']) for i in ids]); g=np.array([D[i]['day'] for i in ids])
oof=np.zeros(len(y))
for tr,te in GroupKFold(5).split(X,y,g):
    sc=StandardScaler().fit(X[tr]); m=LogisticRegression(C=0.3,max_iter=5000,class_weight='balanced').fit(sc.transform(X[tr]),y[tr]); oof[te]=m.predict_proba(sc.transform(X[te]))[:,1]
from sklearn.metrics import balanced_accuracy_score
print('veg-only balacc',balanced_accuracy_score(y,oof>.5))
lowm=np.array([D[i]['f']['az']<180 and D[i]['f']['el']<20 for i in ids])
print('low-morning n',lowm.sum(),'balacc there',balanced_accuracy_score(y[lowm],oof[lowm]>.5),'rest',balanced_accuracy_score(y[~lowm],oof[~lowm]>.5))
c=[(ids[j],y[j],oof[j]) for j in range(len(y)) if not lowm[j] and ((y[j]==1 and oof[j]<0.6) or (y[j]==0 and oof[j]>0.5))]
print('candidates',len(c),'lab1-lowveg',sum(1 for x in c if x[1]==1))
json.dump([[i,int(l),round(float(p),3),P[i]['path'],P[i]['t_local'],float(D[i]['f']['el']),round(V[i]['r_veg_sky'],3)] for i,l,p in c],open('cands.json','w'))
