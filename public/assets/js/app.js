/* État Civil Cotonou — UI */

/* ============================================================
   TOASTS
   ============================================================ */
const Toast = (() => {
  let region = null;

  function getRegion() {
    if (!region) {
      region = document.createElement('div');
      region.id = 'toast-region';
      document.body.appendChild(region);
    }
    return region;
  }

  const icons = {
    success: `<svg class="toast-icon" viewBox="0 0 18 18" fill="none" stroke="#007A47" stroke-width="1.8"><circle cx="9" cy="9" r="8"/><polyline points="5.5,9 8,11.5 12.5,6.5"/></svg>`,
    error:   `<svg class="toast-icon" viewBox="0 0 18 18" fill="none" stroke="#CC1925" stroke-width="1.8"><circle cx="9" cy="9" r="8"/><line x1="6" y1="6" x2="12" y2="12"/><line x1="12" y1="6" x2="6" y2="12"/></svg>`,
    warning: `<svg class="toast-icon" viewBox="0 0 18 18" fill="none" stroke="#C8920A" stroke-width="1.8"><path d="M9 2L16 15H2L9 2z"/><line x1="9" y1="8" x2="9" y2="11"/><circle cx="9" cy="13.5" r="0.8" fill="#C8920A"/></svg>`,
    info:    `<svg class="toast-icon" viewBox="0 0 18 18" fill="none" stroke="#007A47" stroke-width="1.8"><circle cx="9" cy="9" r="8"/><line x1="9" y1="8" x2="9" y2="13"/><circle cx="9" cy="5.5" r="0.8" fill="#007A47"/></svg>`,
  };

  function show(message, type = 'info', duration = 4500) {
    const el = document.createElement('div');
    el.className = `toast toast--${type}`;
    el.innerHTML = `
      ${icons[type] || icons.info}
      <div class="toast-body"><div class="toast-title">${message}</div></div>
      <button class="toast-close" aria-label="Fermer">&times;</button>
    `;
    el.querySelector('.toast-close').addEventListener('click', () => dismiss(el));
    getRegion().appendChild(el);
    const timer = setTimeout(() => dismiss(el), duration);
    el._timer = timer;
    return el;
  }

  function dismiss(el) {
    clearTimeout(el._timer);
    el.classList.add('toast--out');
    el.addEventListener('transitionend', () => el.remove(), { once: true });
  }

  return {
    show,
    success: (m, d) => show(m, 'success', d),
    error:   (m, d) => show(m, 'error', d),
    warning: (m, d) => show(m, 'warning', d),
    info:    (m, d) => show(m, 'info', d),
  };
})();

/* ============================================================
   MODAL
   ============================================================ */
const Modal = (() => {
  const variantIcons = {
    danger:  `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="#CC1925" stroke-width="1.6"><path d="M10 3L17 16H3L10 3z"/><line x1="10" y1="9" x2="10" y2="12"/><circle cx="10" cy="14.5" r="0.8" fill="#CC1925"/></svg>`,
    warning: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="#C8920A" stroke-width="1.6"><path d="M10 3L17 16H3L10 3z"/><line x1="10" y1="9" x2="10" y2="12"/><circle cx="10" cy="14.5" r="0.8" fill="#C8920A"/></svg>`,
    info:    `<svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="#007A47" stroke-width="1.6"><circle cx="10" cy="10" r="8"/><line x1="10" y1="9" x2="10" y2="14"/><circle cx="10" cy="6.5" r="0.8" fill="#007A47"/></svg>`,
  };

  function confirm({ title, body = '', confirmLabel = 'Confirmer', cancelLabel = 'Annuler', variant = 'danger' }) {
    return new Promise((resolve) => {
      const confirmBtnClass = variant === 'danger' ? 'btn btn-danger' : 'btn btn-primary';

      const backdrop = document.createElement('div');
      backdrop.className = 'modal-backdrop';
      backdrop.innerHTML = `
        <div class="modal" role="dialog" aria-modal="true">
          <div class="modal-icon modal-icon--${variant}">${variantIcons[variant] || variantIcons.danger}</div>
          <div class="modal-title">${title}</div>
          ${body ? `<div class="modal-body">${body}</div>` : ''}
          <div class="modal-actions">
            <button class="btn btn-ghost" data-action="cancel">${cancelLabel}</button>
            <button class="${confirmBtnClass}" data-action="confirm">${confirmLabel}</button>
          </div>
        </div>
      `;

      function close(result) {
        backdrop.remove();
        document.removeEventListener('keydown', onKey);
        resolve(result);
      }

      function onKey(e) { if (e.key === 'Escape') close(false); }

      backdrop.addEventListener('click', (e) => { if (e.target === backdrop) close(false); });
      backdrop.querySelector('[data-action="cancel"]').addEventListener('click', () => close(false));
      backdrop.querySelector('[data-action="confirm"]').addEventListener('click', () => close(true));
      document.addEventListener('keydown', onKey);
      document.body.appendChild(backdrop);
      backdrop.querySelector('[data-action="confirm"]').focus();
    });
  }

  return { confirm };
})();

/* ============================================================
   INIT
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {

  // Flash messages PHP → toast (lus depuis data-flash sur un élément caché)
  document.querySelectorAll('[data-flash]').forEach(el => {
    Toast.show(el.dataset.flashMessage, el.dataset.flash);
    el.remove();
  });

  // Formulaires avec confirmation modale
  // Usage : <form data-confirm="Titre" data-confirm-body="..." data-confirm-variant="danger">
  document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const ok = await Modal.confirm({
        title:        form.dataset.confirm,
        body:         form.dataset.confirmBody    || '',
        confirmLabel: form.dataset.confirmLabel   || 'Confirmer',
        cancelLabel:  form.dataset.cancelLabel    || 'Annuler',
        variant:      form.dataset.confirmVariant || 'danger',
      });
      if (ok) form.submit();
    });
  });

  // Liens avec confirmation modale
  document.querySelectorAll('a[data-confirm]').forEach(link => {
    link.addEventListener('click', async (e) => {
      e.preventDefault();
      const ok = await Modal.confirm({
        title:   link.dataset.confirm,
        body:    link.dataset.confirmBody    || '',
        variant: link.dataset.confirmVariant || 'danger',
      });
      if (ok) window.location.href = link.href;
    });
  });

  // Password toggle (eye icon)
  const eyeOn  = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
  const eyeOff = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;

  document.querySelectorAll('input[type="password"]').forEach(input => {
    const wrapper = document.createElement('div');
    wrapper.className = 'password-wrapper';
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'password-toggle';
    btn.setAttribute('aria-label', 'Afficher le mot de passe');
    btn.innerHTML = eyeOff;
    wrapper.appendChild(btn);

    btn.addEventListener('click', () => {
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.innerHTML = show ? eyeOn : eyeOff;
      btn.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
    });
  });

  // Autocomplete suggestions sur les champs nom de la filter-bar
  document.querySelectorAll('.filter-bar input[name="nom"]').forEach(input => {
    // Déduire le type depuis l'URL courante
    const path = window.location.pathname;
    const type = path.startsWith('/naissances') ? 'naissance'
               : path.startsWith('/mariages')   ? 'mariage'
               : path.startsWith('/deces')       ? 'deces'
               : null;
    if (!type) return;

    // Créer le dropdown
    const dropdown = document.createElement('ul');
    dropdown.className = 'autocomplete-list';
    input.parentNode.style.position = 'relative';
    input.parentNode.appendChild(dropdown);

    let debounceTimer = null;

    function hideSuggestions() {
      dropdown.innerHTML = '';
      dropdown.hidden = true;
    }

    function selectSuggestion(value) {
      input.value = value;
      hideSuggestions();
      // Soumettre le formulaire parent
      input.closest('form')?.submit();
    }

    input.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      const q = input.value.trim();
      if (q.length < 2) { hideSuggestions(); return; }

      debounceTimer = setTimeout(async () => {
        try {
          const res = await fetch(`/api/suggestions?type=${type}&q=${encodeURIComponent(q)}`);
          if (!res.ok) return;
          const suggestions = await res.json();
          dropdown.innerHTML = '';
          if (!suggestions.length) { dropdown.hidden = true; return; }

          suggestions.forEach(label => {
            const li = document.createElement('li');
            li.className = 'autocomplete-item';
            li.textContent = label;
            li.addEventListener('mousedown', (e) => {
              e.preventDefault();
              selectSuggestion(label);
            });
            dropdown.appendChild(li);
          });
          dropdown.hidden = false;
        } catch (_) { /* silencieux */ }
      }, 220);
    });

    input.addEventListener('keydown', (e) => {
      const items = [...dropdown.querySelectorAll('.autocomplete-item')];
      const active = dropdown.querySelector('.autocomplete-item--active');
      const idx = items.indexOf(active);

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        active?.classList.remove('autocomplete-item--active');
        const next = items[idx + 1] || items[0];
        next?.classList.add('autocomplete-item--active');
        input.value = next?.textContent || input.value;
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        active?.classList.remove('autocomplete-item--active');
        const prev = items[idx - 1] || items[items.length - 1];
        prev?.classList.add('autocomplete-item--active');
        input.value = prev?.textContent || input.value;
      } else if (e.key === 'Escape') {
        hideSuggestions();
      } else if (e.key === 'Enter' && active) {
        e.preventDefault();
        selectSuggestion(active.textContent);
      }
    });

    document.addEventListener('click', (e) => {
      if (!input.parentNode.contains(e.target)) hideSuggestions();
    });
  });

  // Uppercase inputs
  document.querySelectorAll('input[style*="text-transform:uppercase"]').forEach(input => {
    input.addEventListener('input', () => {
      const pos = input.selectionStart;
      input.value = input.value.toUpperCase();
      input.setSelectionRange(pos, pos);
    });
  });
});
