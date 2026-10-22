from ev3 import *
from sklearn.model_selection import GroupKFold
from sklearn.metrics import balanced_accuracy_score
y=np.array([r[1] for r in base]); g=np.array([r[2] for r in base]); oof=np.zeros(len(y)); oof1=np.zeros(len(y))
for tr,te in GroupKFold(5).split(np.zeros(len(y)),y,g):
    m=Reg(fitreg([base[i] for i in tr],25,0.1),25); oof[te]=m.probs([base[i][0] for i in te])
    m1=Lin(fitlr([base[i] for i in tr],F1,0.1)); oof1[te]=m1.probs([base[i][0] for i in te])
lm=np.array([r[0]['az']<180 and r[0]['el']<25 for r in base])
for nm,o in (('v1-features LR',oof1),('v3 regime',oof)):
    print(nm,'balacc all %.3f lowmorning %.3f main %.3f'%(balanced_accuracy_score(y,o>.5),balanced_accuracy_score(y[lm],o[lm]>.5),balanced_accuracy_score(y[~lm],o[~lm]>.5)))
print('n',len(y),'pos',y.sum(),'lowmorning n',lm.sum())
