{{-- Adds a × clear button to every input[data-clearable]. Clearing a search that is already
     applied (it is in the URL) resubmits the form, so the full list comes back. --}}
<style>
.sc-wrap{position:relative;display:block;flex:1;min-width:0}
.sc-wrap>input{width:100%;padding-right:44px!important}
.sc-wrap>input::-webkit-search-cancel-button{-webkit-appearance:none;appearance:none;display:none}
.sc-clear{position:absolute;top:50%;right:8px;z-index:3;display:grid;place-items:center;width:30px;height:30px;margin-top:-15px;padding:0;border:0;border-radius:50%;background:#e9eef6;color:#4b5b73;cursor:pointer;transition:background .15s}
.sc-clear:hover{background:#dbe3ef;color:#17233c}
.sc-clear:focus-visible{outline:none;box-shadow:0 0 0 3px rgba(57,126,246,.3)}
.sc-clear svg{width:14px;height:14px;fill:currentColor}
button.sc-clear{flex:none!important;min-width:0!important;min-height:0!important;width:30px!important;height:30px!important;padding:0!important;border:0!important;border-radius:50%!important;background:#e9eef6!important;color:#4b5b73!important;box-shadow:none!important;font-size:0!important}
button.sc-clear:hover{background:#dbe3ef!important;color:#17233c!important}
button.sc-clear[hidden]{display:none!important}
</style>
<script>
document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('input[data-clearable]').forEach(input=>{
  let wrap=input.parentElement;
  if(!wrap.classList.contains('sc-wrap')&&!wrap.classList.contains('smart-search')){wrap=document.createElement('span');wrap.className='sc-wrap';input.parentNode.insertBefore(wrap,input);wrap.appendChild(input);}
  wrap.style.position=wrap.style.position||'relative';
  const btn=document.createElement('button');btn.type='button';btn.className='sc-clear';btn.setAttribute('aria-label','Clear search');btn.title='Clear search';
  btn.innerHTML='<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.3 5.7 12 12l6.3 6.3-1.4 1.4L10.6 13.4 4.3 19.7l-1.4-1.4L9.2 12 2.9 5.7l1.4-1.4 6.3 6.3 6.3-6.3Z"/></svg>';
  wrap.appendChild(btn);
  const sync=()=>{btn.hidden=input.value==='';};
  input.addEventListener('input',sync);sync();
  btn.addEventListener('click',()=>{
    input.value='';input.dispatchEvent(new Event('input',{bubbles:true}));sync();
    const form=input.form,name=input.name;
    if(form&&name&&(form.method||'get').toLowerCase()==='get'&&new URLSearchParams(location.search).get(name)){
      const url=new URL(form.action||location.href,location.href);
      new FormData(form).forEach((v,k)=>{if(k!==name&&v!=='')url.searchParams.set(k,v);});
      url.searchParams.delete(name);url.searchParams.delete('page');location.href=url.toString();return;
    }
    input.focus();
  });
});});
</script>
