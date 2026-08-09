$(document).ready(function () {
  const savedLang = localStorage.getItem('lang') || 'ar';
  document.documentElement.dir = savedLang === 'ar' ? 'rtl' : 'ltr';
  document.documentElement.lang = savedLang;
});

// Sidebar controller
function closeSidebar() {
  clearTimeout(window._sidebarOverlayTimer);
  $('#app-sidebar').removeClass('open');
  $('#sidebar-overlay').remove();
}

document.addEventListener('DOMContentLoaded', () => {
  // 1. OPEN sidebar
  $(document).off('click', '#sidebar-toggle-btn').on('click', '#sidebar-toggle-btn', function (e) {
    e.preventDefault();
    const $side = $('#app-sidebar');
    $side.find('.sidebar-content, .sidebar-backdrop').attr('style', '');
    $side.addClass('open');
    if (!$('#sidebar-overlay').length) {
      $('<div id="sidebar-overlay"></div>').appendTo('body');
    }
  });

  // 2. CLOSE sidebar
  $(document).off('click', '#sidebar-close-btn').on('click', '#sidebar-close-btn', function (e) {
    e.preventDefault();
    closeSidebar();
  });

  // 3. CLOSE via overlay
  $(document).off('click touchstart', '#sidebar-overlay').on('click touchstart', '#sidebar-overlay', function (e) {
    e.stopPropagation();
    e.preventDefault();
    $('#app-sidebar').removeClass('open');
    clearTimeout(window._sidebarOverlayTimer);
    window._sidebarOverlayTimer = setTimeout(function () {
      $('#sidebar-overlay').remove();
    }, 100);
  });

  // 4. Swipe to close
  (function () {
    const $sidebar = $('#app-sidebar');
    const $content = $sidebar.find('.sidebar-content');
    const $backdrop = $sidebar.find('.sidebar-backdrop');
    let startX, startY, deltaX = 0;
    let isDragging = false, isScrolling = false;

    $content.on('touchstart', function (e) {
      if (!$sidebar.hasClass('open')) return;
      const t = e.originalEvent.touches[0];
      startX = t.clientX; startY = t.clientY;
      deltaX = 0; isDragging = true; isScrolling = false;
      $content.css('transition', 'none');
      $content.css('touch-action', 'none');
    });

    $content.on('touchmove', function (e) {
      if (!isDragging) return;
      const t = e.originalEvent.touches[0];
      const dx = t.clientX - startX;
      const dy = t.clientY - startY;

      if (!isScrolling && Math.abs(dy) > 10 && Math.abs(dy) > Math.abs(dx)) {
        isScrolling = true;
        $content.css('transition', '').css('touch-action', '').css('transform', '');
        $backdrop.css('opacity', '');
        return;
      }
      if (isScrolling) return;
      e.preventDefault();

      const isRTL = ($('html').attr('dir') || 'rtl') === 'rtl';
      const maxDrag = isRTL ? 280 : startX;
      let dragValue = isRTL ? Math.max(0, Math.min(dx, maxDrag)) : Math.max(-maxDrag, Math.min(0, dx));
      deltaX = dragValue;
      $content.css('transform', `translateX(${dragValue}px)`);
      $backdrop.css('opacity', 1 - Math.abs(dragValue) / maxDrag);
    });

    $(document).on('touchend touchcancel', function () {
      if (!isDragging || isScrolling) { isDragging = false; return; }
      isDragging = false;
      $content.css('transition', '').css('touch-action', '');

      const isRTL = ($('html').attr('dir') || 'rtl') === 'rtl';
      const threshold = isRTL ? 80 : Math.min(80, Math.max(30, startX * 0.4));
      const shouldClose = isRTL ? deltaX > threshold : Math.abs(deltaX) > threshold;

      if (shouldClose) {
        const targetX = isRTL ? '280px' : '-280px';
        const anim = $content[0].animate([
          { transform: $content[0].style.transform },
          { transform: `translateX(${targetX})` }
        ], {
          duration: 200,
          easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
          fill: 'forwards'
        });
        anim.onfinish = function () {
          $content.css('transform', '');
          $backdrop.css('opacity', '');
          closeSidebar();
          anim.cancel();
        };
      } else {
        const currentTransform = $content[0].style.transform;
        const anim = $content[0].animate([
          { transform: currentTransform },
          { transform: 'translateX(0)' }
        ], {
          duration: 250,
          easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
          fill: 'forwards'
        });
        anim.onfinish = function () {
          $content.css('transform', '');
          $backdrop.css('opacity', '');
          anim.cancel();
        };
      }
    });
})();

  // Dropdown toggle
  $(document).off('click', '.sidebar-dropdown-wrapper .dropdown-toggle').on('click', '.sidebar-dropdown-wrapper .dropdown-toggle', function (e) {
    e.preventDefault();
    const $wrapper = $(this).closest('.sidebar-dropdown-wrapper');
    const $submenu = $wrapper.find('.sidebar-submenu');
    if ($submenu.is(':animated')) return;
    $submenu.stop(true, true).slideToggle(250, function () {
      $wrapper.toggleClass('open', $(this).is(':visible'));
    });
  });

  // Sync active menu
  $(document).on('page_shown', function (e, url) {
    $(`.sidebar-menu .menu-item`).removeClass('active');
    $(`.sidebar-menu .menu-item[data-target="${url}"]`).addClass('active');

    $('.sidebar-dropdown-wrapper').each(function () {
      const $wrapper = $(this);
      const $submenu = $wrapper.find('.sidebar-submenu');
      const hasActive = $wrapper.find('.submenu-item.active').length > 0;
      $submenu.stop(true, true);
      if (hasActive) {
        $wrapper.addClass('open');
        $submenu.slideDown(200);
      } else {
        $wrapper.removeClass('open');
        $submenu.slideUp(200);
      }
    });
  });
});

// Prevent wheel on number inputs
$(document).on('wheel', 'input[type="number"]', function () { $(this).blur(); return false; });
