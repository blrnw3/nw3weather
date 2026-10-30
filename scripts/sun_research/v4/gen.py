import json,sys
sys.path.insert(0,'/workspace/nw3weather/site')
from datetime import datetime
from PIL import Image,ImageDraw,ImageFont
from sun_model.localtime import utc_to_local
FT=ImageFont.truetype('/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',26)
def frames(day,step_min):
    d=json.load(open('/workspace/nw3sun/evaltime/feats/%s.json'%day.replace('-','')))
    ks=sorted(k for k in d if d[k]['el']>=2); out=[]; last=None
    for k in ks:
        t=datetime.strptime(k,'%Y%m%d%H%M')
        if last is None or (t-last).total_seconds()>=step_min*60-90: out.append(k); last=t
    return [(day,k,d[k]['_path'],d[k]['el'],d[k]['az']) for k in out]
def render(name,items,per=8):
    man=json.load(open('manifest.json')) if __import__('os').path.exists('manifest.json') else {}
    for s in range(0,len(items),per):
        ch=items[s:s+per]; tw,th=640,427; cap=34
        sh=Image.new('RGB',(2*tw,4*(th+cap)),'white'); dr=ImageDraw.Draw(sh)
        for i,(day,k,p,el,az) in enumerate(ch):
            x,y=(i%2)*tw,(i//2)*(th+cap); sh.paste(Image.open(p).convert('RGB').resize((tw-6,th)),(x+3,y+cap))
            lt=utc_to_local(datetime.strptime(k,'%Y%m%d%H%M')).strftime('%H:%M')
            dr.text((x+6,y+2),'%d  %s %s  el %.0f az %.0f'%(i,day,lt,el,az),fill='black',font=FT)
        fn='%s_%02d'%(name,s//per); sh.save(fn+'.jpg',quality=85); man[fn]=[[day,k] for day,k,*_ in ch]
    json.dump(man,open('manifest.json','w'))
if __name__=='__main__':
    name,step=sys.argv[1],int(sys.argv[2]); items=[]
    for day in sys.argv[3:]: items+=frames(day,step)
    render(name,items); print(name,len(items),(len(items)+7)//8)
