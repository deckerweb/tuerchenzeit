/* Dates and contents are always authorized by the WordPress server. */
(() => {
 'use strict';
 function mount(root) {
  if (root.dataset.mounted) return;
  root.dataset.mounted = '1';
  const status=root.querySelector('.ttz-status'), retry=root.querySelector('.ttz-retry'), clock=root.querySelector('.ttz-countdown'), dialog=root.querySelector('dialog');
  const themes=['evergreen','village','winter','bethlehem','arrival','starpost'];
  let visitKey='',visits=new Set(),remember=false;
  let state,received=0,scheduled,refreshing=false,requesting=false,opener,failures=0,retryDay=null;
  async function request(day) {
   const body=new URLSearchParams({action:'ttz_calendar',calendar:root.dataset.calendar,preview:root.dataset.preview,date:root.dataset.date||''});
   if(day)body.set('day',day);
   const controller=new AbortController(), timeout=setTimeout(()=>controller.abort(),15000);
   try {
    const response=await fetch(root.dataset.endpoint,{method:'POST',credentials:'same-origin',cache:'no-store',body,signal:controller.signal});
    let result;try{result=await response.json();}catch{throw new Error(root.dataset.error);}
    if(!response.ok&&result.success)throw new Error(root.dataset.error);
    if(!result.success)throw new Error(result.data?.message||root.dataset.error);
    return result.data;
   } catch(error){if(error instanceof TypeError||error.name==='AbortError')throw new Error(root.dataset.error);throw error;} finally {clearTimeout(timeout);}
  }
  function show(title,content,isNotice=false,url='',day=0) {
   if(!dialog){status.textContent=content;return;}
   dialog.classList.toggle('ttz-is-notice',isNotice);
   dialog.querySelector('h2').textContent=title;
   const target=dialog.querySelector('.ttz-dialog-content');
   target.innerHTML=content;palette(target);
   const p=state?.display?.presentation||{};const bg=Number(day)===24&&p.christmas_bg?p.christmas_bg:p.modal_bg;dialog.style.removeProperty('--ttz-modal-bg');dialog.style.removeProperty('--ttz-modal-fg');if(/^#[0-9a-f]{6}$/i.test(bg)){dialog.style.setProperty('--ttz-modal-bg',bg);const rgb=bg.slice(1).match(/../g).map(v=>{v=parseInt(v,16)/255;return v<=.04045?v/12.92:((v+.055)/1.055)**2.4;});dialog.style.setProperty('--ttz-modal-fg',rgb[0]*.2126+rgb[1]*.7152+rgb[2]*.0722>.179?'#17251e':'#fffdf7');}
   const link=dialog.querySelector('.ttz-read-more');link.hidden=!url;
   if(url)link.href=url;else link.removeAttribute('href');
   dialog.dataset.animation=state?.animation||'none';
   dialog.hidden=false;
   try {HTMLDialogElement.prototype.showModal.call(dialog);dialog.scrollTop=0;dialog.querySelector('h2').focus({preventScroll:true});}
   catch {dialog.hidden=true;if(isNotice)status.innerHTML=content;else status.textContent=root.dataset.error;}
  }
  function display() {
   const d=state.display;
   if(!d)return;
   if(themes.includes(d.theme)) {
    themes.forEach(theme=>root.classList.toggle('ttz-'+theme,theme===d.theme));
    if(document.body.classList.contains('ttz-landing'))themes.forEach(theme=>document.body.classList.toggle('ttz-site-'+theme,theme===d.theme));
   }
   if(/^#[0-9a-f]{6}$/i.test(d.color)){root.style.setProperty('--ttz-accent',d.color);if(document.body.classList.contains('ttz-landing'))document.body.style.setProperty('--ttz-accent',d.color);}
   root.dataset.popup=d.notice;
   const p=d.presentation||{},link=dialog?.querySelector('.ttz-read-more');if(link)link.textContent=p.button_label||link.textContent;
   for(const [key,css]of Object.entries({button_bg:'--ttz-button-bg',button_fg:'--ttz-button-fg',button_border:'--ttz-button-border',overlay_color:'--ttz-overlay-color'})){root.style.removeProperty(css);if(/^#[0-9a-f]{6}$/i.test(p[key]))root.style.setProperty(css,p[key]);}
   root.style.removeProperty('--ttz-overlay-opacity');if(p.overlay_opacity!==''&&Number.isFinite(Number(p.overlay_opacity)))root.style.setProperty('--ttz-overlay-opacity',Math.max(0,Math.min(100,Number(p.overlay_opacity)))+'%');
   if(dialog){dialog.dataset.buttonStyle=p.button_style||'default';dialog.dataset.buttonShape=p.button_shape||'default';}

   root.setAttribute('aria-label',d.title);
   const heading=root.querySelector('.ttz-heading');
   if(heading){heading.querySelector('h2').textContent=d.title;heading.querySelector('.ttz-year').textContent=d.year;let meaning=heading.querySelector('.ttz-meaning');if(d.meaning){if(!meaning){meaning=document.createElement('p');meaning.className='ttz-meaning';heading.insertBefore(meaning,heading.querySelector('.ttz-year'));}meaning.textContent=d.meaning;}else meaning?.remove();}
   if(clock)clock.querySelector('p').textContent=d.text;
  }
  function palette(container){container.querySelectorAll('[class*="has-ttz-"]').forEach(element=>{element.classList.forEach(name=>{const m=name.match(/^has-ttz-(?:accent|background)-([0-9a-f]{6})-(color|background-color|border-color)$/i);if(m)element.style.setProperty(m[2],'#'+m[1],'important');});});}
  function storage() {
   const key=`ttz:visits:${new URL(root.dataset.endpoint,location.href).pathname}:${root.dataset.site}:${state.effective||root.dataset.calendar}:${state.display?.year}`;
   if(!state.remember||root.dataset.preview){remember=false;visits.clear();visitKey=key;}else if(key!==visitKey){visitKey=key;visits=new Set();remember=false;try{const data=JSON.parse(localStorage.getItem(key)||'null');if(data?.enabled===true){remember=true;visits=new Set(Array.isArray(data.days)?data.days.filter(n=>Number.isInteger(n)&&n>=1&&n<=24):[]);}}catch{}}
   root.querySelectorAll('[data-day]').forEach(b=>b.classList.toggle('ttz-visited',remember&&visits.has(Number(b.dataset.day))));
   const button=root.querySelector('.ttz-remember'),clear=root.querySelector('.ttz-clear-visits'),note=root.querySelector('.ttz-storage-note');if(button){button.hidden=!state.remember||!!root.dataset.preview;button.setAttribute('aria-pressed',String(remember));clear.hidden=button.hidden||!remember;note.hidden=button.hidden;}
  }
  function saveVisits(){try{if(remember)localStorage.setItem(visitKey,JSON.stringify({enabled:true,days:[...visits]}));else localStorage.removeItem(visitKey);}catch{remember=false;visits.clear();}storage();}
  function mark(day){if(!remember||!state.remember||root.dataset.preview)return;visits.add(Number(day));saveVisits();}
  function season(){
   root.querySelectorAll('[data-day]').forEach(b=>{const d=state.doors.find(d=>d.day===Number(b.dataset.day));let img=b.querySelector('.ttz-door-image');if(d?.image?.url){if(!img){img=document.createElement('img');img.className='ttz-door-image';img.loading='lazy';img.alt='';b.prepend(img);}img.src=d.image.url;img.alt='';}else img?.remove();b.classList.toggle('ttz-is-today',Number(b.dataset.day)===state.today);b.setAttribute('aria-current',Number(b.dataset.day)===state.today?'date':'false');});
   const grid=root.querySelector('.ttz-grid');if(grid&&Array.isArray(state.order))state.order.forEach(day=>{const b=grid.querySelector(`[data-day="${day}"]`);if(b)grid.append(b);});
   const today=root.querySelector('.ttz-today');if(today)today.hidden=!state.today;
   const closing=root.querySelector('.ttz-closing');if(closing){closing.hidden=!state.closing;closing.innerHTML=state.closing||'';}
   storage();if(document.body.classList.contains('ttz-landing'))palette(document.body);
  }
  function tick() {
   if(!state||!clock)return;
   const left=Math.max(0,Math.ceil(state.start-(state.now+(state.simulated?0:(performance.now()-received)/1000))));
   const enabled=root.dataset.countdown==='default'?state.display?.countdown:root.dataset.countdown!=='0';
   clock.hidden=!enabled||left<=0;
   for(const [unit,value]of Object.entries({days:Math.floor(left/86400),hours:Math.floor(left/3600)%24,minutes:Math.floor(left/60)%60,seconds:left%60}))clock.querySelector(`[data-unit="${unit}"]`).textContent=String(value).padStart(2,'0');
   if(left===0&&state.now<state.start&&!state.simulated)refresh();
  }
  async function refresh() {
   if(refreshing||!root.isConnected)return;
   refreshing=true;clearTimeout(scheduled);
   try {
    state=await request();received=performance.now();failures=0;display();
    root.querySelectorAll('[data-day]').forEach(button=>{const door=state.doors.find(d=>d.day===Number(button.dataset.day));button.dataset.locked=door?.available?'false':'true';button.removeAttribute('aria-disabled');button.classList.toggle('is-open',!!door?.available);const label=door?.available?root.dataset.available:door?.open?root.dataset.empty:root.dataset.unavailable;button.setAttribute('aria-label',`${root.dataset.dayLabel} ${button.dataset.day}: ${label}`);});
    if(!retryDay)status.textContent='';retry.hidden=!retryDay;season();tick();
    if(state.next&&!state.simulated)scheduled=setTimeout(refresh,Math.min(2147480000,Math.max(1000,(state.next-state.now)*1000+250)));
   } catch(error){status.textContent=error.message||root.dataset.error;retry.hidden=false;failures++;scheduled=setTimeout(refresh,Math.min(300000,30000*failures));}
   finally{refreshing=false;}
  }
  root.addEventListener('click',async event=>{
   const video=event.target.closest('[data-ttz-video]');
   if(video){const id=video.dataset.ttzVideo;if(!/^[A-Za-z0-9_-]{11}$/.test(id))return;const frame=document.createElement('iframe');frame.src='https://www.youtube-nocookie.com/embed/'+encodeURIComponent(id)+'?autoplay=1&rel=0';frame.title=root.dataset.videoTitle;frame.allow='autoplay; encrypted-media; picture-in-picture; fullscreen';frame.allowFullscreen=true;frame.referrerPolicy='strict-origin-when-cross-origin';frame.className='ttz-video-frame';const container=video.closest('.ttz-video'),fallback=container.querySelector('.ttz-video-link');container.replaceChildren(frame);if(fallback)container.append(fallback);return;}
   const button=event.target.closest('[data-day]');if(!button||requesting)return;
   opener=button;
   if(button.dataset.locked==='true'){
    const door=state?.doors.find(d=>d.day===Number(button.dataset.day));const message=door?.open?root.dataset.empty:root.dataset.unavailable;
    const html=door?.message;if(root.dataset.popup==='popup')show(`${root.dataset.dayLabel} ${button.dataset.day}`,html||message,true,'',button.dataset.day);else if(html)status.innerHTML=html;else status.textContent=button.getAttribute('aria-label');
    return;
   }
   requesting=true;button.setAttribute('aria-busy','true');status.textContent='';
   try{const content=await request(button.dataset.day);retryDay=null;retry.hidden=true;mark(button.dataset.day);if(content.direct&&content.url){window.location.assign(content.url);return;}show(content.title,content.html,false,content.url,button.dataset.day);}
   catch(error){status.textContent=error.message||root.dataset.error;retryDay=button.dataset.day;retry.hidden=false;}
   finally{requesting=false;button.removeAttribute('aria-busy');}
  });
  if(dialog){dialog.addEventListener('close',()=>{dialog.hidden=true;dialog.querySelector('.ttz-dialog-content').replaceChildren();opener?.focus();});dialog.addEventListener('click',event=>{if(event.target===dialog){const r=dialog.getBoundingClientRect();if(event.clientX<r.left||event.clientX>r.right||event.clientY<r.top||event.clientY>r.bottom)HTMLDialogElement.prototype.close.call(dialog);}});}
  root.querySelector('.ttz-today')?.addEventListener('click',()=>{const b=root.querySelector(`[data-day="${state?.today}"]`);if(b){b.scrollIntoView({behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth',block:'center'});b.focus({preventScroll:true});}});
  root.querySelector('.ttz-remember')?.addEventListener('click',()=>{remember=!remember;if(!remember)visits.clear();saveVisits();});
  root.querySelector('.ttz-clear-visits')?.addEventListener('click',()=>{visits.clear();saveVisits();});
  retry.addEventListener('click',()=>{const button=retryDay&&root.querySelector(`[data-day="${retryDay}"]`);if(button&&button.dataset.locked!=='true')button.click();else{retryDay=null;refresh();}});
  window.addEventListener('online',()=>{if(root.isConnected)refresh();});
  document.addEventListener('visibilitychange',()=>{if(!document.hidden&&root.isConnected&&performance.now()-received>30000)refresh();});
  refresh();
  const timer=setInterval(()=>{if(!root.isConnected){clearInterval(timer);clearTimeout(scheduled);return;}if(!document.hidden)tick();},1000);
 }
 const boot=()=>document.querySelectorAll('[data-ttz]').forEach(mount);
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot);else boot();
})();
