(() => {
  'use strict';
  function initPerfilEditor() {
    const input = document.getElementById('foto'), trigger = document.getElementById('avatarTrigger'), modal = document.getElementById('cropModal');
    const canvas = document.getElementById('cropCanvas'), stage = document.getElementById('cropStage'), preview = document.getElementById('cropPreview');
    const zoom = document.getElementById('cropZoom'), zoomValue = document.getElementById('cropZoomValue'), output = document.getElementById('fotoCortada'), form = document.getElementById('avatarForm');
    const closeButton = document.getElementById('cropClose'), cancelButton = document.getElementById('cropCancel'), applyButton = document.getElementById('cropApply');
    if (!input || !modal || !canvas || !stage || !preview || !zoom || !zoomValue || !output || !form) return;
    const ctx = canvas.getContext('2d', { alpha: false }); if (!ctx) return;
    ctx.imageSmoothingEnabled = true; ctx.imageSmoothingQuality = 'high';
    const image = new Image(); let loaded = false, drag = null, offsetX = 0, offsetY = 0;
    const size = () => canvas.width;
    const metrics = () => {
      const factor = Number(zoom.value) / 100, contain = size() / Math.max(image.naturalWidth, image.naturalHeight);
      return { w: image.naturalWidth * contain * factor, h: image.naturalHeight * contain * factor };
    };
    const clamp = () => {
      const { w, h } = metrics();
      const minX = Math.min(0, size() - w), maxX = Math.max(0, size() - w);
      const minY = Math.min(0, size() - h), maxY = Math.max(0, size() - h);
      offsetX = Math.min(maxX, Math.max(minX, offsetX)); offsetY = Math.min(maxY, Math.max(minY, offsetY));
      if (w <= size()) offsetX = (size() - w) / 2; if (h <= size()) offsetY = (size() - h) / 2;
    };
    const drawBackdrop = () => {
      const cover = size() / Math.min(image.naturalWidth, image.naturalHeight), w = image.naturalWidth * cover, h = image.naturalHeight * cover;
      ctx.save(); ctx.globalAlpha = .32; ctx.filter = 'blur(18px)'; ctx.drawImage(image, (size() - w) / 2, (size() - h) / 2, w, h); ctx.restore();
      ctx.fillStyle = 'rgba(2,11,20,.24)'; ctx.fillRect(0, 0, size(), size());
    };
    const draw = () => {
      if (!loaded) return;
      const { w, h } = metrics(); if (!drag) { offsetX = (size() - w) / 2; offsetY = (size() - h) / 2; } clamp();
      ctx.fillStyle = '#020b14'; ctx.fillRect(0, 0, size(), size()); drawBackdrop(); ctx.drawImage(image, offsetX, offsetY, w, h);
      zoomValue.textContent = `${zoom.value}%`; preview.src = canvas.toDataURL('image/jpeg', .95);
    };
    const close = (clear = true) => { modal.hidden = true; modal.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; drag = null; if (clear) input.value = ''; };
    const openImage = (file) => {
      if (!file || (!/^image\/(jpeg|png|webp)$/i.test(file.type) && !/\.(jpe?g|png|webp)$/i.test(file.name))) { alert('Escolha uma imagem JPG, PNG ou WEBP.'); input.value = ''; return; }
      if (file.size > 8 * 1024 * 1024) { alert('A imagem deve ter no máximo 8 MB.'); input.value = ''; return; }
      const reader = new FileReader(); reader.onerror = () => alert('Não foi possível ler esta imagem.');
      reader.onload = () => { image.onload = () => { loaded = true; zoom.value = '50'; drag = null; draw(); modal.hidden = false; modal.setAttribute('aria-hidden', 'false'); document.body.style.overflow = 'hidden'; (closeButton || applyButton)?.focus(); }; image.onerror = () => alert('O navegador não conseguiu abrir esta imagem.'); image.src = String(reader.result); };
      reader.readAsDataURL(file);
    };
    trigger?.addEventListener('click', e => { e.preventDefault(); input.click(); }); trigger?.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); } });
    input.addEventListener('change', () => openImage(input.files?.[0])); zoom.addEventListener('input', () => { drag = null; draw(); });
    stage.addEventListener('pointerdown', e => { if (!loaded) return; stage.setPointerCapture?.(e.pointerId); drag = { x: e.clientX, y: e.clientY, ox: offsetX, oy: offsetY }; });
    stage.addEventListener('pointermove', e => { if (!drag) return; offsetX = drag.ox + e.clientX - drag.x; offsetY = drag.oy + e.clientY - drag.y; clamp(); draw(); }); stage.addEventListener('pointerup', () => { drag = null; }); stage.addEventListener('pointercancel', () => { drag = null; });
    closeButton?.addEventListener('click', () => close()); cancelButton?.addEventListener('click', () => close()); modal.addEventListener('click', e => { if (e.target === modal) close(); });
    applyButton?.addEventListener('click', () => { if (!loaded) return; output.value = canvas.toDataURL('image/jpeg', .95); applyButton.disabled = true; applyButton.textContent = 'Salvando…'; form.submit(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.hidden) close(); }); window.__perfilEditorReady = true;
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initPerfilEditor, { once: true }); else initPerfilEditor();
})();
