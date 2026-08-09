(function(){
  var lang = localStorage.getItem('lang') || 'ar';
  document.querySelectorAll('.lang-btn').forEach(function(btn){ if (btn.getAttribute('data-lang') === lang) btn.classList.add('active'); });
  if (typeof i18n !== 'undefined') i18n.translateDOM();
  var hasSession = localStorage.getItem('userId') && localStorage.getItem('userToken');
  var username = localStorage.getItem('username') || 'User';
  var userRole = localStorage.getItem('userRole') || 'user';
  var userAvatar = localStorage.getItem('userAvatar') || '';
  var guestActions = document.getElementById('guest-actions');
  var userMenu = document.getElementById('user-menu');
  var dashboardLink = document.getElementById('dashboard-link');
  var userDashboardLink = document.getElementById('user-dashboard-link');
  var avatarImg = document.getElementById('user-avatar-img');
  var avatarInitial = document.getElementById('user-avatar-initial');
  var adminLabel = document.getElementById('admin-dashboard-label');

  function avatarUrl(url) {
    if (!url || /^https?:\/\//i.test(url) || url.charAt(0) === '/') return url;
    if (url.indexOf('../') === 0) return url.substring(3);
    return url;
  }

  if (adminLabel) adminLabel.textContent = lang === 'ar' ? 'لوحة تحكم الأدمن' : 'Admin Dashboard';
  dashboardLink.style.display = hasSession ? 'inline-flex' : 'none';
  document.querySelectorAll('.guest-hero-link').forEach(function(link){ link.style.display = hasSession ? 'none' : 'inline-flex'; });
  guestActions.style.display = hasSession ? 'none' : 'flex';

  if (hasSession) {
    document.getElementById('user-menu-name').textContent = username;
    avatarInitial.textContent = username.charAt(0).toUpperCase();
    if (userAvatar) {
      avatarImg.src = avatarUrl(userAvatar);
      avatarImg.alt = username;
      avatarImg.onerror = function(){ avatarImg.style.display = 'none'; avatarInitial.style.display = 'inline'; };
      avatarImg.style.display = 'block';
      avatarInitial.style.display = 'none';
    }
    userMenu.classList.add('is-active');
    if (userRole === 'admin') {
      userMenu.classList.add('is-admin');
      dashboardLink.href = 'dashboard/?view_as_user=1';
      userDashboardLink.href = 'dashboard/?view_as_user=1';
    }
  }
})();
