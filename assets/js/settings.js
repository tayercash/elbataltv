$(document).ready(function () {
    var originalValue = null;

    function requesterData() {
        var hwid = btoa(navigator.userAgent + screen.width + screen.height + navigator.language);
        return { requesterId: parseInt(localStorage.getItem('userId') || 0), requesterHwid: hwid, requesterToken: localStorage.getItem('userToken') || '' };
    }

    function renderModeLabel(mode) {
        var key = mode === 'server' ? 'settings.current_server' : 'settings.current_drive';
        var label = window.t ? t(key) : (mode === 'server' ? 'الخادم المحلي' : 'Google Drive');
        $('#mode_label').text(label);
    }

    function loadSettings() {
        $.ajax({
            url: 'api.php?action=get_setting&key=upload_mode',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(Object.assign({ key: 'upload_mode' }, requesterData())),
            success: function (res) {
                var mode = res.value || 'drive';
                originalValue = mode;
                $('#upload_mode_toggle').prop('checked', mode === 'server');
                renderModeLabel(mode);
                $('#save_settings_btn').prop('disabled', true);
                $('#save_status').text('').removeClass('success error');
            },
            error: function () {
                $('#upload_mode_toggle').prop('checked', false);
                originalValue = 'drive';
                renderModeLabel('drive');
            }
        });
    }

    $('#upload_mode_toggle').on('change', function () {
        var currentMode = $(this).is(':checked') ? 'server' : 'drive';
        renderModeLabel(currentMode);
        $('#save_settings_btn').prop('disabled', currentMode === originalValue);
        $('#save_status').text('').removeClass('success error');
    });

    $('#save_settings_btn').on('click', function () {
        var mode = $('#upload_mode_toggle').is(':checked') ? 'server' : 'drive';
        var btn = $(this).prop('disabled', true);
        var savingText = window.t ? t('common.loading') : 'جاري الحفظ...';
        btn.text(savingText);
        $('#save_status').text('').removeClass('success error');

        $.ajax({
            url: 'api.php?action=save_setting',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(Object.assign({ key: 'upload_mode', value: mode }, requesterData())),
            success: function () {
                originalValue = mode;
                var saveText = window.t ? t('settings.save') : 'حفظ الإعدادات';
                btn.prop('disabled', true).text(saveText);
                var msg = window.t ? t('settings.saved') : '✅ تم حفظ الإعدادات';
                $('#save_status').text(msg).addClass('success');
            },
            error: function () {
                var saveText = window.t ? t('settings.save') : 'حفظ الإعدادات';
                btn.prop('disabled', false).text(saveText);
                var msg = window.t ? t('settings.error') : '❌ فشل حفظ الإعدادات';
                $('#save_status').text(msg).addClass('error');
            }
        });
    });

    $(document).on('page_shown', function (e, url) {
        if (url && url.includes('settings.php')) {
            if (typeof i18n !== 'undefined') i18n.translateDOM();
            loadSettings();
        }
    });

    if ($('#upload_mode_toggle').length) {
        loadSettings();
    }
});
