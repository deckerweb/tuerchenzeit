(() => {
 'use strict';
 if(!window.wp?.data||!window.ttzCommand)return;
 wp.domReady(()=>{const commands=wp.data.dispatch('core/commands');if(!commands?.registerCommand)return;
 commands.registerCommand({name:'tuerchenzeit/new-door',label:ttzCommand.label,category:'action',callback:({close})=>{close();window.location.assign(ttzCommand.url);}});
 });
})();
