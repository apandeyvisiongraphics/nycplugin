/* Vision Graphics NYC landing pages: quote popup and scroll animations.
   Loaded only on "landing" pages (see vgnyc_render). Works without it: buttons fall back to #inquiry. */
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
