<style>
.devices-container {
    max-width: 800px;
    margin: 0 auto;
}

.device-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 12px;
    transition: all 0.3s;
    position: relative;
}

.device-card.current-device {
    border-color: rgba(255, 204, 0, 0.3);
    background: rgba(255, 204, 0, 0.04);
}

.device-card-head {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 12px;
}

.device-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(255, 204, 0, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.device-icon i {
    font-size: 1.3rem;
    color: #ffcc00;
}

.device-name {
    flex: 1;
    font-weight: 600;
    color: #fff;
    font-size: 1rem;
}

.device-badge {
    font-size: 0.7rem;
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 500;
}

.device-badge.current {
    background: rgba(255, 204, 0, 0.15);
    color: #ffcc00;
}

.device-badge.other {
    background: rgba(255, 255, 255, 0.05);
    color: #888;
}

.device-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-bottom: 14px;
}

.device-detail {
    font-size: 0.82rem;
    color: #999;
}

.device-detail span {
    color: #ccc;
}

.device-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn-logout-device {
    padding: 8px 20px;
    border-radius: 10px;
    border: none;
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-logout-device:hover {
    background: rgba(239, 68, 68, 0.25);
}

.device-meta {
    font-size: 0.75rem;
    color: #666;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid rgba(255,255,255,0.04);
}

@media (max-width: 600px) {
    .device-details {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="page-header">
    <h2 data-i18n="sidebar.devices">الأجهزة المسجلة</h2>
</div>

<div class="devices-container" id="devices-container">
    <div class="loading-spinner" style="text-align:center;padding:40px;color:#888;">
        <i class="fas fa-spinner fa-spin fa-2x"></i>
        <p style="margin-top:12px;" data-i18n="common.loading">جاري التحميل...</p>
    </div>
</div>

<script>
$(document).ready(function () {
    function t(key) {
        return typeof i18n !== 'undefined' ? i18n.t(key) : key;
    }

    function getPlatformIcon(info) {
        var ua = (info.userAgent || '').toLowerCase();
        if (ua.includes('android')) return 'fa-android';
        if (ua.includes('iphone') || ua.includes('ipad')) return 'fa-apple';
        if (ua.includes('win')) return 'fa-windows';
        if (ua.includes('mac')) return 'fa-apple';
        if (ua.includes('linux')) return 'fa-linux';
        return 'fa-globe';
    }

    function getDeviceLabel(info) {
        var ua = info.userAgent || '';
        if (ua.includes('Android')) return 'Android';
        if (ua.includes('iPhone')) return 'iPhone';
        if (ua.includes('iPad')) return 'iPad';
        if (ua.includes('Windows')) return 'Windows';
        if (ua.includes('Mac OS')) return 'Mac';
        if (ua.includes('Linux')) return 'Linux';
        return t('devices.unknown_device');
    }

    function renderDevices(devices, currentHwid) {
        var container = $('#devices-container');
        if (!devices || !devices.length) {
            container.html('<div style="text-align:center;padding:40px;color:#888;"><i class="fas fa-mobile-alt fa-2x" style="margin-bottom:12px;display:block;"></i><span>' + t('devices.no_devices') + '</span></div>');
            if (typeof i18n !== 'undefined') i18n.translateDOM();
            return;
        }
        var html = '';
        devices.forEach(function (d) {
            var info = {};
            try { info = JSON.parse(d.device_info || '{}'); } catch(e) {}
            var isCurrent = d.hwid === currentHwid;
            var icon = getPlatformIcon(info);
            var label = getDeviceLabel(info);
            var platform = info.platform || label;
            var browser = info.userAgent ? info.userAgent.split(' ').slice(-1)[0] : '-';
            html += '<div class="device-card' + (isCurrent ? ' current-device' : '') + '">';
            html += '<div class="device-card-head">';
            html += '<div class="device-icon"><i class="fab ' + icon + '"></i></div>';
            html += '<div class="device-name">' + platform + '</div>';
            html += '<span class="device-badge ' + (isCurrent ? 'current' : 'other') + '">' + (isCurrent ? t('devices.current_device') : '') + '</span>';
            html += '</div>';
            html += '<div class="device-details">';
            html += '<div class="device-detail"><span>' + t('common.browser') + '</span>: ' + browser + '</div>';
            html += '<div class="device-detail"><span>' + t('common.screen') + '</span>: ' + (info.screenWidth || '-') + 'x' + (info.screenHeight || '-') + '</div>';
            html += '<div class="device-detail"><span>' + t('admin.last_activity') + '</span>: ' + toLocalTime(d.last_login) + '</div>';
            html += '<div class="device-detail"><span>' + t('common.date') + '</span>: ' + toLocalTime(d.created_at) + '</div>';
            html += '</div>';
            if (!isCurrent) {
                html += '<div class="device-actions">';
                html += '<button class="btn-logout-device" data-id="' + d.id + '"><i class="fas fa-sign-out-alt"></i> ' + t('devices.logout_device') + '</button>';
                html += '</div>';
            }
            html += '<div class="device-meta">HWID: ' + d.hwid.substring(0, 16) + '...</div>';
            html += '</div>';
        });
        container.html(html);
        if (typeof i18n !== 'undefined') i18n.translateDOM();
    }

    function loadDevices() {
        var currentHwid = localStorage.getItem('deviceHwid');
        if (!currentHwid) {
            currentHwid = btoa(navigator.userAgent + screen.width + screen.height + navigator.language);
            localStorage.setItem('deviceHwid', currentHwid);
        }
        $.ajax({
            url: 'api.php?action=get_user_devices',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                requesterId: parseInt(localStorage.getItem('userId') || 0),
                requesterHwid: currentHwid,
                requesterToken: localStorage.getItem('userToken') || ''
            }),
            success: function (res) {
                if (Array.isArray(res)) {
                    renderDevices(res, currentHwid);
                } else {
                    $('#devices-container').html('<div style="text-align:center;padding:40px;color:#ef4444;">' + t('devices.load_error') + '</div>');
                    if (typeof i18n !== 'undefined') i18n.translateDOM();
                }
            },
            error: function () {
                $('#devices-container').html('<div style="text-align:center;padding:40px;color:#ef4444;">' + t('toast.server_error') + '</div>');
                if (typeof i18n !== 'undefined') i18n.translateDOM();
            }
        });
    }

    $(document).on('click', '.btn-logout-device', function () {
        var btn = $(this);
        var deviceId = btn.data('id');
        var card = btn.closest('.device-card');
        if (!confirm(t('devices.confirm_logout'))) return;
        btn.prop('disabled', true).css('opacity', '0.5');
        $.ajax({
            url: 'api.php?action=remove_user_device',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                requesterId: parseInt(localStorage.getItem('userId') || 0),
                requesterHwid: localStorage.getItem('deviceHwid') || '',
                requesterToken: localStorage.getItem('userToken') || '',
                device_id: deviceId
            }),
            success: function (res) {
                if (res.success) {
                    card.slideUp(300, function () { $(this).remove(); });
                } else {
                    alert(res.error || t('devices.delete_error'));
                    btn.prop('disabled', false).css('opacity', '1');
                }
            },
            error: function () {
                alert(t('toast.server_error'));
                btn.prop('disabled', false).css('opacity', '1');
            }
        });
    });

    loadDevices();
});
</script>