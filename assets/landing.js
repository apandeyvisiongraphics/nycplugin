/* Vision Graphics NYC landing pages: quote popup and scroll animations.
   Loaded on "landing" pages and "blog" posts (see vgnyc_render). Works without it: buttons fall back to #inquiry. */
(() => {
  const doc = document.documentElement;
  const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ---- Reveal cards, quotes, images and steps as they scroll into view ----
  if (!reduce && 'IntersectionObserver' in window) {
    doc.classList.add('vg-js');
    const items = document.querySelectorAll('.vg-landing .vg-card, .vg-landing .vg-quote, .vg-landing .vg-gallery__item, .vg-landing .vg-media, .vg-landing .vg-steps__list li, .vg-landing .faqs details');
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); } });
    }, { rootMargin: '0px 0px -6% 0px' });
    items.forEach((el) => {
      const siblings = Array.from(el.parentElement.children);
      el.style.setProperty('--vg-delay', `${Math.min(siblings.indexOf(el), 5) * 70}ms`);
      el.classList.add('vg-reveal');
      io.observe(el);
    });
  }

  // ---- Blog posts: reading progress bar + highlight the current heading in the table of contents ----
  const progress = document.querySelector('.vg-blog__progress');
  const article = document.querySelector('.vg-blog__body');
  if (progress && article) {
    // Current heading = the last section whose top has passed 35% of the viewport.
    const toc = Array.from(document.querySelectorAll('.vg-toc a[href^="#"]'))
      .map((a) => ({ a, el: document.getElementById(decodeURIComponent(a.getAttribute('href').slice(1))) }))
      .filter((t) => t.el);
    let ticking = false;
    let current = null;
    const update = () => {
      ticking = false;
      const r = article.getBoundingClientRect();
      const total = r.height - window.innerHeight * 0.6;
      const p = total > 0 ? Math.min(1, Math.max(0, -r.top / total)) : 1;
      progress.style.setProperty('--vg-progress', p.toFixed(4));
      const line = window.innerHeight * 0.35;
      let active = null;
      toc.forEach((t) => { if (t.el.getBoundingClientRect().top <= line) active = t.a; });
      if (active !== current) {
        toc.forEach((t) => t.a.classList.toggle('is-active', t.a === active));
        current = active;
      }
    };
    window.addEventListener('scroll', () => { if (!ticking) { ticking = true; requestAnimationFrame(update); } }, { passive: true });
    window.addEventListener('resize', update);
    update();
  }

  // ---- Quote popup: every quote button opens the page's form in a modal ----
  const dialog = document.getElementById('vg-inquiry-modal');
  const home = document.querySelector('#inquiry .vg-inquiry__form');
  const form = home && home.querySelector('.vgnyc-form');
  if (!dialog || !form || typeof dialog.showModal !== 'function') return;
  const slot = dialog.querySelector('.vg-modal__slot');
  let opener = null;

  // Only buttons inside the page content open the popup; header and footer buttons keep linking to /nyc/contact/.
  const isQuoteButton = (a) => {
    if (!a.matches('main a.btn')) return false;
    const href = a.getAttribute('href') || '';
    return href === '#inquiry' || /^(https?:\/\/[^/]+)?\/nyc\/contact\/(\?.*)?$/.test(href);
  };

  document.addEventListener('click', (e) => {
    const a = e.target.closest('a');
    if (!a || !isQuoteButton(a) || e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
    e.preventDefault();
    opener = a;
    slot.appendChild(form);                 // the same CF7 form moves into the popup (one form per page)
    dialog.showModal();
    doc.classList.add('vg-modal-open');
    const first = form.querySelector('input:not([type=hidden]), select, textarea');
    if (first) setTimeout(() => first.focus({ preventScroll: true }), 60);
  });

  dialog.addEventListener('close', () => {
    home.insertBefore(form, home.firstChild); // put it back in the on-page form section
    doc.classList.remove('vg-modal-open');
    if (opener) opener.focus({ preventScroll: true });
  });
  dialog.querySelector('.vg-modal__close').addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (e) => { if (e.target === dialog) dialog.close(); }); // click on the backdrop
})();
