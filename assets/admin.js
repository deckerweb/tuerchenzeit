(() => {
 'use strict';
 const input=document.getElementById('ttz-color'),mount=document.getElementById('ttz-color-root');
 if(input&&mount&&window.wp?.components&&wp.element){
  const h=wp.element.createElement;
  function Picker(){const [color,setColor]=wp.element.useState(input.value);return h(wp.components.Dropdown,{className:'ttz-color-dropdown',contentClassName:'ttz-color-popover',renderToggle:({isOpen,onToggle})=>h(wp.components.Button,{variant:'secondary',onClick:onToggle,'aria-expanded':isOpen,'aria-label':ttzAdmin.color},h('span',{className:'ttz-color-chip',style:{backgroundColor:color}}),color),renderContent:()=>h(wp.components.ColorPicker,{color,enableAlpha:false,onChange:value=>{if(/^#[0-9a-f]{6}$/i.test(value)){input.value=value;setColor(value);}}})});}
  if(wp.element.createRoot)wp.element.createRoot(mount).render(h(Picker));else wp.element.render(h(Picker),mount);
  input.classList.add('screen-reader-text');
 }

 document.querySelectorAll('[data-optional-color]').forEach(input=>{
  const mount=input.parentElement.querySelector('[data-color-root]');
  if(!mount||!window.wp?.components||!wp.element)return;
  const h=wp.element.createElement;
  function OptionalPicker(){const [color,setColor]=wp.element.useState(input.value);function change(value){if(value===''||/^#[0-9a-f]{6}$/i.test(value)){input.value=value;setColor(value);}}wp.element.useEffect(()=>{const sync=()=>setColor(input.value);input.addEventListener('input',sync);return()=>input.removeEventListener('input',sync);},[]);return h(wp.element.Fragment,null,h(wp.components.Dropdown,{renderToggle:({isOpen,onToggle})=>h(wp.components.Button,{variant:'secondary',onClick:onToggle,'aria-expanded':isOpen,'aria-label':input.labels[0].textContent},h('span',{className:'ttz-color-chip',style:{backgroundColor:color||'transparent'}}),'▾'),renderContent:()=>h(wp.components.ColorPicker,{color:color||'#235b4e',enableAlpha:false,onChange:change})}),h(wp.components.Button,{variant:'tertiary',onClick:()=>change('')},ttzAdmin.reset));}
  if(wp.element.createRoot)wp.element.createRoot(mount).render(h(OptionalPicker));else wp.element.render(h(OptionalPicker),mount);
 });
 document.querySelectorAll('[data-notice-editor]').forEach(editor=>{
  const field=editor.querySelector('textarea'),preview=editor.querySelector('[data-notice-preview]');
  function update(){let value=field.value;for(const [token,text]of Object.entries({'{tuerchen}':field.dataset.exampleDoor,'{tag}':'4','{datum}':field.dataset.exampleDate,'{uhrzeit}':'00:00'}))value=value.split(token).join(text);const source=new DOMParser().parseFromString(value,'text/html');function clean(node){if(node.nodeType===Node.TEXT_NODE)return document.createTextNode(node.textContent);const target=document.createElement(['P','BR','STRONG','EM'].includes(node.nodeName)?node.nodeName:'span');node.childNodes.forEach(child=>target.append(clean(child)));return target;}preview.replaceChildren(...Array.from(source.body.childNodes,clean));}
  editor.querySelectorAll('[data-insert],[data-format]').forEach(button=>button.addEventListener('click',()=>{const start=field.selectionStart,end=field.selectionEnd,tag=button.dataset.format;const value=tag?'<'+tag+'>'+field.value.slice(start,end)+'</'+tag+'>':button.dataset.insert;field.setRangeText(value,start,end,'end');field.focus();update();}));field.addEventListener('input',update);update();
 });
 document.querySelectorAll('[data-date-preview]').forEach(link=>{const base=link.href;link.addEventListener('click',()=>{const url=new URL(base);url.searchParams.set('date',document.querySelector('[data-simulated-date]').value);link.href=url.href;});});
 document.querySelectorAll('[data-card]').forEach(card=>{
  const choose=card.querySelector('[data-choose-image]'),clear=card.querySelector('[data-clear-image]');
  choose?.addEventListener('click',()=>{if(!window.wp?.media)return;const frame=wp.media({multiple:false,library:{type:'image'}});frame.on('select',()=>{const item=frame.state().get('selection').first().toJSON();card.querySelector('[data-field="image"]').value=item.id;const img=document.createElement('img');img.src=item.sizes?.thumbnail?.url||item.url;img.alt=item.alt||'';card.querySelector('.ttz-card-image').replaceChildren(img);});frame.open();});
  clear?.addEventListener('click',()=>{card.querySelector('[data-field="image"]').value='0';card.querySelector('.ttz-card-image').replaceChildren();});
 });
 const picker=document.getElementById('ttz-picker');
 let active,opener,generation=0,timeout,controller;
 if(picker){
  const source=picker.querySelector('[data-picker-source]'),query=picker.querySelector('[data-picker-search]'),types=picker.querySelector('[data-picker-type]'),results=picker.querySelector('[data-picker-results]'),status=picker.querySelector('[data-picker-status]'),mode=picker.querySelector('[data-picker-mode]'),create=picker.querySelector('[data-create-own]');
  const field=name=>active?.querySelector(`[data-field="${name}"]`);
  function close(){HTMLDialogElement.prototype.close.call(picker);}
  function controls(){const reference=source.value==='reference';picker.querySelector('[data-type-wrap]').hidden=!reference;picker.querySelector('[data-mode-wrap]').hidden=!reference;picker.querySelector('[data-create-wrap]').hidden=reference;}
  function assign(item){
   field('id').value=item.id;field('source').value=source.value;field('mode').value=mode.value;
   active.querySelector('[data-card-title]').textContent=item.title;
   active.querySelector('[data-card-source]').textContent=source.selectedOptions[0].text;
   active.querySelector('[data-card-status]').textContent=item.status==='publish'?ttzAdmin.ready:ttzAdmin.draft;active.dataset.ready=item.status==='publish'?'ready':'draft';
   const edit=active.querySelector('[data-card-edit]');edit.href=item.edit;edit.hidden=false;active.querySelector('[data-remove]').hidden=false;
   document.querySelector('.ttz-assignment-status').textContent=ttzAdmin.saved;close();
  }
  async function send(action,values,signal){const body=new URLSearchParams({action,nonce:ttzAdmin.nonce,...values});const response=await fetch(ttzAdmin.url,{method:'POST',credentials:'same-origin',body,signal});const data=await response.json();if(!data.success)throw new Error(data.data?.message||ttzAdmin.error);return data.data;}
  function search(){clearTimeout(timeout);const current=++generation;controller?.abort();results.replaceChildren();status.textContent='';if(query.value.trim().length<2)return;
   timeout=setTimeout(async()=>{controller=new AbortController();try{const items=await send('ttz_search',{source:source.value,type:types.value,q:query.value},controller.signal);if(current!==generation)return;status.textContent=items.length?'':ttzAdmin.empty;items.forEach(item=>{const li=document.createElement('li'),button=document.createElement('button'),title=document.createElement('strong'),detail=document.createElement('span');button.type='button';title.textContent=item.title;detail.textContent=`${item.type} · ${item.status==='publish'?ttzAdmin.ready:ttzAdmin.draft}`;button.append(title,detail);button.addEventListener('click',()=>assign(item));li.append(button);results.append(li);});}catch(error){if(error.name!=='AbortError'&&current===generation)status.textContent=error.message;}},250);
  }
  document.querySelectorAll('[data-card]').forEach(card=>{
   card.querySelector('[data-pick]').addEventListener('click',event=>{active=card;opener=event.currentTarget;source.value=field('source').value;mode.value=field('mode').value;types.value='';query.value='';results.replaceChildren();status.textContent='';picker.querySelector('h2').textContent=ttzAdmin.heading.replace('%d',card.dataset.card);controls();picker.hidden=false;HTMLDialogElement.prototype.showModal.call(picker);query.focus();});
   card.querySelector('[data-remove]').addEventListener('click',()=>{card.dataset.ready='empty';card.querySelector('[data-field="id"]').value='0';card.querySelector('[data-card-title]').textContent=ttzAdmin.none;card.querySelector('[data-card-status]').textContent=ttzAdmin.none;card.querySelector('[data-card-edit]').hidden=true;card.querySelector('[data-remove]').hidden=true;document.querySelector('.ttz-assignment-status').textContent=ttzAdmin.removed;});
  });
  source.addEventListener('change',()=>{controls();search();});types.addEventListener('change',search);query.addEventListener('input',search);
  create.addEventListener('click',async()=>{if(!query.value.trim()){query.focus();return;}const target=active;create.disabled=true;source.disabled=true;query.disabled=true;generation++;controller?.abort();clearTimeout(timeout);try{const item=await send('ttz_create_door',{title:query.value.trim()});if(active===target&&picker.open){assign(item);}else{document.querySelector('.ttz-assignment-status').textContent=item.title+': '+ttzAdmin.draft;}}catch(error){status.textContent=error.message;}finally{create.disabled=false;source.disabled=false;query.disabled=false;}});
  picker.addEventListener('close',()=>{picker.hidden=true;generation++;controller?.abort();clearTimeout(timeout);opener?.focus();});
 }
 for(const [selector,id]of [['[data-ttz-help]','ttz-help'],['[data-ttz-changelog]','ttz-changelog']]){
  const link=document.querySelector(selector),dialog=document.getElementById(id);if(!link||!dialog)continue;
  link.addEventListener('click',event=>{try{dialog.hidden=false;HTMLDialogElement.prototype.showModal.call(dialog);event.preventDefault();}catch{dialog.hidden=true;}});
  dialog.addEventListener('close',()=>{dialog.hidden=true;link.focus();});
 }
 const docFrame=document.querySelector('.ttz-documentation-frame');
 if(docFrame){docFrame.addEventListener('load',()=>{try{docFrame.contentDocument.addEventListener('keydown',event=>{if(event.key==='Escape'){const dialog=docFrame.closest('dialog');if(dialog?.open){event.preventDefault();HTMLDialogElement.prototype.close.call(dialog);}}});}catch{/* The native close button and standalone link stay available. */}});}
 document.querySelectorAll('.ttz-admin-dialog').forEach(dialog=>dialog.addEventListener('click',event=>{if(event.target===dialog){const r=dialog.getBoundingClientRect();if(event.clientX<r.left||event.clientX>r.right||event.clientY<r.top||event.clientY>r.bottom)HTMLDialogElement.prototype.close.call(dialog);}}));

 const form=document.querySelector('body.post-type-ttz_calendar form#post'),publish=document.getElementById('publish');
 if(form&&publish){
  const snapshot=()=>JSON.stringify(Array.from(form.elements).filter(e=>e.name&&(/^(ttz_|post_title$|post_status$)/.test(e.name))).map(e=>[e.name,e.type==='checkbox'||e.type==='radio'?e.checked:e.value]));
  const original=snapshot();let dirty=false,submitting=false;
  const initialStatus=document.getElementById('original_post_status')?.value;
  const label=initialStatus==='publish'||initialStatus==='future'?ttzAdmin.save:ttzAdmin.publish;
  const originalButton=publish.value||publish.textContent;
  let lastButton=originalButton,contextualLabel=label;
  const dock=document.createElement('div');dock.className='ttz-save-dock';dock.hidden=true;
  const message=document.createElement('span'),save=document.createElement('button');save.type='button';save.textContent=label;save.addEventListener('click',()=>publish.click());dock.append(message,save);document.body.append(dock);
  function refresh(){dirty=snapshot()!==original;document.body.classList.toggle('ttz-dirty',dirty);const state=dirty?ttzAdmin.dirty:ttzAdmin.clean;document.querySelectorAll('.ttz-save-state').forEach(e=>{if(e.textContent!==state)e.textContent=state;});message.textContent=state;const currentButton=publish.value||publish.textContent;if(currentButton!==lastButton)contextualLabel=currentButton===originalButton?label:currentButton.replace(/^● /,'');const text=(dirty?'● ':'')+contextualLabel;lastButton=text;if(publish.tagName==='INPUT')publish.value=text;else publish.textContent=text;save.textContent=text;save.disabled=publish.disabled;const cards=Array.from(document.querySelectorAll('[data-card]'));const count=cards.filter(e=>e.dataset.ready==='ready').length;const progress=document.querySelector('.ttz-live-progress');if(progress){progress.textContent=ttzAdmin.progress.replace('%d',count);progress.closest('a')?.setAttribute('aria-label',progress.textContent);}}
  form.addEventListener('input',refresh);form.addEventListener('change',refresh);
  // Media and React controls also update hidden inputs without native change events.
  setInterval(refresh,600);refresh();
  form.addEventListener('submit',()=>{submitting=true;save.disabled=true;});
  window.addEventListener('beforeunload',e=>{if(dirty&&!submitting){e.preventDefault();e.returnValue='';}});
  new IntersectionObserver(entries=>{dock.hidden=entries[0].isIntersecting;},{threshold:0.1}).observe(publish);
 }

 const preview=document.querySelector('.ttz-popup-preview');
 if(preview){
  const value=name=>document.querySelector(`[name="ttz_settings[${name}]"]`)?.value||'';
  const color=(name,fallback)=>/^#[0-9a-f]{6}$/i.test(value(name))?value(name):fallback;
  const modal=preview.querySelector('.ttz-preview-modal'),overlay=preview.querySelector('.ttz-preview-overlay'),button=preview.querySelector('.ttz-preview-button');
  function renderPreview(){
   const christmas=preview.querySelector('[data-preview-christmas]').checked;
   const custom=christmas&&value('christmas_bg')?color('christmas_bg',''):color('modal_bg','');
   const background=custom||'#fffdf7';let foreground='#22382e';
   if(custom){const rgb=custom.slice(1).match(/../g).map(v=>{v=parseInt(v,16)/255;return v<=.04045?v/12.92:((v+.055)/1.055)**2.4;});foreground=rgb[0]*.2126+rgb[1]*.7152+rgb[2]*.0722>.179?'#17251e':'#fffdf7';}
   modal.style.backgroundColor=background;modal.style.color=foreground;
   overlay.style.backgroundColor=color('overlay_color','#0d211a');const opacity=value('overlay_opacity');overlay.style.opacity=opacity===''?'.79':String(Math.max(0,Math.min(100,Number(opacity)))/100);
   const style=value('button_style');button.style.backgroundColor=style==='outline'?'transparent':color('button_bg',style==='filled'?'#235b4e':'transparent');
   const text=color('button_fg',style==='filled'?'#fffdf7':(custom?foreground:'#235b4e'));button.style.color=text;button.style.borderColor=color('button_border',text);
   button.style.borderRadius=({square:'0',soft:'8px',pill:'999px'})[value('button_shape')]||'4px';button.textContent=value('button_label')||ttzAdmin.full;
  }
  preview.addEventListener('change',renderPreview);document.querySelector('#ttz-settings')?.addEventListener('input',renderPreview);document.querySelector('#ttz-settings')?.addEventListener('change',renderPreview);setInterval(renderPreview,600);renderPreview();
 }

 document.querySelectorAll('[data-ttz-copy]').forEach(button=>button.addEventListener('click',async()=>{
  const code=button.parentElement.querySelector('code'),status=button.parentElement.querySelector('.ttz-copy-status');if(!code)return;
  try{if(!navigator.clipboard?.writeText)throw new Error();await navigator.clipboard.writeText(code.textContent);status.textContent=ttzAdmin.copied;}
  catch{const range=document.createRange();range.selectNodeContents(code);const selection=window.getSelection();selection.removeAllRanges();selection.addRange(range);status.textContent=ttzAdmin.copyFallback;}
 }));
 function jumpToDoor(card){card.scrollIntoView({behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth',block:'center'});card.focus({preventScroll:true});}
 document.querySelectorAll('[data-door-jump]').forEach(link=>link.addEventListener('click',event=>{const card=document.getElementById(link.hash.slice(1));if(card){event.preventDefault();jumpToDoor(card);}}));
 let lastDoor=0;
 document.querySelectorAll('[data-next-unfinished]').forEach(link=>link.addEventListener('click',event=>{event.preventDefault();const cards=[...document.querySelectorAll('[data-card]')].filter(card=>card.dataset.ready!=='ready');const status=document.querySelector('.ttz-jump-status');if(!cards.length){status.textContent=ttzAdmin.allReady;return;}status.textContent='';const next=cards.find(card=>Number(card.dataset.card)>lastDoor)||cards[0];lastDoor=Number(next.dataset.card);jumpToDoor(next);}));
 document.querySelectorAll('.ttz-checklist-wrap').forEach(wrap=>{
  const show=wrap.querySelector('[data-show-checklist]'),hide=wrap.querySelector('[data-hide-checklist]'),list=wrap.querySelector('.ttz-start-checklist'),status=wrap.querySelector('.ttz-checklist-status');
  async function setHidden(hidden){show.disabled=hide.disabled=true;status.textContent='';try{const response=await fetch(ttzAdmin.url,{method:'POST',credentials:'same-origin',body:new URLSearchParams({action:'ttz_checklist',nonce:ttzAdmin.checklistNonce,calendar:wrap.dataset.calendar,hidden:hidden?'1':'0'})});const result=await response.json();if(!response.ok||!result.success)throw new Error();list.hidden=hidden;show.hidden=!hidden;show.disabled=hide.disabled=false;(hidden?show:hide).focus();}catch{status.textContent=ttzAdmin.checklistError;}finally{show.disabled=hide.disabled=false;}}
  show.addEventListener('click',()=>setHidden(false));hide.addEventListener('click',()=>setHidden(true));
 });
})();
