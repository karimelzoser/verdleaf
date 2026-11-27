document.documentElement.classList.add('js');
const menu=document.querySelector('.menu-toggle'),links=document.getElementById('menu');
if(menu&&links){menu.addEventListener('click',()=>{const open=links.classList.toggle('open');menu.setAttribute('aria-expanded',String(open))});document.addEventListener('keydown',e=>{if(e.key==='Escape'&&links.classList.contains('open')){links.classList.remove('open');menu.setAttribute('aria-expanded','false');menu.focus()}})}
document.querySelectorAll('[data-print]').forEach(b=>b.addEventListener('click',()=>window.print()));
if('IntersectionObserver' in window&&!matchMedia('(prefers-reduced-motion: reduce)').matches){const observer=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('reveal');observer.unobserve(e.target)}}),{threshold:.15});document.querySelectorAll('.specimen,.journey,.entries article').forEach(e=>observer.observe(e))}
