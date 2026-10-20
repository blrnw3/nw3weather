from run4 import *
import run4
R=pickle.load(open('run4.pkl','rb'))
for nm,sp,se,w,C in (('v4k_single_w4',False,False,4.0,0.1),('v4l_single_w3_C0.03',False,False,3.0,0.03),('v4m_single_w3_C0.3',False,False,3.0,0.3),('v4n_seasonT_w4',False,True,4.0,0.1),('v4o_split_w3',True,False,3.0,0.1)):
    R.append(run(nm,sp,se,w,C)); pickle.dump(R,open('run4.pkl','wb'))
