import pickle,sys,math
sys.path.insert(0,'/workspace/nw3weather/site')
from datetime import datetime
from PIL import Image,ImageDraw,ImageFont
from sun_model.localtime import utc_to_local
from ev4 import NEW,COV
R={o['name']:o for o in pickle.load(open('run4.pkl','rb'))}
B=ImageFont.truetype('/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',15); H=ImageFont.truetype('/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',22)
PATH={(r[2],r[4]):r[0]['_path'] for r in NEW}
from ev4 import t as DATM
def sheet(day,v4='v4i_single_w3'):
    a=R['v3']['fa'][day]; b=R[v4]['fa'][day]; n=len(a); cols=7; rows=math.ceil(n/cols); tw,th,cap=320,213,40
    im=Image.new('RGB',(cols*tw,rows*(th+cap)+70),'white'); dr=ImageDraw.Draw(im)
    lab=sum(x[1] for x in a); dis3=sum(1 for x in a if x[3]!=x[1]); dis4=sum(1 for x in b if x[3]!=x[1])
    dr.text((8,6),'%s  LLM label vs v3 (T0.70) vs v4 (CV, held out)   LLM sunny frames %d/%d   errors v3 %d  v4 %d   datm %.1f h  day totals v3 %s / v4 %s'%(day,lab,n,dis3,dis4,DATM[day],R['v3']['new'][day],R[v4]['new'][day]),fill='black',font=H)
    dr.text((8,36),'tile border: red = v4 wrong, orange = only v3 wrong, green = all agree.  caption: local time | L=LLM  3=v3 call (p)  4=v4 call (p)',fill='black',font=B)
    for i,((k,l,p3,g3),(_,_,p4,g4)) in enumerate(zip(a,b)):
        x,y=(i%cols)*tw,(i//cols)*(th+cap)+70
        col='red' if g4!=l else 'orange' if g3!=l else 'green'
        dr.rectangle([x,y,x+tw-1,y+th+cap-1],fill=col)
        im.paste(Image.open(PATH[(day,k)]).convert('RGB').resize((tw-8,th-4)),(x+4,y+cap))
        lt=utc_to_local(datetime.strptime(k,'%Y%m%d%H%M')).strftime('%H:%M')
        dr.text((x+6,y+2),'%s  L=%d  3=%s(%.2f)  4=%s(%.2f)'%(lt,l,g3,p3,g4,p4),fill='white' if col!='orange' else 'black',font=B)
        dr.text((x+6,y+20),'el %.0f'%0 if False else '',fill='white',font=B)
    im.save('/workspace/nw3sun/review4/%s.jpg'%day,quality=85); print(day,n,dis3,dis4)
for d in ('2022-06-11','2023-06-26','2025-08-14'): sheet(d)
