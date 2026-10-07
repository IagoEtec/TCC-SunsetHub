(() => {
  'use strict';

  const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
  const coarse = window.matchMedia?.('(pointer: coarse)').matches;
  const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

  const surfaceSelectors = [
    '.liquid-glass-surface', '.glass', '.glass-card', '.hero_card', '.card', '.card_dashboard',
    '.perfil_card', '.perfil_header-cover', '.settings_card', '.dashboard_card', '.stat-card',
    '.login_box', '.cadastro_box', '.cadastro_card', '.login_card', '.form_container',
    '.support_card', '.feature_card', '.info_card', '.content-card', '.pagina_card',
    '.sobre_card', '.missao_card', '.recurso_card', '.beneficio_card', '.step_card',
    '.resultado-card', '.chart_card', '.summary_card', '.legend_card', '.history_card',
    '.prefs_card', '.form-card', '.card-stat', '.modulo-card', '.aula-item', '.quiz-card',
    '.streak-modal-card', '.item-card', '.saldo-card', '.como-ganhar-box', '.dropdown_menu',
    '.crop-dialog', 'section[class*="card"]', 'article'
  ];

  const magneticSelectors = '.btn_primary, .btn_secondary, .btn_editar_perfil, button, input[type="submit"], .nav-link, .navbar a';
  const surfaceSelector = surfaceSelectors.join(', ');
  const excluded = (element) => element.closest('nav, header, .navbar, .modal_overlay') && !element.matches('.crop-dialog, .quiz-card, .streak-modal-card');

  const addRipple = (element, event) => {
    if (reduceMotion || event.pointerType === 'keyboard') return;
    const rect = element.getBoundingClientRect();
    const ripple = document.createElement('span');
    ripple.className = 'lg-water-ripple';
    ripple.style.left = `${event.clientX - rect.left}px`;
    ripple.style.top = `${event.clientY - rect.top}px`;
    const diameter = Math.max(rect.width, rect.height) * 1.35;
    ripple.style.width = `${diameter}px`;
    ripple.style.height = `${diameter}px`;
    element.appendChild(ripple);
    ripple.addEventListener('animationend', () => ripple.remove(), { once: true });
  };

  const bindSurface = (element) => {
    if (excluded(element) || element.dataset.liquidGlassBound === '1') return;
    element.dataset.liquidGlassBound = '1';
    element.classList.add('lg-card-surface');
    element.setAttribute('data-liquid-glass', 'true');
    element.style.setProperty('--lg-x', '50%');
    element.style.setProperty('--lg-y', '0%');
    element.style.setProperty('--lg-scroll-shift', '0px');
    if (reduceMotion || coarse) return;

    element.addEventListener('pointerenter', () => element.classList.add('lg-water-active'), { passive: true });
    element.addEventListener('pointermove', (event) => {
      const rect = element.getBoundingClientRect();
      const x = clamp((event.clientX - rect.left) / rect.width, 0, 1);
      const y = clamp((event.clientY - rect.top) / rect.height, 0, 1);
      element.style.setProperty('--lg-x', `${x * 100}%`);
      element.style.setProperty('--lg-y', `${y * 100}%`);
      element.style.setProperty('--lg-tilt-x', `${(0.5 - y) * 1.15}deg`);
      element.style.setProperty('--lg-tilt-y', `${(x - 0.5) * 1.15}deg`);
    }, { passive: true });
    element.addEventListener('pointerdown', (event) => addRipple(element, event), { passive: true });
    element.addEventListener('pointerleave', () => {
      element.classList.remove('lg-water-active');
      element.style.setProperty('--lg-x', '50%');
      element.style.setProperty('--lg-y', '0%');
      element.style.setProperty('--lg-tilt-x', '0deg');
      element.style.setProperty('--lg-tilt-y', '0deg');
    }, { passive: true });
  };

  const bindMagnetic = (element) => {
    if (element.dataset.liquidMagneticBound === '1') return;
    element.dataset.liquidMagneticBound = '1';
    if (reduceMotion || coarse) return;
    element.addEventListener('pointermove', (event) => {
      const rect = element.getBoundingClientRect();
      element.style.setProperty('--lg-mx', `${clamp((event.clientX - rect.left - rect.width / 2) * .08, -5, 5)}px`);
      element.style.setProperty('--lg-my', `${clamp((event.clientY - rect.top - rect.height / 2) * .08, -4, 4)}px`);
      element.classList.add('lg-magnetic-active');
    }, { passive: true });
    element.addEventListener('pointerleave', () => {
      element.style.setProperty('--lg-mx', '0px');
      element.style.setProperty('--lg-my', '0px');
      element.classList.remove('lg-magnetic-active');
    }, { passive: true });
  };

  const bindScrollParallax = () => {
    if (reduceMotion || coarse) return;
    let frame = 0;
    const update = () => {
      frame = 0;
      const center = innerHeight / 2;
      document.querySelectorAll('.lg-card-surface').forEach((element) => {
        const rect = element.getBoundingClientRect();
        if (rect.bottom < -120 || rect.top > innerHeight + 120) return;
        const offset = clamp((rect.top + rect.height / 2 - center) * -0.035, -10, 10);
        element.style.setProperty('--lg-scroll-shift', `${offset.toFixed(2)}px`);
      });
    };
    const request = () => { if (!frame) frame = requestAnimationFrame(update); };
    window.addEventListener('scroll', request, { passive: true });
    window.addEventListener('resize', request, { passive: true });
    request();
  };

  const initCursor = () => {
    if (reduceMotion || coarse || document.querySelector('.lg-cursor-orb')) return;
    const orb = document.createElement('span');
    orb.className = 'lg-cursor-orb';
    orb.setAttribute('aria-hidden', 'true');
    document.body.appendChild(orb);
    const haze = document.createElement('span');
    haze.className = 'lg-parallax-haze';
    haze.setAttribute('aria-hidden', 'true');
    document.body.appendChild(haze);
    let raf = 0;
    let targetX = innerWidth / 2;
    let targetY = innerHeight / 2;
    let currentX = targetX;
    let currentY = targetY;
    const render = () => {
      currentX += (targetX - currentX) * .14;
      currentY += (targetY - currentY) * .14;
      orb.style.transform = `translate3d(${currentX - 7}px, ${currentY - 7}px, 0)`;
      haze.style.transform = `translate3d(${(currentX / innerWidth - .5) * 18}px, ${(currentY / innerHeight - .5) * 14}px, 0)`;
      raf = requestAnimationFrame(render);
    };
    window.addEventListener('pointermove', (event) => { targetX = event.clientX; targetY = event.clientY; }, { passive: true });
    window.addEventListener('pointerleave', () => { orb.classList.add('lg-cursor-away'); haze.classList.add('lg-cursor-away'); }, { passive: true });
    window.addEventListener('pointerenter', () => { orb.classList.remove('lg-cursor-away'); haze.classList.remove('lg-cursor-away'); }, { passive: true });
    raf = requestAnimationFrame(render);
  };

  const bindPageLinks = () => {
    document.querySelectorAll('a[href]').forEach((link) => {
      const href = link.getAttribute('href');
      if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || link.target === '_blank' || link.dataset.lgTransition === 'off') return;
      if (link.origin !== window.location.origin) return;
      try { if (new URL(link.href).pathname === window.location.pathname) link.classList.add('lg-current-page'); } catch (_) { return; }
      if (reduceMotion) return;
      link.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const target = new URL(link.href);
        if (target.origin !== window.location.origin) return;
        event.preventDefault();
        document.documentElement.classList.add('lg-page-exit');
        window.setTimeout(() => { window.location.href = link.href; }, 220);
      });
    });
  };

  const init = () => {
    document.documentElement.classList.add('liquid-glass-ready');
    document.querySelectorAll(surfaceSelector).forEach(bindSurface);
    document.querySelectorAll(magneticSelectors).forEach(bindMagnetic);
    bindPageLinks();
    bindScrollParallax();
    initCursor();
    if (!reduceMotion && 'IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
        if (entry.isIntersecting) { entry.target.classList.add('lg-revealed'); observer.unobserve(entry.target); }
      }), { threshold: .08 });
      document.querySelectorAll('.lg-card-surface').forEach((element) => observer.observe(element));
    } else document.querySelectorAll('.lg-card-surface').forEach((element) => element.classList.add('lg-revealed'));
    window.setTimeout(() => document.documentElement.classList.add('lg-page-ready'), 20);
  };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true }); else init();
})();
