import sys,json,csv
sys.path.insert(0,'/workspace/nw3weather/site')
from sun_model.features import veg_feats,load_small
r={}
for x in csv.DictReader(open('/workspace/nw3sun/m2_labels.csv')):
    r[x['id']]=veg_feats(load_small('/workspace/nw3sun/'+x['path']),int(x['t_local'][5:7]))
json.dump(r,open('m2_feats3.json','w')); print(len(r))
