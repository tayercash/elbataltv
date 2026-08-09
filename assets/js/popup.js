class PopupManager {
  static init(root) {
    root = root || document;
    root.querySelectorAll('[data-openpopup]').forEach(el => {
      if (el._popupBound) return;
      el._popupBound = true;
      el.addEventListener('click', e => {
        e.preventDefault();
        const id = el.getAttribute('data-openpopup');
        PopupManager.openPopup(id);
      });
    });
    root.querySelectorAll('[data-closepopup]').forEach(el => {
      if (el._closeBound) return;
      el._closeBound = true;
      el.addEventListener('click', e => {
        e.preventDefault();
        const id = el.getAttribute('data-closepopup');
        PopupManager.closePopup(id);
      });
    });
  }

  static openPopup(id) {
    const popup = document.getElementById(id);
    if (!popup) return;
    popup.classList.add('show');
    document.body.classList.add('no-scroll');
  }

  static closePopup(id) {
    const popup = document.getElementById(id);
    if (!popup) return;
    popup.classList.remove('show');
    document.body.classList.remove('no-scroll');
  }
}

window.confirmPopup = function(options) {
  options = options || {};
  return new Promise(function(resolve) {
    var popup = document.getElementById('confirm_action_popup');
    if (!popup) {
      resolve(false);
      return;
    }
    var title = popup.querySelector('[data-confirm-title]');
    var message = popup.querySelector('[data-confirm-message]');
    var confirmBtn = popup.querySelector('[data-confirm-yes]');
    var cancelBtn = popup.querySelector('[data-confirm-no]');
    if (title) title.textContent = options.title || (window.t ? t('common.confirm') : 'Confirm');
    if (message) message.textContent = options.message || '';
    if (confirmBtn) confirmBtn.textContent = options.confirmText || (window.t ? t('common.confirm') : 'Confirm');
    if (cancelBtn) cancelBtn.textContent = options.cancelText || (window.t ? t('common.cancel') : 'Cancel');

    function finish(result) {
      if (confirmBtn) confirmBtn.removeEventListener('click', onConfirm);
      if (cancelBtn) cancelBtn.removeEventListener('click', onCancel);
      PopupManager.closePopup('confirm_action_popup');
      resolve(result);
    }
    function onConfirm(e) { e.preventDefault(); finish(true); }
    function onCancel(e) { e.preventDefault(); finish(false); }

    if (confirmBtn) confirmBtn.addEventListener('click', onConfirm);
    if (cancelBtn) cancelBtn.addEventListener('click', onCancel);
    PopupManager.openPopup('confirm_action_popup');
  });
};

document.addEventListener('click', e => {
  const popup = e.target.closest('.mou_popup.show');
  if (popup) {
    if (popup.hasAttribute('data-require-choice')) return;
    const backdrop = e.target.closest('.mou_popup_backdrop');
    if (backdrop || e.target === popup) {
      popup.classList.remove('show');
      document.body.classList.remove('no-scroll');
    }
  }
});

$.fn.openpopup = function () { PopupManager.openPopup(this.attr('id')); return this; };
$.fn.closepopup = function () { PopupManager.closePopup(this.attr('id')); return this; };
