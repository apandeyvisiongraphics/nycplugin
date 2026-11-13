(() => {
  const carousel = document.querySelector('.vgnyc-carousel');
  if (!carousel) return;
  const slides = Array.from(carousel.querySelectorAll('.reference-slide'));
  let current = 0;
  function move(delta) {
    current = (current + delta + slides.length) % slides.length;
    slides.forEach((slide, index) => { slide.hidden = index !== current; });
  }
  carousel.querySelector('.reference-prev').addEventListener('click', () => move(-1));
  carousel.querySelector('.reference-next').addEventListener('click', () => move(1));
})();

// Header: shadow once scrolled, current-page highlight, hover-to-open dropdowns on desktop,
// one dropdown open at a time, close on Escape / outside click, lock page scroll under the mobile menu.
(() => {
  const header = document.querySelector('.main-header');
  if (!header) return;
  const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  const here = location.pathname.replace(/\/?$/, '/');
  document.querySelectorAll('.desktop-nav > a, .product-ribbon a, .export-mobile nav a').forEach((a) => {
    if (a.getAttribute('href') === here) a.setAttribute('aria-current', 'page');
  });

  const menus = Array.from(header.querySelectorAll('.desktop-nav > details, .export-mobile'));
  const mobile = header.querySelector('.export-mobile');
  const sync = () => document.documentElement.classList.toggle('vg-menu-open', !!(mobile && mobile.open && getComputedStyle(mobile).display !== 'none'));
  menus.forEach((d) => d.addEventListener('toggle', () => {
    if (d.open) menus.forEach((o) => { if (o !== d) o.open = false; });
    sync();
  }));
  const closeAll = () => menus.forEach((d) => { d.open = false; });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAll(); });
  document.addEventListener('click', (e) => { if (!header.contains(e.target)) closeAll(); });
  window.addEventListener('resize', sync);

  if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    header.querySelectorAll('.desktop-nav > details').forEach((d) => {
      let t; let openedAt = 0;
      d.addEventListener('mouseenter', () => { clearTimeout(t); if (!d.open) { d.open = true; openedAt = Date.now(); } });
      // A click right after hover-opening would toggle it shut again; keep it open instead.
      d.querySelector('summary').addEventListener('click', (e) => { if (d.open && Date.now() - openedAt < 600) e.preventDefault(); });
      d.addEventListener('mouseleave', () => { t = setTimeout(() => { d.open = false; }, 180); });
    });
  }
})();
