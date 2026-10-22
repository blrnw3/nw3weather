from ev3 import *
import pickle
from PIL import Image,ImageDraw,ImageFont
from sun_model.localtime import utc_to_local
FT=ImageFont.truetype('/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',13); FH=ImageFont.truetype('/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',20)
spec=json.load(open('/workspace/nw3weather/site/sun_model/models/nw3_sunny_lr_v3.json')); full=Reg(spec['parts'],25)
R=pickle.load(open('final3.pkl','rb'))
for day in OVER:
    r,grid,pp=day_total(day,full,0.7,False,grid_out=True)
    fr=load(day); gm={m.t.strftime('%Y%m%d%H%M'):m for m in grid}; keys=[k for k in sorted(fr) if k in gm]
    if len(keys)>72: s=len(keys)/72; keys=[keys[int(i*s)] for i in range(72)]
    cols=8; tw,th=300,200; cap=34; hh=60; rows=(len(keys)+cols-1)//cols
    sh=Image.new('RGB',(cols*tw,hh+rows*(th+cap)),'white'); d=ImageDraw.Draw(sh)
    d.text((10,8),'%s   datm %.1f h   v3 %.2f h (held-out CV %.2f h)   v2 %.2f h   v1 %.2f h'%(day,t[day],r['sun_h'],R['v3_T0.70']['over'][day],R['v2']['over'][day],R['v1']['over'][day]),fill='black',font=FH)
    d.text((10,36),'tile: local time | sun el | regime (LM = low-morning model) | v3 p (decision 0.70) | v3 call | veg/sky ratio | [gates]   border: yellow=sunny grey=not sunny red=unknown',fill='#444',font=FT)
    for i,k in enumerate(keys):
        x,y=(i%cols)*tw,hh+(i//cols)*(th+cap); m=gm[k]; f=fr[k]
        im=Image.open(f['_path']).convert('RGB'); im.thumbnail((tw-8,th-8))
        d.rectangle([x+1,y+1,x+tw-2,y+th-2],fill={1:'#f0b800',0:'#888888',None:'#d00000'}[m.value]); sh.paste(im,(x+4,y+4))
        d.text((x+4,y+th),'%s el%.0f %s p%.2f %s r%.2f'%(utc_to_local(m.t).strftime('%H:%M'),m.el,'LM' if full.reg(f)=='lowmorning' else 'M',pp[k],{1:'SUNNY',0:'not sunny',None:'unknown'}[m.value],f['r_veg_sky']),fill='black',font=FT)
        if m.flags: d.text((x+4,y+th+16),'['+','.join(sorted(m.flags))+']',fill='#c00000',font=FT)
    sh.save('/workspace/nw3sun/review3/%s.jpg'%day,quality=82); print(day,r['sun_h'])
# mask overlay
M={s:np.asarray(Image.open('/workspace/nw3weather/site/sun_model/models/veg_mask_%s.png'%s))>127 for s in ('summer','winter')}
tiles=[]
for s,f in (('summer','/workspace/nw3sun/valdays/2022/06/11/1200hik.jpg'),('winter','/workspace/nw3sun/valdays/2022/01/18/1200hik.jpg')):
    im=Image.open(f).convert('RGB').resize((768,432)); a=np.asarray(im).astype(float)
    m=np.array(Image.fromarray((M[s]*255).astype(np.uint8)).resize((768,432),Image.NEAREST))>0
    b=a.copy(); b[m]=b[m]*0.45+np.array([255,0,255])*0.55
    rows_=np.nonzero(M[s].any(1))[0]; top=int((rows_.min()-2)*432/108); b[:top]=b[:top]*0.75+np.array([0,160,255])*0.25
    tiles.append(np.concatenate([a,b],1))
out=Image.fromarray(np.concatenate(tiles,0).astype(np.uint8)); d=ImageDraw.Draw(out)
d.text((780,8),'summer mask (May-Oct): magenta = vegetation, blue tint = sky region',fill='white',font=FH); d.text((780,440),'winter mask (Nov-Apr)',fill='white',font=FH)
out.save('/workspace/nw3sun/review3/veg_mask_overlay.jpg',quality=85)
