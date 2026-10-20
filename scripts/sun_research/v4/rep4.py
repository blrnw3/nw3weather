import pickle,sys,numpy as np
from ev4 import *
R=pickle.load(open(sys.argv[1] if len(sys.argv)>1 else 'run4.pkl','rb'))
def fa(o,days):
    a=[(l,v) for d in days for k,l,p,v in o['fa'][d] if v is not None]; return round(np.mean([l==v for l,v in a]),3),len(a)
SUMN=[d for d in NEWDAYS if season(d)=='summer']; WINN=[d for d in NEWDAYS if season(d)=='winter']
for o in R:
    c=o['clean']
    print('%-22s T=%s'%(o['name'],o['T']))
    print('   clean',st(c),'| summer',st({d:v for d,v in c.items() if season(d)=='summer'}),'| winter',st({d:v for d,v in c.items() if season(d)=='winter'}))
    print('   MJJA',st({d:v for d,v in c.items() if int(d[5:7]) in (5,6,7,8)}),'NDJF',st({d:v for d,v in c.items() if int(d[5:7]) in (11,12,1,2)}),'tune',st(o['tune']))
    print('   hazy',st({d:o['new'][d] for d in HAZY}),'over',st({d:o['new'][d] for d in OVER}),'newCV',st(o['new']))
    print('   frameacc 0611',fa(o,['2022-06-11']),'summer',fa(o,SUMN),'winter',fa(o,WINN),'all',fa(o,NEWDAYS),' 0611 total',o['new']['2022-06-11'])
print('\nover/partial days: datm | per model total (cov)')
for d in OVER+['2022-06-16','2024-05-20']:
    print(d,t[d],' '.join('%s=%s'%(o['name'][:4],o['new'][d]) for o in R),' cov',R[-1]['cov'][d])
