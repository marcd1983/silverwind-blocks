/**
 * Opens <dialog id="modal-…"> from any trigger with data-modal-target
 * (or data-remodal-target, the attribute older templates used), and from a
 * URL hash such as /page#modal-23. Closing is native: Escape, the close
 * button and the backdrop are all <form method="dialog">.
 */
function dialogFor(id) {
  const element = id ? document.getElementById(id) : null;
  return element instanceof HTMLDialogElement ? element : null;
}

function openFromHash() {
  const dialog = dialogFor(decodeURIComponent(window.location.hash.slice(1)));
  if (dialog && !dialog.open) dialog.showModal();
}

export function initModals() {
  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-modal-target], [data-remodal-target]');
    if (!trigger) return;
    const dialog = dialogFor(trigger.dataset.modalTarget || trigger.dataset.remodalTarget);
    if (!dialog) return;
    event.preventDefault();
    dialog.showModal();
  });

  window.addEventListener('hashchange', openFromHash);
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', openFromHash, { once: true });
  } else {
    openFromHash();
  }
}
