from ev3 import *
import itertools
for el_max,Cc,w,obs in itertools.product((15,25),(0.1,),(2,4),(True,False)):
    for T in (0.5,0.7,0.85):
        o,_=evaluate(el_max,Cc,w,T,obs)
        print('el%d C%.1f w%d obsEv%d T%.2f | tune %s | hazyCV %s | overCV %s'%(el_max,Cc,w,obs,T,o['tune'],o['hazy'],o['over']),flush=True)
