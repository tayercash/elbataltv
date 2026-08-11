var API_URL = "api.php";
var REFRESH_INTERVAL = 3000;
var timer = null;
var currentStatus = "";
var currentSearch = "";

var STATUS_NAMES = {
    queued: "في الانتظار",
    downloading: "جاري التحميل",
    paused: "متوقف مؤقتًا",
    completed: "مكتملة",
    error: "خطأ",
    cancelled: "ملغاة"
};

var PLATFORM_NAMES = {
    android: "أندرويد",
    electron: "إلكترون",
    web: "ويب",
    admin: "الإدارة"
};

function formatBytes(bytes, decimals) {
    if (!bytes) return "0 B";
    decimals = decimals === undefined ? 1 : decimals;
    var k = 1024;
    var sizes = ["B", "KB", "MB", "GB", "TB", "PB"];
    var i = Math.floor(Math.log(bytes) / Math.log(k));
    if (i < 0) i = 0;
    return parseFloat((bytes / Math.pow(k, i)).toFixed(decimals)) + " " + sizes[i];
}

function escapeHtml(str) {
    if (str === null || str === undefined) return "";
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function apiCall(action, data, cb) {
    data = data || {};
    data.action = action;
    $.ajax({
        type: "POST",
        url: API_URL,
        data: data,
        dataType: "json",
        success: function (res) {
            cb(res);
        },
        error: function (xhr) {
            cb({ success: false, message: "HTTP " + xhr.status });
        }
    });
}

function loadStats() {
    apiCall("stats", {}, function (res) {
        if (!res.success) return;
        var d = res.data;
        var c = d.counts || {};
        $("#stat_downloading").text(c.downloading || 0);
        $("#stat_queued").text(c.queued || 0);
        $("#stat_completed").text(c.completed || 0);
        $("#stat_error").text(c.error || 0);
        $("#stat_speed").text(formatBytes(d.active_speed || 0) + "/s");

        var s = d.settings || {};
        $("#max_concurrent").val(s.max_concurrent || 3);
        $("#max_retries").val(s.max_retries || 3);
        $("#downloads_enabled").prop("checked", (s.downloads_enabled || "0") == "1");
        $("#pause_all").prop("checked", (s.pause_all || "0") == "1");
    });
}

function loadDownloads() {
    currentStatus = $("#filter_status").val();
    currentSearch = $("#filter_search").val();
    apiCall("list", { status: currentStatus, search: currentSearch, limit: 200 }, function (res) {
        if (!res.success) return;
        var jobs = res.data || [];
        renderTable(jobs);
        loadStats();
    });
}

function renderTable(jobs) {
    var tbody = $("#downloads_tbody");
    if (jobs.length == 0) {
        tbody.html('<tr><td colspan="8" class="empty">لا توجد تحميلات</td></tr>');
        return;
    }

    var html = "";
    for (var i = 0; i < jobs.length; i++) {
        var j = jobs[i];
        var status = j.status || "queued";
        var statusName = STATUS_NAMES[status] || status;
        var platform = PLATFORM_NAMES[j.platform] || j.platform || "—";
        var progress = Math.min(100, Math.max(0, parseFloat(j.progress) || 0));
        var sizeText = (j.total_size > 0) ? formatBytes(j.total_size) : "—";
        var downloadedText = (j.downloaded_size > 0) ? formatBytes(j.downloaded_size) : "0 B";
        var ext = j.file_ext ? ("." + escapeHtml(j.file_ext)) : "";
        var time = j.created_at ? j.created_at.replace("T", " ").substring(0, 16) : "—";

        var actions = "";
        if (status == "paused" || status == "queued" || status == "error" || status == "cancelled") {
            actions += '<button class="action-btn resume" title="استئناف" onclick="resumeJob(' + j.id + ')"><i class="fas fa-play"></i></button>';
        }
        if (status == "downloading" || status == "queued" || status == "paused") {
            actions += '<button class="action-btn cancel" title="إلغاء" onclick="cancelJob(' + j.id + ')"><i class="fas fa-ban"></i></button>';
        }
        actions += '<button class="action-btn delete" title="حذف" onclick="deleteJob(' + j.id + ')"><i class="fas fa-trash"></i></button>';

        html +=
            '<tr>' +
            '<td class="file-cell"><span class="file-title" title="' + escapeHtml(j.file_title) + '">' + escapeHtml(j.file_title) + '</span><span class="file-ext">' + ext + '</span></td>' +
            '<td><span title="' + formatBytes(j.downloaded_size) + ' من ' + sizeText + '">' + sizeText + '</span></td>' +
            '<td><div class="progress-wrap">' +
                '<div class="progress-bar"><span style="width:' + progress + '%"></span></div>' +
                '<span class="progress-pct">' + progress.toFixed(1) + '% (' + downloadedText + ')</span>' +
            '</div></td>' +
            '<td class="speed">' + formatBytes(j.speed) + '/s</td>' +
            '<td class="device-cell" title="' + escapeHtml(j.device_id) + '">' + escapeHtml(j.device_id) + '<br>' + platform + '</td>' +
            '<td><span class="badge ' + status + '">' + statusName + '</span></td>' +
            '<td class="time-cell">' + time + '</td>' +
            '<td><div class="actions">' + actions + '</div></td>' +
            '</tr>';
    }
    tbody.html(html);
}

function saveSettings() {
    $("#settings_status").html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...').css("color", "#5a6b7f");
    apiCall("settings_save", {
        max_concurrent: $("#max_concurrent").val(),
        max_retries: $("#max_retries").val(),
        downloads_enabled: $("#downloads_enabled").is(":checked") ? "1" : "0",
        pause_all: $("#pause_all").is(":checked") ? "1" : "0"
    }, function (res) {
        if (res.success) {
            $("#settings_status").html('<i class="fas fa-check"></i> تم الحفظ').css("color", "#27ae60");
        } else {
            $("#settings_status").html('<i class="fas fa-times"></i> ' + (res.message || "خطأ")).css("color", "#e74c3c");
        }
        setTimeout(function () { $("#settings_status").html(""); }, 3000);
        loadStats();
    });
}

function addJob() {
    var title = $("#add_title").val().trim();
    var link = $("#add_link").val().trim();
    var ext = $("#add_ext").val().trim();
    var headers = $("#add_headers").val().trim() || "{}";
    var device = $("#add_device").val().trim();

    if (!title || !link) {
        $("#add_status").html("أدخل اسم الملف والرابط").css("color", "#e74c3c");
        return;
    }
    try { JSON.parse(headers); } catch (e) { headers = "{}"; }

    $("#add_status").html('<i class="fas fa-spinner fa-spin"></i> جاري الإضافة...').css("color", "#5a6b7f");
    apiCall("add", {
        file_title: title,
        file_link: link,
        file_ext: ext,
        custom_headers: headers,
        device_id: device
    }, function (res) {
        if (res.success) {
            $("#add_status").html('<i class="fas fa-check"></i> تمت الإضافة').css("color", "#27ae60");
            $("#add_title").val("");
            $("#add_link").val("");
            $("#add_ext").val("");
            $("#add_headers").val("");
            $("#add_device").val("");
            loadDownloads();
        } else {
            $("#add_status").html('<i class="fas fa-times"></i> ' + (res.message || "خطأ")).css("color", "#e74c3c");
        }
        setTimeout(function () { $("#add_status").html(""); }, 3000);
    });
}

function cancelJob(id) {
    if (!confirm("هل أنت متأكد من إلغاء هذا التحميل؟")) return;
    apiCall("cancel", { job_id: id }, function (res) {
        loadDownloads();
    });
}

function resumeJob(id) {
    apiCall("resume_admin", { job_id: id }, function (res) {
        loadDownloads();
    });
}

function deleteJob(id) {
    if (!confirm("حذف هذا التحميل نهائيًا من القائمة؟")) return;
    apiCall("delete", { job_id: id }, function (res) {
        loadDownloads();
    });
}

function refreshNow() {
    loadDownloads();
}

function debouncedLoad() {
    clearTimeout(window.__searchTimer);
    window.__searchTimer = setTimeout(loadDownloads, 500);
}

$(document).ready(function () {
    $(".stat-card").on("click", function () {
        var status = $(this).attr("data-status");
        if (!status) return;
        $("#filter_status").val(status);
        loadDownloads();
    });

    loadDownloads();
    timer = setInterval(loadDownloads, REFRESH_INTERVAL);

    document.addEventListener("visibilitychange", function () {
        if (document.hidden) {
            clearInterval(timer);
        } else if (!timer) {
            timer = setInterval(loadDownloads, REFRESH_INTERVAL);
            loadDownloads();
        }
    });
});
