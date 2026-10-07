(() => {
  'use strict';

  // Versão 67.67 — Tema Liquid Glass inspirado em interfaces translúcidas, com fallback acessível.

  // Versão incremental 15/15 — Consolida a versão avançada, com comentários de manutenção, validação de entrada e documentação do ciclo.

  if (window.__A11yUniversalWidget) return;
  window.__A11yUniversalWidget = true;

  const VERSION = '15.0.0';
  const BUILD_VERSION = '67.67.0';
  const CONFIG = {
    storageKey: 'a11y-universal-preferences-v1',
    hostId: 'a11y-universal-widget-host',
    filterSvgId: 'a11y-universal-svg-filters',
    minScale: 0.85,
    maxScale: 2,
    step: 0.05,
    defaultScale: 1,
    zIndex: 999999
  };

  const DEFAULTS = { fontScale: CONFIG.defaultScale, colorMode: 'none', open: false, highContrast: false, lineHeight: 1 };
  const LINE_HEIGHTS = [1, 1.25, 1.5, 1.75];
  const LETTER_SPACING = [0, 0.03, 0.06];
  const originalBodyClass = 'a11y-high-contrast';
  const A11Y_IGNORE = '[data-a11y-ignore], [aria-hidden="true"]';
  const FONT_SELECTOR = 'p, span, a, li, h1, h2, h3, h4, h5, h6, label, button, input, textarea, select, blockquote, figcaption, dt, dd';
  const FILTERS = {
    protanopia: [0.567, 0.433, 0, 0, 0, 0.558, 0.442, 0, 0, 0, 0, 0.242, 0.758, 0, 0, 0, 0, 0, 1, 0],
    deuteranopia: [0.625, 0.375, 0, 0, 0, 0.7, 0.3, 0, 0, 0, 0, 0.3, 0.7, 0, 0, 0, 0, 0, 1, 0],
    tritanopia: [0.95, 0.05, 0, 0, 0, 0, 0.433, 0.567, 0, 0, 0, 0.475, 0.525, 0, 0, 0, 0, 0, 1, 0],
    achromatopsia: [0.2126, 0.7152, 0.0722, 0, 0, 0.2126, 0.7152, 0.0722, 0, 0, 0.2126, 0.7152, 0.0722, 0, 0, 0, 0, 0, 1, 0]
  };

  let memoryState = {};
  const state = loadState();
  let host;
  let shadow;
  let panel;
  const colorFilterTargets = new Map();

  function loadState() {
    try {
      const saved = JSON.parse(localStorage.getItem(CONFIG.storageKey) || '{}');
      return {
        ...DEFAULTS,
        ...saved,
        fontScale: clamp(Number(saved.fontScale) || DEFAULTS.fontScale, CONFIG.minScale, CONFIG.maxScale),
        colorMode: Object.prototype.hasOwnProperty.call(FILTERS, saved.colorMode) ? saved.colorMode : 'none'
      };
    } catch (_) {
      return { ...DEFAULTS, ...memoryState };
    }
  }

  function exportPreferences() {
    const payload = JSON.stringify({ fontScale: state.fontScale, colorMode: state.colorMode }, null, 2);
    if (navigator.clipboard) navigator.clipboard.writeText(payload).catch(() => {});
  }

  function saveState() {
    try {
      localStorage.setItem(CONFIG.storageKey, JSON.stringify({
        fontScale: state.fontScale,
        colorMode: state.colorMode
      }));
    } catch (_) { /* localStorage pode estar bloqueado; o widget continua funcional. */ }
  }

  function clamp(value, min, max) { return Math.min(max, Math.max(min, Number.isFinite(value) ? value : min)); }
  function escapeHtml(value) {
    return String(value).replace(/[&<>'"]/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[char]));
  }

  function injectColorFilters() {
    if (document.getElementById(CONFIG.filterSvgId)) return;
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.id = CONFIG.filterSvgId;
    svg.setAttribute('aria-hidden', 'true');
    svg.setAttribute('width', '0');
    svg.setAttribute('height', '0');
    svg.style.cssText = 'position:absolute;width:0;height:0;overflow:hidden;pointer-events:none';

    const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
    Object.entries(FILTERS).forEach(([name, values]) => {
      const filter = document.createElementNS('http://www.w3.org/2000/svg', 'filter');
      filter.id = `a11y-filter-${name}`;
      const matrix = document.createElementNS('http://www.w3.org/2000/svg', 'feColorMatrix');
      matrix.setAttribute('type', 'matrix');
      matrix.setAttribute('values', values.join(' '));
      filter.appendChild(matrix);
      defs.appendChild(filter);
    });
    svg.appendChild(defs);
    (document.body || document.documentElement).appendChild(svg);
  }

  function applyHighContrast() {
    if (document.body) document.body.classList.toggle(originalBodyClass, Boolean(state.highContrast));
  }

  function applyColorMode() {
    if (!document.body) return;
    colorFilterTargets.forEach((previousFilter, element) => {
      element.style.filter = previousFilter;
    });
    colorFilterTargets.clear();

    if (state.colorMode !== 'none') {
      const filterValue = `url(#a11y-filter-${state.colorMode})`;
      Array.from(document.body.children).forEach(element => {
        if (element === host || element.id === CONFIG.filterSvgId) return;
        colorFilterTargets.set(element, element.style.filter);
        element.style.filter = filterValue;
      });
    }
    shadow.querySelectorAll('[data-color-mode]').forEach(button => {
      const selected = button.dataset.colorMode === state.colorMode;
      button.setAttribute('aria-pressed', String(selected));
      button.classList.toggle('selected', selected);
    });
  }

  function applyLetterSpacing() {
    document.querySelectorAll(FONT_SELECTOR).forEach(element => {
      if (element.matches(A11Y_IGNORE) || element.closest(A11Y_IGNORE)) return; if (!host || !host.contains(element)) element.style.setProperty('letter-spacing', `${state.letterSpacing || 0}em`, 'important'); });
  }

  function applyLineHeight() {
    document.querySelectorAll(FONT_SELECTOR).forEach(element => { if (!host || !host.contains(element)) element.style.setProperty('line-height', String(state.lineHeight), 'important'); });
  }

  function clearManagedStyles() { document.querySelectorAll('[data-a11y-original-font-size]').forEach(element => element.style.removeProperty('font-size')); }

  function applyFontScale() {
    if (!document.body) return;
    document.querySelectorAll(FONT_SELECTOR).forEach(element => {
      if (host && (element === host || host.contains(element))) return;
      if (!element.hasAttribute('data-a11y-original-font-size')) {
        const computed = window.getComputedStyle(element).fontSize;
        if (computed && computed !== '0px') element.setAttribute('data-a11y-original-font-size', computed);
      }
      const original = parseFloat(element.getAttribute('data-a11y-original-font-size'));
      if (Number.isFinite(original)) {
        element.style.setProperty('font-size', `${original * state.fontScale}px`, 'important');
      }
    });
    const value = `${Math.round(state.fontScale * 100)}%`;
    const output = shadow.querySelector('[data-font-value]');
    if (output) output.textContent = value;
  }

  function resetFontStyles() {
    document.querySelectorAll('[data-a11y-original-font-size]').forEach(element => {
      element.style.removeProperty('font-size');
      element.removeAttribute('data-a11y-original-font-size');
    });
  }

  function resetPreferences() {
    state.fontScale = CONFIG.defaultScale;
    state.colorMode = 'none';
    resetFontStyles();
    applyFontScale();
    applyLineHeight();
    applyLetterSpacing();
    applyColorMode();
    applyHighContrast();
    saveState();
  }

  function button(label, attrs = '', content = label) {
    return `<button type="button" class="a11y-button" ${attrs} aria-label="${escapeHtml(label)}">${content}</button>`;
  }

  function createWidget() {
    host = document.createElement('div');
    host.id = CONFIG.hostId;
    host.setAttribute('data-a11y-widget', 'true');
    host.style.cssText = `all:initial;position:fixed;right:20px;bottom:20px;z-index:${CONFIG.zIndex};font-family:Arial,sans-serif`;
    shadow = host.attachShadow({ mode: 'open' });
    shadow.innerHTML = `
      <style>
        :host { all: initial; }
        *, *::before, *::after { box-sizing: border-box; }
        :host { --glass-text: #10243e; --glass-muted: #38516d; --glass-blue: #0759b8; --glass-border: rgba(255,255,255,.72); }
        .a11y-wrap { position: relative; color: var(--glass-text); font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Segoe UI", Arial, sans-serif; }
        .a11y-fab { width: 58px; height: 58px; border: 1px solid rgba(255,255,255,.8); border-radius: 20px; cursor: pointer; display: grid; place-items: center; color: #fff; background: linear-gradient(145deg, rgba(23,122,232,.92), rgba(4,63,143,.92)); box-shadow: 0 12px 26px rgba(13,54,105,.28), inset 0 1px 1px rgba(255,255,255,.7); transition: transform .22s ease, box-shadow .22s ease; }
        .a11y-fab:hover, .a11y-fab:focus-visible { transform: translateY(-2px) scale(1.03); box-shadow: 0 16px 32px rgba(13,54,105,.36), inset 0 1px 1px rgba(255,255,255,.85); outline: 3px solid #ffbf47; outline-offset: 3px; }
        .a11y-fab svg { width: 30px; height: 30px; fill: currentColor; filter: drop-shadow(0 1px 1px rgba(0,0,0,.2)); }
        .a11y-panel { position: absolute; right: 0; bottom: 74px; width: min(372px, calc(100vw - 28px)); max-height: min(720px, calc(100vh - 104px)); overflow: auto; padding: 19px; border: 1px solid var(--glass-border); border-radius: 24px; background: linear-gradient(135deg, rgba(255,255,255,.78), rgba(235,245,255,.56)); box-shadow: 0 24px 60px rgba(18,54,92,.26), inset 0 1px 0 rgba(255,255,255,.95), inset 0 -1px 0 rgba(255,255,255,.3); opacity: 0; visibility: hidden; transform: translateY(12px) scale(.97); transition: opacity .22s ease, transform .22s ease, visibility .22s; }
        .a11y-panel::before { content: ""; position: absolute; inset: 1px; pointer-events: none; border-radius: 23px; background: linear-gradient(120deg, rgba(255,255,255,.5), transparent 30%, transparent 70%, rgba(255,255,255,.22)); }
        .a11y-panel.open { opacity: 1; visibility: visible; transform: translateY(0) scale(1); }
        .a11y-header, .a11y-section, .a11y-reset, .a11y-panel > * { position: relative; z-index: 1; }
        .a11y-header { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom: 14px; }
        .a11y-title { margin: 0; font-size: 20px; letter-spacing: -.02em; font-weight: 750; color: var(--glass-text); }
        .a11y-close { border: 1px solid rgba(70,103,137,.25); width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,.48); font-size: 23px; line-height: 1; cursor: pointer; color: var(--glass-muted); }
        .a11y-section { border-top: 1px solid rgba(67,104,142,.18); padding-top: 14px; margin-top: 14px; }
        .a11y-section h3 { margin: 0 0 10px; font-size: 13px; letter-spacing: .02em; color: var(--glass-muted); }
        .a11y-row { display: flex; gap: 8px; flex-wrap: wrap; }
        .a11y-button { border: 1px solid rgba(75,111,148,.3); border-radius: 12px; padding: 9px 10px; cursor: pointer; background: rgba(255,255,255,.54); color: var(--glass-text); font: inherit; font-size: 13px; box-shadow: inset 0 1px 0 rgba(255,255,255,.72); transition: background .18s, transform .18s, box-shadow .18s; }
        .a11y-button:hover, .a11y-button:focus-visible { transform: translateY(-1px); background: rgba(255,255,255,.84); box-shadow: 0 5px 12px rgba(44,82,120,.14), inset 0 1px 0 #fff; outline: 3px solid #ffbf47; outline-offset: 2px; }
        .a11y-button.selected { background: linear-gradient(145deg, rgba(20,112,215,.95), rgba(5,76,163,.95)); color: #fff; border-color: rgba(255,255,255,.72); box-shadow: 0 6px 14px rgba(8,69,144,.25), inset 0 1px 0 rgba(255,255,255,.5); }
        .a11y-high-contrast { background-color: #000 !important; color: #fff !important; }
        .a11y-high-contrast a { color: #ffff00 !important; }
        .a11y-status { min-width: 52px; align-self: center; text-align:center; font-weight: 700; color: var(--glass-blue); }
        .a11y-high-contrast a { text-decoration: underline !important; }
        .a11y-reset { width: 100%; margin-top: 16px; background: rgba(255,244,229,.78); border-color: rgba(180,111,29,.5); color: #704100; }
        .a11y-high-contrast .a11y-panel { border: 2px solid #fff; background: #000; color: #fff; }
        @supports not (backdrop-filter: blur(20px)) { .a11y-panel { background: #f5f9ff; } .a11y-button, .a11y-close { background: #fff; } }
        @supports (backdrop-filter: blur(20px)) { .a11y-panel { -webkit-backdrop-filter: blur(24px) saturate(150%); backdrop-filter: blur(24px) saturate(150%); } }
        @media (prefers-contrast: more) { .a11y-button { border-width: 2px; } .a11y-panel { background: #fff; } }
        @media (prefers-reduced-motion: reduce) { .a11y-panel, .a11y-fab, .a11y-button { transition: none; } }
      </style>
      <div class="a11y-wrap" data-a11y-version="67.67">
        <div class="a11y-live" data-a11y-live aria-live="polite" role="status"></div>
        <section class="a11y-panel" data-panel role="dialog" aria-labelledby="a11y-title" aria-modal="false">
          <div class="a11y-header"><h2 class="a11y-title" id="a11y-title">Acessibilidade</h2>${button('Fechar painel', 'data-close', '×')}</div>
          <div class="a11y-section"><h3>Tipografia</h3><div class="a11y-row">
            ${button('Diminuir fonte', 'data-font="down"', '−')}
            ${button('Tamanho padrão', 'data-font="reset"', 'Padrão')}
            ${button('Aumentar fonte', 'data-font="up"', '+')}
            <span class="a11y-status" data-font-value aria-live="polite">100%</span>
          </div></div>
          <div class="a11y-section"><h3>Filtros de cor</h3><div class="a11y-row">
            ${button('Cores normais', 'data-color-mode="none"', 'Normal')}
            ${button('Simular protanopia', 'data-color-mode="protanopia"', 'Protanopia')}
            ${button('Simular deuteranopia', 'data-color-mode="deuteranopia"', 'Deuteranopia')}
            ${button('Simular tritanopia', 'data-color-mode="tritanopia"', 'Tritanopia')}
            ${button('Simular acromatopsia', 'data-color-mode="achromatopsia"', 'Escala de cinza')}
          </div></div>
          ${button('Resetar preferências', 'data-reset class="a11y-button a11y-reset"', 'Resetar preferências')}
        </section>
        <button class="a11y-fab" type="button" data-toggle aria-expanded="false" aria-controls="a11y-title" aria-label="Abrir painel de acessibilidade">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="4" r="2.2"/><path d="M20 7.5a1 1 0 0 0-1.3-.6A17 17 0 0 1 12 8.2a17 17 0 0 1-6.7-1.3A1 1 0 0 0 4.6 8.7c1.8.8 3.7 1.3 5.7 1.5v2.1l-3.8 7.1a1 1 0 0 0 1.8 1l3.1-5.8a.7.7 0 0 1 1.2 0l3.1 5.8a1 1 0 0 0 1.8-1l-3.8-7.1v-2.1c2-.2 3.9-.7 5.7-1.5A1 1 0 0 0 20 7.5Z"/></svg>
        </button>
      </div>`;
    panel = shadow.querySelector('[data-panel]');

    shadow.addEventListener('click', event => {
      const target = event.target.closest('button');
      if (!target) return;
      if (target.hasAttribute('data-toggle')) togglePanel();
      if (target.hasAttribute('data-close')) togglePanel(false);
      if (target.hasAttribute('data-reset')) resetPreferences();
      if (target.dataset.font) {
        if (target.dataset.font === 'down') state.fontScale = clamp(state.fontScale - CONFIG.step, CONFIG.minScale, CONFIG.maxScale);
        if (target.dataset.font === 'up') state.fontScale = clamp(state.fontScale + CONFIG.step, CONFIG.minScale, CONFIG.maxScale);
        if (target.dataset.font === 'reset') { state.fontScale = CONFIG.defaultScale; resetFontStyles(); }
        applyFontScale(); saveState();
      }
      if (target.dataset.colorMode) { state.colorMode = target.dataset.colorMode; applyColorMode(); saveState(); }
    });
    document.addEventListener('click', event => {
      if (state.open && !host.contains(event.target)) togglePanel(false);
    });
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && state.open) togglePanel(false);
    });
    document.body.appendChild(host);
  }

  function announce(message) {
    const live = shadow && shadow.querySelector('[data-a11y-live]');
    if (live) live.textContent = message;
  }

  function togglePanel(force) {
    state.open = typeof force === 'boolean' ? force : !state.open;
    panel.classList.toggle('open', state.open);
    const toggle = shadow.querySelector('[data-toggle]');
    toggle.setAttribute('aria-expanded', String(state.open));
    if (state.open) shadow.querySelector('[data-close]').focus();
  }

  let observer;

  function observeDynamicContent() {
    if (observer || !document.body || !window.MutationObserver) return;
    observer = new MutationObserver(() => {
      applyFontScale();
      if (state.colorMode !== 'none') applyColorMode();
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  function init() {
    if (!document.body || document.getElementById(CONFIG.hostId)) return;
    injectColorFilters();
    createWidget();
    applyFontScale();
    applyLineHeight();
    applyLetterSpacing();
    applyColorMode();
    observeDynamicContent();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();
