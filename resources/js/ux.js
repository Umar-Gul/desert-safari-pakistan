/* ==========================================================
   UX layer: scroll animations and interactions (no dependencies)
   Import from resources/js/app.js:  import './ux.js';
   ========================================================== */
(() => {
  const doc = document.documentElement;
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const $  = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];

  const ready = (fn) =>
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', fn) : fn();

  ready(() => {
    doc.classList.add('js');

    /* ---------- 1. Auto-tag elements for reveal ----------
       Anything already carrying data-reveal is respected.
       Otherwise: each <main> section's heading/intro/children are tagged,
       and grid/flex card children are staggered. */
    const tag = (el, type = 'up', delay = 0) => {
      if (!el || el.hasAttribute('data-reveal')) return;
      el.setAttribute('data-reveal', type);
      if (delay) el.style.setProperty('--ux-delay', delay + 'ms');
    };

    $$('main section').forEach((section, i) => {
      if (i === 0) return; // hero animates on load, not on scroll
      const wrap = section.querySelector(':scope > div') || section;
      $$(':scope > h1, :scope > h2, :scope > p, :scope > header, :scope > .section-head', wrap)
        .forEach((el, n) => tag(el, 'up', n * 80));

      $$(':scope > .grid, :scope > .flex, :scope > ul, :scope > div > .grid', wrap).forEach((grid) => {
        [...grid.children].forEach((child, n) => {
          if (child.hasAttribute('data-no-reveal')) return;
          tag(child, 'up', Math.min(n, 8) * 90);
          child.classList.add('ux-card');
        });
      });
    });

    // cards, FAQs, gallery tiles anywhere else
    $$('.tour-card, [data-card], article.card, details').forEach((el, n) => tag(el, 'up', (n % 4) * 80));
    $$('main img:not([data-reveal])').forEach((img) => {
      if (img.closest('[data-reveal]') || img.closest('section:first-of-type')) return;
      const box = img.parentElement;
      if (box && box.children.length === 1) box.classList.add('ux-zoom');
    });
    $$('footer > div, footer .grid > *').forEach((el, n) => tag(el, 'fade', n * 60));

    /* ---------- 2. Reveal observer ---------- */
    const targets = $$('[data-reveal]');
    if (reduce || !('IntersectionObserver' in window)) {
      targets.forEach((el) => el.classList.add('is-visible'));
    } else {
      const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
          if (!e.isIntersecting) return;
          e.target.classList.add('is-visible');
          io.unobserve(e.target);
        });
      }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
      targets.forEach((el) => io.observe(el));
    }

    /* ---------- 3. Lazy images: fade in when loaded ---------- */
    $$('img[loading="lazy"]').forEach((img) => {
      const done = () => img.classList.add('is-loaded');
      img.complete && img.naturalWidth ? done() : img.addEventListener('load', done, { once: true });
      img.addEventListener('error', done, { once: true });
    });

    /* ---------- 4. Scroll progress, header, back-to-top, parallax ---------- */
    const bar = document.createElement('div');
    bar.id = 'ux-progress';
    document.body.appendChild(bar);

    const topBtn = document.createElement('button');
    topBtn.id = 'ux-top';
    topBtn.type = 'button';
    topBtn.setAttribute('aria-label', 'Back to top');
    topBtn.innerHTML =
      '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>';
    topBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' }));
    document.body.appendChild(topBtn);

    const header = $('header[data-header]') || $('body > header') || $('header');
    if (header) {
      header.classList.add('ux-header');
      // keep navbar always visible: make it sticky only if it isn't already fixed/sticky
      if (getComputedStyle(header).position === 'static') {
        header.style.position = 'sticky';
        header.style.top = '0';
        header.style.zIndex = '50';
      }
    }
    $$('header nav a').forEach((a) => a.classList.add('ux-link'));

    const parallax = $$('[data-parallax]');
    if (!parallax.length) {
      const heroImg = $('main section:first-of-type img.absolute, main section:first-of-type img[class*="object-cover"]');
      heroImg && parallax.push(heroImg);
    }

    let lastY = window.scrollY, ticking = false;
    const onScroll = () => {
      const y = window.scrollY;
      const max = doc.scrollHeight - window.innerHeight;
      bar.style.transform = `scaleX(${max > 0 ? Math.min(y / max, 1) : 0})`;
      topBtn.classList.toggle('is-on', y > 700);

      if (header) {
        header.classList.toggle('is-scrolled', y > 24); // navbar stays fixed; shadow only
      }
      if (!reduce && y < window.innerHeight * 1.2) {
        parallax.forEach((el) => { el.style.transform = `translate3d(0, ${y * 0.18}px, 0) scale(1.08)`; });
      }
      lastY = y; ticking = false;
    };
    window.addEventListener('scroll', () => {
      if (!ticking) { requestAnimationFrame(onScroll); ticking = true; }
    }, { passive: true });
    onScroll();

    /* ---------- 5. Smooth anchor links with header offset ---------- */
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a[href^="#"]');
      if (!a || a.getAttribute('href') === '#') return;
      const target = $(a.getAttribute('href'));
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
      history.pushState(null, '', a.getAttribute('href'));
    });

    /* ---------- 6. Button ripple ---------- */
    document.addEventListener('pointerdown', (e) => {
      const btn = e.target.closest('.btn, .btn-primary, .btn-whatsapp');
      if (!btn || reduce) return;
      const r = btn.getBoundingClientRect();
      const size = Math.max(r.width, r.height);
      const s = document.createElement('span');
      s.className = 'ux-ripple';
      s.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX - r.left - size / 2}px;top:${e.clientY - r.top - size / 2}px`;
      btn.appendChild(s);
      s.addEventListener('animationend', () => s.remove());
    });

    /* ---------- 7. Count-up numbers: <span data-count="1500" data-suffix="+"> ---------- */
    const counters = $$('[data-count]');
    if (counters.length) {
      const run = (el) => {
        const end = parseFloat(el.dataset.count) || 0;
        const suffix = el.dataset.suffix || '';
        if (reduce) { el.textContent = end.toLocaleString() + suffix; return; }
        const dur = 1600, t0 = performance.now();
        const tick = (t) => {
          const p = Math.min((t - t0) / dur, 1);
          const eased = 1 - Math.pow(1 - p, 3);
          el.textContent = Math.round(end * eased).toLocaleString() + suffix;
          if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
      };
      const co = new IntersectionObserver((entries) => {
        entries.forEach((e) => { if (e.isIntersecting) { run(e.target); co.unobserve(e.target); } });
      }, { threshold: 0.6 });
      counters.forEach((c) => co.observe(c));
    }

    /* ---------- 8. Smooth <details> FAQ: close siblings in the same group ---------- */
    $$('details[data-accordion] summary, .faq details summary').forEach((sum) => {
      sum.addEventListener('click', () => {
        const group = sum.closest('[data-accordion], .faq');
        if (!group) return;
        $$('details[open]', group).forEach((d) => { if (d !== sum.parentElement) d.removeAttribute('open'); });
      });
    });
  });
})();