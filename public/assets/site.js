document.documentElement.classList.add('js');
const menu=document.querySelector('.menu-toggle'),links=document.getElementById('menu');
if(menu&&links){menu.addEventListener('click',()=>{const open=links.classList.toggle('open');menu.setAttribute('aria-expanded',String(open))});document.addEventListener('keydown',e=>{if(e.key==='Escape'&&links.classList.contains('open')){links.classList.remove('open');menu.setAttribute('aria-expanded','false');menu.focus()}})}
document.querySelectorAll('[data-print]').forEach(b=>b.addEventListener('click',()=>window.print()));
if('IntersectionObserver' in window&&!matchMedia('(prefers-reduced-motion: reduce)').matches){const observer=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('reveal');observer.unobserve(e.target)}}),{threshold:.15});document.querySelectorAll('.specimen,.journey,.entries article').forEach(e=>observer.observe(e))}

function setupPanels(selector,targetAttr){
 const buttons=[...document.querySelectorAll(selector)];
 if(!buttons.length)return;
 const panels=buttons.map(b=>document.getElementById(b.getAttribute(targetAttr)));
 function select(index){buttons.forEach((b,i)=>b.setAttribute('aria-pressed',String(i===index)));panels.forEach((p,i)=>{if(p){p.hidden=i!==index;if(i===index){p.classList.remove('reveal');void p.offsetWidth;p.classList.add('reveal')}}});}
 select(0);buttons.forEach((b,i)=>b.addEventListener('click',()=>select(i)));
}
setupPanels('[data-form-target]','data-form-target');setupPanels('[data-tour-target]','data-tour-target');

document.querySelectorAll('[data-cinematic]').forEach(video=>{
 if(!matchMedia('(prefers-reduced-motion: reduce)').matches&&matchMedia('(min-width: 851px)').matches&&!navigator.connection?.saveData){video.play().catch(()=>{});}
});
