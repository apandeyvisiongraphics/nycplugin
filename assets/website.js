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
