var navigationCache = {};
var isTransitioning = false;

function toLocalTime(utcStr) {
  if (!utcStr) return '-';
  try {
    var d = new Date(utcStr.replace(' ', 'T') + (utcStr.indexOf('Z') === -1 && utcStr.indexOf('+') === -1 ? 'Z' : ''));
    if (isNaN(d.getTime())) return utcStr;
    return d.toLocaleString();
  } catch(e) { return utcStr; }
}

function shortUrl(full) {
  return full.replace(/^assets\/pages\//, '').replace(/\.php$/, '');
}
function fullUrl(short) {
  return short.indexOf('/') === -1 && short.indexOf('.php') === -1 ? 'assets/pages/' + short + '.php' : short;
}
function getPageFromURL() {
  var m = window.location.search.match(/[?&]page=([^&]+)/);
  if (m) return fullUrl(decodeURIComponent(m[1]));
  var parts = window.location.pathname.replace(/\/+$/, '').split('/').filter(Boolean);
  if (parts.length < 2) return null;
  var p = parts[parts.length - 1];
  if (p === 'index.php' || /\.\w+$/.test(p)) return null;
  return fullUrl(p);
}
function updateURL(page) {
  if (!window.history.pushState) return;
  var dir = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
  var s = page ? shortUrl(page) : '';
  var newPath = s ? dir + s : dir;
  if (window.location.pathname !== newPath) window.history.pushState({page: page}, '', newPath);
}

if (!window.navigationJsInitialized) {
  window.navigationJsInitialized = true;

  window.isBackgroundRequest = function(url) {
    var rawUrl = typeof url === 'string' ? url : (url && url.url ? url.url : '');
    return /[?&]action=(heartbeat|get_online_users)\b/.test(rawUrl);
  };

  (function() {
    if (window.fetchPatched) return;
    window.fetchPatched = true;
    const originalFetch = window.fetch;
    window.activeFetchCount = 0;

    window.fetch = function(...args) {
      if (window.isBackgroundRequest(args[0])) {
        return originalFetch.apply(this, args);
      }
      window.activeFetchCount++;
      $(document).trigger('fetchStart');
      return originalFetch.apply(this, args).then(
        function(response) {
          window.activeFetchCount--;
          $(document).trigger('fetchEnd');
          return response;
        },
        function(error) {
          window.activeFetchCount--;
          $(document).trigger('fetchEnd');
          throw error;
        }
      );
    };
  })();

  $.ajaxPrefilter(function(options) {
    if (window.isBackgroundRequest(options.url)) {
      options.global = false;
    }
  });

  window.isNetworkIdle = function() {
    const activeJQuery = typeof $ !== 'undefined' && $.active ? $.active : 0;
    const activeFetch = window.activeFetchCount || 0;
    return activeJQuery === 0 && activeFetch === 0;
  };

  (function() {
    $(document).ready(function() {
      if ($('#top-progress-bar').length === 0) {
        $('head').append(`
          <style>
            #top-progress-bar {
              position: fixed; top: 0; left: 0; width: 0; height: 3px;
              background: linear-gradient(90deg, #ffcc00, #ff9900);
              box-shadow: 0 0 10px rgba(255, 204, 0, 0.7), 0 0 5px rgba(255, 204, 0, 0.4);
              z-index: 1000000005;
              transition: width 0.3s ease, opacity 0.3s ease;
              opacity: 0; pointer-events: none;
            }
            #top-progress-bar.loading {
              opacity: 1;
              animation: pulseProgressBar 2s infinite ease-in-out;
            }
            @keyframes pulseProgressBar {
              0% { filter: brightness(1); }
              50% { filter: brightness(1.3); }
              100% { filter: brightness(1); }
            }
          </style>
        `);
        $('body').append('<div id="top-progress-bar"></div>');
      }
    });

    let progressInterval = null;
    let currentProgress = 0;

    window.startTopProgress = function() {
      if (progressInterval) clearInterval(progressInterval);
      const $bar = $('#top-progress-bar');
      $bar.addClass('loading').css({ width: '0%', opacity: 1 });
      currentProgress = 15;
      $bar.css('width', currentProgress + '%');
      progressInterval = setInterval(() => {
        if (currentProgress < 90) {
          currentProgress += (90 - currentProgress) * 0.15;
          $bar.css('width', currentProgress + '%');
        }
      }, 100);
    };

    window.endTopProgress = function() {
      if (progressInterval) clearInterval(progressInterval);
      const $bar = $('#top-progress-bar');
      $bar.css('width', '100%');
      setTimeout(() => {
        $bar.css('opacity', 0);
        setTimeout(() => $bar.removeClass('loading').css('width', '0%'), 300);
      }, 200);
    };

    $(document).on('ajaxStart fetchStart', function() {
      window.startTopProgress();
    });

    $(document).on('ajaxStop fetchEnd', function() {
      setTimeout(() => {
        if (window.isNetworkIdle()) {
          window.endTopProgress();
        }
      }, 50);
    });
  })();

  $(document).off('click', '.sidebar-menu .menu-item').on('click', '.sidebar-menu .menu-item', function(e) {
    if ($(this).hasClass('dropdown-toggle')) return;
    if (isTransitioning) return;

    e.preventDefault();
    const $this = $(this);
    const oldUrl = sessionStorage.getItem('active_nav_url');
    const newUrl = $this.attr('data-target');

    if (!newUrl) return;

    var role = localStorage.getItem('userRole');
    var adminPages = ['assets/pages/admin.php', 'assets/pages/settings.php'];
    if (adminPages.includes(newUrl) && role !== 'admin') {
      window.showToast('Unauthorized', 'error');
      return;
    }

    sessionStorage.setItem('active_nav_url', newUrl);
    updateURL(newUrl);
    if (newUrl === oldUrl && navigationCache[newUrl]) return;

    isTransitioning = true;
    $('.full_app_loader').css('display', 'flex').hide().fadeIn(200);

    if (oldUrl) {
      const $currentPage = $(`[data-navigation_url="${oldUrl}"]`);
      if ($currentPage.length > 0) {
        const scrollPos = $(window).scrollTop();
        const $oldItem = $(`.sidebar-menu .menu-item[data-target="${oldUrl}"]`);
        $oldItem.attr('data-scrolled_from_top', scrollPos);
        $currentPage.removeClass('show').addClass('hidden');
        navigationCache[oldUrl] = $currentPage.detach();
      }
    }

    $('.sidebar-menu .menu-item').removeClass('active');
    $this.addClass('active');
    if (window.closeSidebar) window.closeSidebar();

    setTimeout(function() {
      if (navigationCache[newUrl]) {
        const $page = navigationCache[newUrl];
        $('#content').append($page);
        $page.removeClass('hidden');
        setTimeout(function() { $page.addClass('show'); }, 50);
        $(document).trigger('page_shown', [newUrl]);
        const scrolled = parseInt($this.attr('data-scrolled_from_top')) || 0;
        $(window).scrollTop(scrolled);
        setTimeout(function() {
          $('.full_app_loader').fadeOut(300);
          isTransitioning = false;
        }, 200);
      } else {
        load_navigation_html_content(newUrl);
      }
    }, 300);
  });

  $(window).on('popstate', function(e) {
    if (isTransitioning) return;
    var state = e.originalEvent && e.originalEvent.state;
    var page = (state && state.page) ? state.page : getPageFromURL();
    if (!page) return;

    var oldUrl = sessionStorage.getItem('active_nav_url');
    sessionStorage.setItem('active_nav_url', page);

    if (oldUrl && oldUrl !== page) {
      var $currentPage = $('[data-navigation_url="' + oldUrl + '"]');
      if ($currentPage.length > 0) {
        var scrollPos = $(window).scrollTop();
        var $oldItem = $('.sidebar-menu .menu-item[data-target="' + oldUrl + '"]');
        $oldItem.attr('data-scrolled_from_top', scrollPos);
        $currentPage.removeClass('show').addClass('hidden');
        navigationCache[oldUrl] = $currentPage.detach();
      }
    }

    $('.sidebar-menu .menu-item').removeClass('active');
    $('.sidebar-menu .menu-item[data-target="' + page + '"]').addClass('active');

    if (navigationCache[page]) {
      var $cached = navigationCache[page];
      $('#content').append($cached);
      $cached.removeClass('hidden');
      setTimeout(function() { $cached.addClass('show'); }, 50);
      $(document).trigger('page_shown', [page]);
      var scrolled = parseInt($('.sidebar-menu .menu-item[data-target="' + page + '"]').attr('data-scrolled_from_top')) || 0;
      $(window).scrollTop(scrolled);
    } else {
      load_navigation_html_content(page);
    }
  });

  $(document).on('page_ready', function(e, url) {
    const activeUrl = sessionStorage.getItem('active_nav_url');
    if (url === activeUrl) {
      const $page = $(`[data-navigation_url="${url}"]`);
      $page.removeClass('hidden');
      setTimeout(function() { $page.addClass('show'); }, 50);
      $(document).trigger('page_shown', [url]);
      const scrolled = parseInt($('.sidebar-menu .menu-item[data-target="' + url + '"]').attr('data-scrolled_from_top')) || 0;
      $(window).scrollTop(scrolled);
      setTimeout(function() {
        $('.full_app_loader').fadeOut(400);
        isTransitioning = false;
      }, 200);
    }
  });
}

function resolvePageUrl(url) {
  if (!url || /^https?:\/\//i.test(url) || url.indexOf('/') === 0) return url;
  var base = typeof BASE_PATH !== 'undefined' ? BASE_PATH.replace(/\/+$/, '') : '';
  return base + '/' + url.replace(/^\.\.\//g, '');
}

function load_navigation_html_content(url) {
  let safetyTimeout = null;
  let transitionCompleted = false;

  function completeTransition() {
    if (transitionCompleted) return;
    transitionCompleted = true;
    if (safetyTimeout) clearTimeout(safetyTimeout);
    $(document).off('ajaxStop.nav fetchEnd.nav');

    const $page = $(`[data-navigation_url="${url}"]`);
    if ($page.length > 0) {
      $page.removeClass('hidden');
      setTimeout(function() { $page.addClass('show'); }, 50);
    }
    $(document).trigger('page_shown', [url]);

    $('.full_app_loader').fadeOut(400);
    isTransitioning = false;
    if (typeof i18n !== 'undefined') i18n.translateDOM();
  }

  $(document).on('ajaxStop.nav fetchEnd.nav', function() {
    setTimeout(function() {
      if (window.isNetworkIdle()) {
        completeTransition();
      }
    }, 250);
  });

  safetyTimeout = setTimeout(function() {
    completeTransition();
  }, 4000);

  $.ajax({
    type: 'GET',
    url: resolvePageUrl(url),
    success: function(res) {
      var $page = $('<div data-navigation_url="' + url + '" class="hidden">' + res + '</div>');
      $('#content').append($page);
      if (typeof PopupManager !== 'undefined') PopupManager.init();
    },
    error: function() {
      completeTransition();
    }
  });
}

window.showToast = function(message, type) {
  var bgColor = '#333';
  if (type === 'success') bgColor = '#10b981';
  else if (type === 'error' || type === 'danger') bgColor = '#ef4444';
  Toastify({ text: message, duration: 3000, backgroundColor: bgColor, gravity: 'bottom', position: 'right', stopOnFocus: true }).showToast();
};

$(document).ready(function() {
  var role = localStorage.getItem('userRole');
  var adminPages = ['assets/pages/admin.php', 'assets/pages/settings.php'];
  var savedUrl = getPageFromURL() || sessionStorage.getItem('active_nav_url');
  if (savedUrl && adminPages.includes(savedUrl) && role !== 'admin') {
    savedUrl = null;
    sessionStorage.removeItem('active_nav_url');
  }
  var $targetItem = savedUrl ? $(`.sidebar-menu .menu-item[data-target="${savedUrl}"]`) : null;
  if (!$targetItem || !$targetItem.length) {
    $targetItem = $('.sidebar-menu .menu-item.active').eq(0);
  }
  if ($targetItem && $targetItem.length) {
    $('.sidebar-menu .menu-item').removeClass('active');
    $targetItem.addClass('active');
    var url = $targetItem.attr('data-target');
    sessionStorage.setItem('active_nav_url', url);
    if (window.history && window.history.replaceState) {
      window.history.replaceState({page: url}, '', window.location.href);
    }
    $('.full_app_loader').css('display', 'flex').show();
    load_navigation_html_content(url);
  }
});
