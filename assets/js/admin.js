$(document).ready(function () {
    let activeUsersData = [];
    let currentPage = 1;
    let currentSort = 'id';
    let currentSortDir = 'DESC';
    let searchTimer = null;

    function requesterData() {
        var hwid = btoa(navigator.userAgent + screen.width + screen.height + navigator.language);
        return { requesterId: parseInt(localStorage.getItem('userId') || 0), requesterHwid: hwid, requesterToken: localStorage.getItem('userToken') || '' };
    }

    function stringToColor(str) {
        if (!str) return '#fff';
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        const colors = [
            '#ffcc00', '#00ffcc', '#ff66cc', '#ccff66', 
            '#66ccff', '#ff9966', '#cc99ff', '#66ff99',
            '#ff5e5e', '#5eff5e', '#5e5eff', '#ffff5e'
        ];
        const index = Math.abs(hash) % colors.length;
        return colors[index];
    }

    function t(key) {
        return typeof i18n !== 'undefined' ? i18n.t(key) : key;
    }

    function initializeAdminPage() {
        currentPage = 1;
        currentSort = 'id';
        currentSortDir = 'DESC';
        $('#admin-search-input').val('');
        $('.mou_admin_table th.sortable').removeClass('active').find('.sort-icon i').attr('class', 'fas fa-sort');
        $('.mou_admin_table th.sortable[data-sort="id"]').addClass('active').find('.sort-icon i').attr('class', 'fas fa-sort-down');
        if (typeof initCustomSelects !== 'undefined') initCustomSelects();
        fetchUsers();
        fetchOnlineUsers();
    }

    function fetchUsers() {
        var perPage = parseInt($('#per-page-select').val()) || 20;
        var search = $('#admin-search-input').val().trim();
        var data = Object.assign({ page: currentPage, per_page: perPage, sort: currentSort, sort_dir: currentSortDir }, requesterData());
        if (search) data.search = search;

        $.ajax({
            url: 'api.php?action=get_users_paginated',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function (res) {
                if (!res.success) { showToast(t('toast.error'), 'error'); return; }
                var container = $('#admin_users_container');
                container.empty();
                (res.users || []).forEach(function (user) {
                    container.append(createUserRow(user));
                });
                updatePagination(res);
                refreshUserStatus();
            },
            error: function () {
                showToast(t('toast.server_error'), 'error');
            }
        });
    }

    function fetchOnlineUsers(callback) {
        var ids = [];
        $('.user-row').each(function () {
            var id = $(this).data('id');
            if (id) ids.push(id);
        });
        var data = Object.assign({ ids: ids }, requesterData());

        $.ajax({
            url: 'api.php?action=get_online_users',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function (onlineList) {
                activeUsersData = onlineList || [];
                if (callback) callback();
                else refreshUserStatus();
            }
        });
    }

    function updatePagination(res) {
        var page = res.page;
        var totalPages = res.total_pages;
        var total = res.total;
        var perPage = res.per_page;

        var start = (page - 1) * perPage + 1;
        var end = Math.min(page * perPage, total);
        $('#page-info').text(start + '-' + end + ' / ' + total);

        $('#page-prev').prop('disabled', page <= 1);
        $('#page-next').prop('disabled', page >= totalPages);

        var numContainer = $('#page-numbers');
        numContainer.empty();

        var maxVisible = 7;
        var half = Math.floor(maxVisible / 2);
        var startPage = Math.max(1, page - half);
        var endPage = Math.min(totalPages, startPage + maxVisible - 1);
        if (endPage - startPage + 1 < maxVisible) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        if (startPage > 1) {
            numContainer.append('<button class="page-btn" data-page="1">1</button>');
            if (startPage > 2) numContainer.append('<span style="color:#666;padding:0 4px;">...</span>');
        }
        for (var i = startPage; i <= endPage; i++) {
            var active = i === page ? 'active' : '';
            numContainer.append('<button class="page-btn ' + active + '" data-page="' + i + '">' + i + '</button>');
        }
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) numContainer.append('<span style="color:#666;padding:0 4px;">...</span>');
            numContainer.append('<button class="page-btn" data-page="' + totalPages + '">' + totalPages + '</button>');
        }

        var activeCount = 0;
        $('#stat-total').text(res.site_total || res.total);
        $('#stat-online').text(res.online_count || 0);
        $('#stat-disabled').text(res.disabled_count || 0);
    }

    function createUserRow(user) {
        var isDisabled = user.status == 0;
        var genderLabels = { male: t('profile.male'), female: t('profile.female') };
        var genderText = user.gender ? genderLabels[user.gender] || user.gender : '-';
        var countryText = user.country ? getCountryLabel(user.country) : '-';
        var roleLabel = user.role === 'admin' ? t('admin.role_admin') : t('admin.role_user');
        var isSelfOrAdmin = user.username === 'admin';

        var avatarHtml = user.avatar ? '<img src="' + user.avatar + '" style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">' : '<div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#ffcc00,#ff9900);color:#000;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:0.8rem;flex-shrink:0;">' + (user.username ? user.username.charAt(0).toUpperCase() : '?') + '</div>';

        return '<tr class="user-row" id="user_' + user.id + '" data-username="' + user.username + '">' +
            '<td data-label="#"><span style="color:#666;font-weight:400;">' + user.id + '</span></td>' +
            '<td data-label="' + t('common.username') + '">' +
                '<div style="display:flex;align-items:center;gap:8px;">' +
                    '<span class="online-dot" style="width:8px;height:8px;border-radius:50%;background-color:#555;display:inline-block;transition:all 0.5s ease;flex-shrink:0;"></span>' +
                    avatarHtml +
                    '<span style="color:' + stringToColor(user.username) + ';font-weight:bold;' + (isDisabled ? 'opacity:0.5;text-decoration:line-through;' : '') + '">' + $('<span>').text(user.username).html() + '</span>' +
                '</div>' +
            '</td>' +
            '<td data-label="' + t('common.email') + '" style="color:#999;font-size:0.85rem;">' + (user.email ? $('<span>').text(user.email).html() : '-') + '</td>' +
            '<td data-label="' + t('admin.role') + '">' +
                '<span style="font-size:0.75rem;color:' + (user.role === 'admin' ? '#ffcc00' : '#66ccff') + ';background:' + (user.role === 'admin' ? 'rgba(255,204,0,0.1)' : 'rgba(102,204,255,0.1)') + ';padding:3px 10px;border-radius:8px;font-weight:500;display:inline-block;white-space:nowrap;">' + roleLabel + '</span>' +
            '</td>' +
            '<td data-label="' + t('admin.status') + '">' +
                (isDisabled ? '<span style="color:#ff5252;background:rgba(255,82,82,0.1);padding:3px 10px;border-radius:8px;font-size:0.8rem;font-weight:500;display:inline-block;white-space:nowrap;">' + t('admin.disabled') + '</span>' : '<span style="color:#2ec76e;background:rgba(46,199,110,0.1);padding:3px 10px;border-radius:8px;font-size:0.8rem;font-weight:500;display:inline-block;white-space:nowrap;">' + t('admin.active') + '</span>') +
            '</td>' +
            '<td data-label="' + t('profile.gender') + '" style="color:#999;">' + genderText + '</td>' +
            '<td data-label="' + t('profile.country') + '" style="color:#999;">' + countryText + '</td>' +
            '<td data-label="' + t('admin.created_at') + '" style="color:#888;font-size:0.8rem;white-space:nowrap;">' + toLocalTime(user.created_at) + '</td>' +
            '<td data-label="' + t('admin.last_activity') + '" style="color:#888;font-size:0.8rem;white-space:nowrap;">' + toLocalTime(user.last_activity) + '</td>' +
            '<td data-label="' + t('admin.max_devices') + '">' +
                '<span class="max-devices-badge" data-user-id="' + user.id + '" style="cursor:pointer;font-size:0.8rem;padding:3px 10px;border-radius:8px;font-weight:500;display:inline-block;white-space:nowrap;background:rgba(255,204,0,0.1);color:#ffcc00;" title="' + t('admin.click_to_set') + '">' +
                    (user.max_devices !== null && user.max_devices !== undefined ? user.max_devices : '<span style="color:#888;">' + t('admin.default') + '</span>') +
                '</span>' +
            '</td>' +
            '<td data-label="' + t('admin.actions') + '">' +
                '<div class="user_actions" style="display:flex;gap:6px;flex-wrap:wrap;">' +
                    '<button class="btn-devices" data-id="' + user.id + '" data-max-devices="' + (user.max_devices !== null && user.max_devices !== undefined ? user.max_devices : '') + '" title="' + t('admin.set_max_devices') + '"><i class="fas fa-microchip"></i></button>' +
                    '<button class="btn-edit" data-id="' + user.id + '" data-username="' + user.username + '" data-role="' + (user.role || 'user') + '" ' + (isSelfOrAdmin ? 'disabled' : '') + ' title="' + t('common.edit') + '"><i class="fas fa-edit"></i></button>' +
                    '<button class="btn-logs" data-id="' + user.id + '" data-username="' + user.username + '" title="' + t('admin.view_logs') + '"><i class="fas fa-history"></i></button>' +
                    '<button class="btn-reset" data-id="' + user.id + '" title="' + t('admin.reset_device') + '"><i class="fas fa-redo"></i></button>' +
                    '<button class="' + (isDisabled ? 'btn-enable' : 'btn-disable') + '" data-id="' + user.id + '" data-status="' + user.status + '" ' + (isSelfOrAdmin ? 'disabled' : '') + '><i class="fas fa-toggle-on"></i></button>' +
                    '<button class="btn-delete" data-id="' + user.id + '" data-username="' + user.username + '" ' + (isSelfOrAdmin ? 'disabled' : '') + ' title="' + t('common.delete') + '"><i class="fas fa-trash-alt"></i></button>' +
                '</div>' +
            '</td>' +
        '</tr>';
    }

    function getCountryLabel(code) {
        if (!code || typeof i18n === 'undefined') return code;
        return i18n.getCountryLabel(code);
    }

    function refreshUserStatus() {
        $('.user-row').each(function () {
            var $row = $(this);
            var username = $row.data('username');
            var dot = $row.find('.online-dot');
            var isOnline = activeUsersData.some(function (u) { return u.username === username; });
            if (isOnline) {
                dot.css({ 'background-color': '#2ec76e', 'box-shadow': '0 0 10px #2ec76e' });
            } else {
                dot.css({ 'background-color': '#555', 'box-shadow': 'none' });
            }
        });
    }

    // ---- Logs popup ----
    var logsCurrentUserId = 0;
    var logsCurrentUsername = '';
    var logsPage = 1;

    function openLogsPopup(userId, username) {
        logsCurrentUserId = userId;
        logsCurrentUsername = username;
        logsPage = 1;
        $('#logs-popup-title').text(t('admin.logs_title') + ' - ' + username);
        fetchLogs();
        $('#admin_logs_popup').openpopup();
    }

    function fetchLogs() {
        var perPage = 20;
        $.ajax({
            url: 'api.php?action=get_user_logs',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(Object.assign({ user_id: logsCurrentUserId, page: logsPage, per_page: perPage }, requesterData())),
            success: function (res) {
                if (!res.success) { showToast(t('toast.error'), 'error'); return; }
                var tbody = $('#logs-table-body');
                tbody.empty();
                (res.logs || []).forEach(function (log) {
                    tbody.append(createLogRow(log));
                });
                updateLogsPagination(res);
            },
            error: function () {
                showToast(t('toast.server_error'), 'error');
            }
        });
    }

    function createLogRow(log) {
        var statusClass = log.success == 1 ? 'log-success' : 'log-fail';
        var statusText = log.success == 1 ? '&#10003; ' + t('admin.logs_success') : '&#10007; ' + t('admin.logs_fail');
        var methodText = log.method === 'google' ? 'Google' : t('admin.logs_password');
        var country = log.location_country || '-';
        var city = log.location_city || '-';
        var reason = log.failure_reason ? $('<span>').text(log.failure_reason).html() : '-';
        var ua = log.user_agent ? $('<span>').text(log.user_agent.substring(0, 80) + (log.user_agent.length > 80 ? '...' : '')).html() : '-';
        var time = toLocalTime(log.created_at);

        return '<tr>' +
            '<td data-label="' + t('admin.logs_time') + '" style="white-space:nowrap;font-size:0.8rem;color:#aaa;">' + time + '</td>' +
            '<td data-label="' + t('admin.logs_method') + '"><span style="padding:2px 8px;border-radius:6px;background:rgba(255,255,255,0.06);font-weight:500;font-size:0.8rem;">' + methodText + '</span></td>' +
            '<td data-label="' + t('admin.logs_ip') + '" style="direction:ltr;text-align:left;font-size:0.8rem;color:#aaa;font-family:monospace;">' + (log.ip_address || '-') + '</td>' +
            '<td data-label="' + t('admin.logs_country') + '" style="font-size:0.85rem;">' + country + '</td>' +
            '<td data-label="' + t('admin.logs_city') + '" style="font-size:0.85rem;">' + city + '</td>' +
            '<td data-label="' + t('admin.logs_status') + '" class="' + statusClass + '" style="font-weight:600;">' + statusText + '</td>' +
            '<td data-label="' + t('admin.logs_reason') + '" style="font-size:0.8rem;color:#999;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + (log.failure_reason || '') + '">' + reason + '</td>' +
            '<td data-label="' + t('admin.logs_user_agent') + '" style="font-size:0.75rem;color:#777;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + (log.user_agent || '') + '">' + ua + '</td>' +
        '</tr>';
    }

    function updateLogsPagination(res) {
        var page = res.page;
        var totalPages = res.total_pages;
        var total = res.total;
        $('#logs-page-prev').prop('disabled', page <= 1);
        $('#logs-page-next').prop('disabled', page >= totalPages);
        var start = (page - 1) * res.per_page + 1;
        var end = Math.min(page * res.per_page, total);
        $('#logs-page-info').text(start + '-' + end + ' / ' + total);
    }

    // ---- Event handlers ----

    // Pagination
    $(document).on('click', '#page-prev', function () {
        if (currentPage > 1) { currentPage--; fetchUsers(); }
    });
    $(document).on('click', '#page-next', function () {
        currentPage++; fetchUsers();
    });
    $(document).on('click', '#page-numbers .page-btn', function () {
        currentPage = parseInt($(this).data('page'));
        fetchUsers();
    });
    $(document).on('change', '#per-page-select', function () {
        currentPage = 1;
        fetchUsers();
    });

    // Search
    $(document).on('input', '#admin-search-input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            currentPage = 1;
            fetchUsers();
        }, 400);
    });

    // Sort
    $(document).on('click', '.mou_admin_table th.sortable', function () {
        var sort = $(this).data('sort');
        if (currentSort === sort) {
            currentSortDir = currentSortDir === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentSort = sort;
            currentSortDir = 'ASC';
        }
        $('.mou_admin_table th.sortable').removeClass('active');
        $(this).addClass('active');
        $('.mou_admin_table th.sortable .sort-icon i').attr('class', 'fas fa-sort');
        $(this).find('.sort-icon i').attr('class', 'fas fa-sort-' + (currentSortDir === 'ASC' ? 'up' : 'down'));
        currentPage = 1;
        fetchUsers();
    });

    // Reset device
    $(document).on('click', '.user_actions .btn-reset', async function (e) {
        e.preventDefault();
        var userId = $(this).data('id');
        var ok = await confirmPopup({
            title: t('common.confirm'),
            message: t('admin.confirm_reset'),
            confirmText: t('common.confirm'),
            cancelText: t('common.cancel')
        });
        if (!ok) return;

        $.ajax({
            url: 'api.php?action=reset_hwid',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(Object.assign({ userId: userId }, requesterData())),
            success: function (data) {
                showToast(data.success ? t('admin.reset_success') : t('toast.error') + (data.error || ''), data.success ? 'success' : 'error');
            },
            error: function () {
                showToast(t('toast.server_error'), 'error');
            }
        });
    });

    // Toggle status
    $(document).on('click', '.user_actions .btn-enable, .user_actions .btn-disable', async function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var currentStatus = $(this).data('status');
        var action = currentStatus == 1 ? t('admin.disable') : t('admin.enable');
        var ok = await confirmPopup({
            title: t('common.confirm'),
            message: t('admin.confirm_toggle', { action: action }),
            confirmText: t('common.confirm'),
            cancelText: t('common.cancel')
        });
        if (!ok) return;

        $.ajax({
            url: 'api.php?action=toggle_user_status',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(Object.assign({ id: id, currentStatus: currentStatus }, requesterData())),
            success: function (data) {
                if (data.success) {
                    showToast(t('admin.toggle_success', { action: action }), 'success');
                    fetchUsers();
                } else {
                    showToast(data.error || '', 'error');
                }
            },
            error: function () {
                showToast(t('toast.server_error'), 'error');
            }
        });
    });

    // Delete user
    $(document).on('click', '.user_actions .btn-delete', async function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var username = $(this).data('username');
        var ok = await confirmPopup({
            title: t('common.delete'),
            message: t('admin.confirm_delete', { name: username }),
            confirmText: t('common.delete'),
            cancelText: t('common.cancel')
        });
        if (!ok) return;

        $.ajax({
            url: 'api.php?action=delete_user',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(Object.assign({ id: id }, requesterData())),
            success: function (data) {
                if (data.success) {
                    showToast(t('admin.delete_success'), 'success');
                    fetchUsers();
                } else {
                    showToast(data.error || t('admin.add_failed'), 'error');
                }
            },
            error: function () {
                showToast(t('toast.server_error'), 'error');
            }
        });
    });

    // Logs
    $(document).on('click', '.user_actions .btn-logs', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var username = $(this).data('username');
        openLogsPopup(id, username);
    });
    $(document).on('click', '#logs-page-prev', function () {
        if (logsPage > 1) { logsPage--; fetchLogs(); }
    });
    $(document).on('click', '#logs-page-next', function () {
        logsPage++; fetchLogs();
    });

    // Add user
    $(document).on('submit', '#admin_add_user_form', function (e) {
        e.preventDefault();
        var username = $('#admin_username').val().trim();
        var email = $('#admin_email').val().trim();
        var password = $('#admin_password').val().trim();
        var confirm = $('#admin_confirm_password').val().trim();
        var role = $('#admin_role').val();
        if (!username || !password) {
            showToast(t('admin.validation_required'), 'error');
            return;
        }

        if (password !== confirm) {
            showToast(t('admin.passwords_mismatch'), 'error');
            return;
        }

        $.ajax({
            url: 'api.php?action=add_user',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(Object.assign({ username: username, email: email, password: password, role: role }, requesterData())),
            success: function (data) {
                if (data.success) {
                    showToast(t('admin.add_success'), 'success');
                    $('#admin_username, #admin_email, #admin_password, #admin_confirm_password').val('');
                    $('#admin_add_user_popup').closepopup();
                    fetchUsers();
                } else {
                    showToast(data.error || t('admin.add_failed'), 'error');
                }
            },
            error: function () {
                showToast(t('toast.server_error'), 'error');
            }
        });
    });

    $(document).on('click', '.add_user_btn', function () {
        $('#admin_username, #admin_email, #admin_password, #admin_confirm_password').val('');
        $('#admin_add_user_popup').openpopup();
    });

    // Toggle search bar
    $(document).on('click', '.admin-search-toggle-btn', function () {
        var $bar = $('.admin-search-sticky');
        $bar.toggleClass('show');
        $(this).toggleClass('active');
        if ($bar.hasClass('show')) {
            $('#admin-search-input').focus();
        }
    });
    $(document).on('click', '.admin-search-close-btn', function () {
        $('.admin-search-sticky').removeClass('show');
        $('.admin-search-toggle-btn').removeClass('active');
        $('#admin-search-input').val('').trigger('input');
    });
    $(document).on('keydown', '#admin-search-input', function (e) {
        if (e.key === 'Escape') {
            $('.admin-search-close-btn').click();
        }
    });

    // Edit user
    $(document).on('click', '.user_actions .btn-edit', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var username = $(this).data('username');
        var role = $(this).data('role') || 'user';
        $('#edit_user_id').val(id);
        $('#edit_username').val(username);
        $('#edit_role').val(role);
        $('#edit_password').val('');
        $('#admin_edit_user_popup').openpopup();
    });

    $(document).on('submit', '#admin_edit_user_form', function (e) {
        e.preventDefault();
        var id = $('#edit_user_id').val();
        var username = $('#edit_username').val().trim();
        var password = $('#edit_password').val().trim();
        var role = $('#edit_role').val();

        if (!username) {
            showToast(t('admin.validation_required'), 'error');
            return;
        }

        var data = Object.assign({ id: id, username: username, role: role }, requesterData());
        if (password) data.password = password;

        $.ajax({
            url: 'api.php?action=update_user',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function (res) {
                if (res.success) {
                    showToast(t('admin.edit_success'), 'success');
                    $('#admin_edit_user_popup').closepopup();
                    fetchUsers();
                } else {
                    showToast(res.error || t('admin.edit_failed'), 'error');
                }
            },
            error: function () {
                showToast(t('toast.server_error'), 'error');
            }
        });
    });

    // ---- Max Devices popup ----
    function openMaxDevicesPopup(userId, currentVal) {
        $('#max_devices_user_id').val(userId);
        if (currentVal !== '' && currentVal !== null && currentVal !== undefined) {
            $('#max_devices_input').val(currentVal).prop('disabled', false).prop('readonly', false);
            $('#max_devices_reset_toggle').removeClass('active');
        } else {
            $('#max_devices_input').val('').prop('disabled', true).prop('readonly', true);
            $('#max_devices_reset_toggle').addClass('active');
        }
        $('#admin_max_devices_popup').openpopup();
        setTimeout(function () { if (!$('#max_devices_input').prop('disabled')) $('#max_devices_input').focus(); }, 300);
    }

    $(document).on('click', '.btn-devices', function () {
        var userId = $(this).data('id');
        var currentVal = $(this).data('max-devices');
        openMaxDevicesPopup(userId, currentVal);
    });

    $(document).on('click', '.max-devices-badge', function () {
        var userId = $(this).data('user-id');
        var $btn = $('.btn-devices[data-id="' + userId + '"]');
        var currentVal = $btn.length ? $btn.data('max-devices') : '';
        openMaxDevicesPopup(userId, currentVal);
    });

    $(document).on('click', '#max_devices_reset_toggle', function () {
        $(this).toggleClass('active');
        if ($(this).hasClass('active')) {
            $('#max_devices_input').val('').prop('disabled', true).prop('readonly', true);
        } else {
            $('#max_devices_input').prop('disabled', false).prop('readonly', false).focus();
        }
    });

    $(document).on('submit', '#admin_max_devices_form', function (e) {
        e.preventDefault();
        var userId = $('#max_devices_user_id').val();
        var resetDefault = $('#max_devices_reset_toggle').hasClass('active');
        var maxDevices = resetDefault ? '' : $('#max_devices_input').val();

        $.ajax({
            url: 'api.php?action=update_user_max_devices',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(Object.assign({ user_id: userId, max_devices: maxDevices }, requesterData())),
            success: function (res) {
                if (res.success) {
                    showToast(t('common.success'), 'success');
                    $('#admin_max_devices_popup').closepopup();
                    fetchUsers();
                } else {
                    showToast(res.error || t('toast.error'), 'error');
                }
            },
            error: function () {
                showToast(t('toast.server_error'), 'error');
            }
        });
    });

    // Page shown / init
    $(document).on('page_shown', function (e, url) {
        if (url && url.includes('admin.php')) {
            initializeAdminPage();
        }
    });

    if ($('#admin_users_container').length) {
        initializeAdminPage();
    }

    // Online status refresh + update stat card
    setInterval(function () {
        if ($('#admin_users_container').length) {
            fetchOnlineUsers();
        }
        $.ajax({
            url: 'api.php?action=get_online_users',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(requesterData()),
            success: function (list) {
                $('#stat-online').text((list || []).length);
            }
        });
    }, 5000);
});