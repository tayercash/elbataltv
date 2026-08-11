// ============================================================
// ElbatalTV Local Downloads Manager
// نظام تحميل لوكال بالكامل — بدون أي اتصال بالسيرفر
// - Electron: SQLite عبر الـ main process (electron/downloads-manager.js)
// - Android: bridge نيتف جافا (android/ElbatalDownloadsBridge.java) + SQLite
// - Web: مخزن محلي (in-memory + localStorage)
// ============================================================
var ElDownloads = (function () {
    var isElectron = typeof what_window !== "undefined" && typeof what_window.electron !== "undefined";
    var useIPC = isElectron && typeof what_window.ipcRenderer !== "undefined" && typeof what_window.ipcRenderer.invoke === "function";
    var useNativeAndroid = typeof elDownloadsNative !== "undefined";

    var running = {};
    var statusCallback = null;
    var LS_KEY = "el_downloads_local";
    var dlRefreshTimer = null;
    var dlLiveTimer = null;

    var STATUS_LABELS = {
        queued: "في الانتظار",
        downloading: "جاري التحميل",
        paused: "متوقف مؤقتا",
        completed: "اكتمل",
        error: "خطأ",
        cancelled: "ملغي",
        native: "مشغل داخليا"
    };

    function esc(str) {
        return String(str == null ? "" : str)
            .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
    }

    function fmtSize(b) {
        b = +b || 0;
        if (b >= 1073741824) return (b / 1073741824).toFixed(2) + " GB";
        if (b >= 1048576) return (b / 1048576).toFixed(2) + " MB";
        if (b >= 1024) return (b / 1024).toFixed(1) + " KB";
        return b + " B";
    }

    function safeFixSingleQuotes(str) {
        str = str || "";
        if (typeof fixSingleQuotes === "function") {
            try { return fixSingleQuotes(str); } catch (e) { }
        }
        return str.replace(/([{,])\s*'([^']+)'\s*:/g, '$1"$2":')
            .replace(/:\s*'([^']*)'/g, ':"$1"');
    }

    function randomToken() {
        return "dl-" + Date.now().toString(36) + "-" + Math.random().toString(36).slice(2, 10);
    }

    function emit(status, job) {
        if (statusCallback && typeof statusCallback === "function") {
            try { statusCallback(status, job); } catch (e) { }
        }
    }

    // ============================================================
    // الطبقة اللوكالية العامة (بديل السيرفر)
    // ============================================================

    function ipcInvoke(channel) {
        var args = Array.prototype.slice.call(arguments, 1);
        return new Promise(function (resolve, reject) {
            try {
                what_window.ipcRenderer.invoke.apply(what_window.ipcRenderer, [channel].concat(args)).then(resolve, function (err) {
                    reject(new Error((err && err.message) || "ipc error"));
                });
            } catch (e) { reject(e); }
        });
    }

    // ---------- bridge أندرويد النيتف (elDownloadsNative) ----------

    function nativeCallAsync(method, callbackName) {
        // للدوال اللي بتاخد اسم callback واحد (getDownloads / getSettings)
        return new Promise(function (resolve) {
            var done = false;
            window[callbackName] = function (res) {
                if (done) return;
                done = true;
                clearTimeout(timer);
                resolve(res);
            };
            var timer = setTimeout(function () {
                if (done) return;
                done = true;
                resolve(null);
            }, 5000);
            try {
                elDownloadsNative[method](callbackName);
            } catch (e) {
                done = true;
                clearTimeout(timer);
                resolve(null);
            }
        });
    }

    function nativeFire(method, arg) {
        try { elDownloadsNative[method](arg); } catch (e) { }
    }

    function normalizeJob(job) {
        if (!job) return null;
        return {
            job_token: job.job_token,
            file_title: job.file_title || "download",
            file_ext: job.file_ext || "",
            file_link: job.file_link || "",
            file_dir: job.file_dir || "",
            custom_headers: job.custom_headers || "{}",
            status: job.status || "queued",
            total_size: parseInt(job.total_size, 10) || 0,
            downloaded_size: parseInt(job.downloaded_size, 10) || 0,
            speed: parseFloat(job.speed) || 0,
            progress: parseFloat(job.progress) || 0,
            error_msg: job.error_msg || "",
            file_path: job.file_path || "",
            created_at: job.created_at || "",
            started_at: job.started_at || "",
            completed_at: job.completed_at || ""
        };
    }

    function onNativeProgress(job) {
        var j = normalizeJob(job);
        if (!j) return;
        running[j.job_token] = j;
        persistLocal(j);
        emit("progress", j);
        if (typeof $ !== "undefined" && $("#downloads").hasClass("show")) {
            refresh();
        }
    }
    window.elDownloadsNativeOnProgress = onNativeProgress;

    function getLocalHistory() {
        try {
            var h = JSON.parse(localStorage.getItem(LS_KEY) || "[]");
            return Array.isArray(h) ? h : [];
        } catch (e) { return []; }
    }

    function saveLocalHistory(history) {
        try { localStorage.setItem(LS_KEY, JSON.stringify(history.slice(0, 100))); } catch (e) { }
    }

    function localList() {
        var out = [];
        var seen = {};
        getLocalHistory().forEach(function (j) { seen[j.job_token] = true; out.push(j); });
        Object.keys(running).forEach(function (t) {
            var idx = -1;
            for (var i = 0; i < out.length; i++) { if (out[i].job_token === t) { idx = i; break; } }
            if (idx > -1) out[idx] = running[t];
            else if (!seen[t]) out.push(running[t]);
        });
        return out;
    }

    function persistLocal(job) {
        var h = getLocalHistory();
        var idx = -1;
        for (var i = 0; i < h.length; i++) { if (h[i].job_token === job.job_token) { idx = i; break; } }
        if (idx > -1) h[idx] = job; else h.unshift(job);
        saveLocalHistory(h);
    }

    // ---------- Electron (SQLite عبر الـ main process) ----------

    function electronDownload(file) {
        return ipcInvoke("downloads:add", {
            file_title: file.file_name || file.file_title || "",
            file_ext: file.file_ext || "",
            file_link: file.file_link || "",
            file_dir: file.file_dir || "",
            custom_headers: typeof file.custom_headers === "string" ? file.custom_headers : JSON.stringify(file.custom_headers || {})
        }).then(function (res) {
            if (res && res.error) throw new Error(res.error);
            var job = res || {};
            if (job && job.job_token) running[job.job_token] = job;
            emit("queued", job);
            return job;
        });
    }

    // ---------- أندرويد النيتف (تحميل بالهيدرز الكاملة عبر الجافا) ----------

    function androidNativeDownload(file) {
        var job = {
            job_token: randomToken(),
            file_title: file.file_name || file.file_title || "download",
            file_ext: file.file_ext || "mp4",
            file_link: file.file_link,
            file_dir: file.file_dir || "",
            custom_headers: file.custom_headers || "{}",
            status: "queued",
            total_size: 0,
            downloaded_size: 0,
            speed: 0,
            progress: 0,
            error_msg: "",
            created_at: new Date().toISOString()
        };
        running[job.job_token] = job;
        persistLocal(job);
        emit("queued", job);
        try {
            elDownloadsNative.startDownload(JSON.stringify({
                job_token: job.job_token,
                file_link: job.file_link,
                file_title: job.file_title,
                file_ext: job.file_ext,
                file_dir: job.file_dir,
                custom_headers: job.custom_headers
            }));
        } catch (e) {
            job.status = "error";
            job.error_msg = "native bridge error";
            emit("failed", job);
            persistLocal(job);
        }
        return Promise.resolve(job);
    }

    // ---------- تحميل محلي بالـ fetch (Android/Web) ----------

    function localDownload(file) {
        var job = {
            job_token: randomToken(),
            file_title: file.file_name || file.file_title || "download",
            file_ext: file.file_ext || "mp4",
            file_link: file.file_link,
            file_dir: file.file_dir || "",
            custom_headers: file.custom_headers || "{}",
            status: "queued",
            total_size: 0,
            downloaded_size: 0,
            speed: 0,
            progress: 0,
            error_msg: "",
            created_at: new Date().toISOString()
        };
        running[job.job_token] = job;
        persistLocal(job);
        emit("queued", job);
        setTimeout(function () { streamLocal(job, file); }, 50);
        return Promise.resolve(job);
    }

    function streamLocal(job, file) {
        job.status = "downloading";
        var headers = {};
        try { headers = JSON.parse(safeFixSingleQuotes(file.custom_headers || "{}")); } catch (e) { }
        headers["MOuCustomREQUEST"] = "NICE";

        fetch(file.file_link, { method: "GET", headers: headers })
            .then(function (response) {
                if (!response.ok) throw new Error("HTTP " + response.status);
                var total = parseInt(response.headers.get("Content-Length") || "0", 10) || 0;
                job.total_size = total;
                if (!response.body) throw new Error("stream not supported");

                var reader = response.body.getReader();
                var chunks = [];
                var received = 0;
                var lastReport = Date.now();
                var lastBytes = 0;

                function pump() {
                    if (job._stop) { reader.cancel(); return; }
                    return reader.read().then(function (r) {
                        if (r.done) {
                            var type = response.headers.get("Content-Type") || "application/octet-stream";
                            var blob = new Blob(chunks, { type: type });
                            var url = URL.createObjectURL(blob);
                            var a = document.createElement("a");
                            a.href = url;
                            a.download = job.file_title + "." + job.file_ext;
                            document.body.appendChild(a);
                            a.click();
                            setTimeout(function () {
                                document.body.removeChild(a);
                                URL.revokeObjectURL(url);
                            }, 4000);
                            job.status = "completed";
                            job.progress = 100;
                            job.speed = 0;
                            job.downloaded_size = received;
                            emit("completed", job);
                            persistLocal(job);
                            return;
                        }
                        chunks.push(r.value);
                        received += r.value.byteLength;
                        job.downloaded_size = received;
                        job.progress = total > 0 ? Math.min(100, Math.round((received / total) * 100)) : 0;
                        var now = Date.now();
                        if (now - lastReport >= 1000) {
                            job.speed = (received - lastBytes) / ((now - lastReport) / 1000);
                            lastReport = now;
                            lastBytes = received;
                            persistLocal(job);
                        }
                        return pump();
                    });
                }
                return pump();
            })
            .catch(function (err) {
                var message = (err && err.message) || "download error";
                if (typeof mouscripts !== "undefined" && typeof mouscripts.download_file_now === "function") {
                    try {
                        mouscripts.download_file_now(
                            file.file_link,
                            file.file_dir || "",
                            job.file_title + "." + job.file_ext,
                            job.job_token,
                            true,
                            file.custom_headers || "{}"
                        );
                        job.status = "native";
                        emit("native", job);
                        persistLocal(job);
                        return;
                    } catch (e) { }
                }
                job.status = "error";
                job.error_msg = message;
                job.speed = 0;
                emit("failed", job);
                persistLocal(job);
            });
    }

    // ============================================================
    // الواجهة العامة
    // ============================================================

    function download(file) {
        if (useIPC) {
            var ext = (file.file_ext || "").toLowerCase();
            if (ext === "m3u8") {
                var h = {};
                try { h = JSON.parse(safeFixSingleQuotes(file.custom_headers || "{}")); } catch (e) { }
                what_window.ipcRenderer.send("download-m3u8", [file.file_link, (file.file_name || "download") + ".mp4", h]);
                var j = { job_token: randomToken(), file_title: file.file_name || "download", file_ext: "mp4", status: "native" };
                running[j.job_token] = j;
                emit("native", j);
                return Promise.resolve(j);
            }
            return electronDownload(file);
        }
        if (useNativeAndroid) return androidNativeDownload(file);
        return localDownload(file);
    }

    function list() {
        if (useIPC) return ipcInvoke("downloads:list", {});
        if (useNativeAndroid) return nativeCallAsync("getDownloads", "elDownloadsNativeOnList");
        return Promise.resolve(localList());
    }

    function pause(job_token) {
        if (useIPC) return ipcInvoke("downloads:pause", job_token).catch(function () { });
        if (useNativeAndroid) { nativeFire("pauseDownload", job_token); return Promise.resolve(); }
        var job = running[job_token];
        if (job && job.status === "downloading") {
            job._stop = true;
            job.status = "paused";
            job.speed = 0;
            emit("paused", job);
        }
        return Promise.resolve();
    }

    function resume(job_token) {
        if (useIPC) return ipcInvoke("downloads:resume", job_token).catch(function () { });
        if (useNativeAndroid) { nativeFire("resumeDownload", job_token); return Promise.resolve(); }
        var job = running[job_token];
        if (job) {
            job._stop = false;
            job.status = "queued";
            emit("queued", job);
        }
        return Promise.resolve();
    }

    function cancelSelf(job_token) {
        if (useIPC) return ipcInvoke("downloads:cancel", job_token).then(function () { refresh(); }).catch(function () { });
        if (useNativeAndroid) { nativeFire("cancelDownload", job_token); refresh(); return Promise.resolve(); }
        var job = running[job_token];
        if (job) {
            job._stop = true;
            job.status = "cancelled";
            job.speed = 0;
            emit("cancelled", job);
        }
        refresh();
        return Promise.resolve();
    }

    function deleteJob(job_token) {
        if (useIPC) return ipcInvoke("downloads:delete", job_token).then(function () { refresh(); }).catch(function () { });
        if (useNativeAndroid) { nativeFire("deleteDownload", job_token); refresh(); return Promise.resolve(); }
        var job = running[job_token];
        if (job) { job._stop = true; delete running[job_token]; }
        var h = getLocalHistory();
        saveLocalHistory(h.filter(function (x) { return x.job_token !== job_token; }));
        refresh();
        return Promise.resolve();
    }

    function finish() { return Promise.resolve(); }
    function fail() { return Promise.resolve(); }

    function setStatusCallback(cb) { statusCallback = cb; }

    function getRunning() { return running; }

    function getSettings() {
        if (useIPC) return ipcInvoke("downloads:settings", "get", {});
        if (useNativeAndroid) {
            return nativeCallAsync("getSettings", "elDownloadsNativeOnSettings").then(function (s) {
                return s || { max_concurrent: "1", downloads_enabled: "1", pause_all: "0", max_retries: "3" };
            });
        }
        return Promise.resolve({ max_concurrent: "1", downloads_enabled: "1", pause_all: "0", max_retries: "3" });
    }

    function setSettings(data) {
        if (useIPC) return ipcInvoke("downloads:settings", "set", data || {}).catch(function () { });
        if (useNativeAndroid) { nativeFire("setSettings", JSON.stringify(data || {})); return Promise.resolve(data || {}); }
        return Promise.resolve({ max_concurrent: "1", downloads_enabled: "1", pause_all: "0", max_retries: "3" });
    }

    // ============================================================
    // صفحة التحميلات داخل التطبيق (نظام الواجهة العادي)
    // ============================================================

    function downloadItemHtml(job) {
        var total = parseInt(job.total_size, 10) || 0;
        var down = parseInt(job.downloaded_size, 10) || 0;
        var live = running[job.job_token];
        if (live) {
            down = parseInt(live.downloaded_size, 10) || down;
            if (!total && live.total_size) total = parseInt(live.total_size, 10) || 0;
        }
        var status = job.status || "queued";
        if (live) status = live.status || status;
        var pct = total > 0 ? Math.min(100, Math.round((down / total) * 100)) : (status === "completed" ? 100 : 0);
        var label = STATUS_LABELS[status] || status;
        var speedTxt = "";
        if (status === "downloading") {
            var sp = parseFloat(job.speed) || 0;
            if (live && live.speed) sp = live.speed;
            if (sp > 0) speedTxt = fmtSize(sp) + "/s";
        }
        var actions = "";
        if (status === "completed" && job.file_path) {
            actions += '<button class="dl_btn dl_btn_play" data-dl="play" data-token="' + job.job_token + '"><i class="fas fa-play"></i> تشغيل</button>';
        }
        var sizeTxt = total > 0 ? fmtSize(down) + " / " + fmtSize(total) : fmtSize(down);
        return '<div class="download_item" data-token="' + job.job_token + '">'
            + '<div class="download_item_top">'
            + '<span class="download_item_title">' + esc(job.file_title || "download") + '</span>'
            + '<div class="download_item_top_actions">'
            + '<span class="download_item_status st_' + status + '">' + label + '</span>'
            + '<button class="dl_more_btn" data-dl="menu" data-token="' + job.job_token + '"><i class="fas fa-ellipsis-v"></i></button>'
            + '</div>'
            + '</div>'
            + '<div class="download_item_bar"><div class="download_item_bar_fill' + (status === "completed" ? " st_done" : "") + '" style="width:' + pct + '%"></div></div>'
            + '<div class="download_item_info">'
            + '<span class="download_item_sizes">' + sizeTxt + '</span>'
            + (speedTxt ? '<span class="download_item_speed">' + speedTxt + '</span>' : '')
            + '</div>'
            + (actions ? '<div class="download_item_actions">' + actions + '</div>' : '')
            + '</div>';
    }

    // ============================================================
    // قائمة الإدارة (زرار 3 نقاط) + بوب أب الحذف + التشغيل
    // ============================================================

    var dlMenuToken = null;

    function openDownloadMenu(token) {
        dlMenuToken = token;
        var job = running[token];
        if (!job || typeof $ === "undefined" || !$("#dl_menu_popup").length) return;
        var status = job.status || "queued";
        var html = "";
        if (status === "queued" || status === "downloading") {
            html += '<button class="dl_menu_btn" data-dl-menu="pause"><i class="fas fa-pause"></i> إيقاف مؤقت</button>';
        }
        if (status === "paused") {
            html += '<button class="dl_menu_btn" data-dl-menu="resume"><i class="fas fa-play"></i> استئناف</button>';
        }
        if (status === "completed" && job.file_path) {
            html += '<button class="dl_menu_btn" data-dl-menu="play"><i class="fas fa-play"></i> تشغيل الملف</button>';
        }
        html += '<button class="dl_menu_btn dl_menu_danger" data-dl-menu="delete"><i class="fas fa-trash"></i> حذف العملية</button>';
        $("#dl_menu_list").html(html);
        $("#dl_menu_popup").openpopup();
    }

    function dlMenuAction(act) {
        if (!dlMenuToken) return;
        var token = dlMenuToken;
        if (typeof $ !== "undefined" && $("#dl_menu_popup").length) {
            $("#dl_menu_popup").closepopup();
        }
        if (act === "pause") {
            pause(token).then(function () { refresh(); });
        } else if (act === "resume") {
            resume(token).then(function () { refresh(); });
        } else if (act === "play") {
            playFile(token);
        } else if (act === "delete") {
            if (typeof $ !== "undefined" && $("#dl_delete_popup").length) {
                $("#dl_delete_popup").openpopup();
            }
        }
    }

    function openDeletePopup() {
        if (typeof $ !== "undefined" && $("#dl_delete_popup").length) {
            $("#dl_delete_popup").openpopup();
        }
    }

    function dlDeleteAction(mode) {
        var token = dlMenuToken;
        dlMenuToken = null;
        if (typeof $ !== "undefined" && $("#dl_delete_popup").length) {
            $("#dl_delete_popup").closepopup();
        }
        if (!token) return;
        if (mode === "file") {
            deleteJob(token);
        } else if (mode === "record") {
            deleteRecord(token);
        }
    }

    function deleteRecord(job_token) {
        if (useIPC) return ipcInvoke("downloads:delete-record", job_token).then(function () { refresh(); }).catch(function () { });
        if (useNativeAndroid) { nativeFire("deleteRecordDownload", job_token); refresh(); return Promise.resolve(); }
        var job = running[job_token];
        if (job) { job._stop = true; delete running[job_token]; }
        var h = getLocalHistory();
        saveLocalHistory(h.filter(function (x) { return x.job_token !== job_token; }));
        refresh();
        return Promise.resolve();
    }

    function playFile(token) {
        var job = running[token];
        if (!job || !job.file_path) {
            if (typeof showToast === "function") { try { showToast("الملف غير متاح للتشغيل"); } catch (e) { } }
            return;
        }
        var src = "file://" + String(job.file_path).replace(/\\/g, "/");
        var title = job.file_title || "download";
        try {
            if (typeof play_vid === "function") {
                play_vid(src, title, "", "{}");
            } else if (typeof mouscripts !== "undefined" && typeof mouscripts.play_vid === "function") {
                mouscripts.play_vid(src, title, "", "{}");
            }
        } catch (e) { }
    }

    function runningList() {
        var out = [];
        Object.keys(running).forEach(function (t) { out.push(running[t]); });
        return out;
    }

    var lastListSig = null;

    function listSignature(jobs) {
        return (jobs || []).map(function (j) {
            return j.job_token + "|" + (j.status || "") + "|" + (parseInt(j.downloaded_size, 10) || 0) + "|" + (parseInt(j.total_size, 10) || 0) + "|" + Math.round(parseFloat(j.speed) || 0) + "|" + Math.round(parseFloat(j.progress) || 0);
        }).join(",");
    }

    function render(jobs) {
        var $list = $("#downloads_list");
        if (!$list.length) return;
        if (!jobs || !jobs.length) {
            lastListSig = null;
            $list.html('<div class="empty_downloads">لا توجد تحميلات</div>');
            return;
        }
        jobs.forEach(function (job) {
            if (job && job.job_token && !running[job.job_token]) {
                running[job.job_token] = job;
            }
        });
        var sig = listSignature(jobs);
        if (sig === lastListSig) return;
        lastListSig = sig;
        var html = "";
        jobs.forEach(function (job) { html += downloadItemHtml(job); });
        $list.html(html);
    }

    function refresh() {
        return list().then(function (jobs) {
            render(jobs);
        }).catch(function () {
            render(runningList());
        });
    }

    function liveTick() {
        if (typeof $ === "undefined" || !$("#downloads").hasClass("show")) return;
        Object.keys(running).forEach(function (token) {
            var job = running[token];
            if (!job || job.status !== "downloading") return;
            var $item = $('#downloads_list .download_item[data-token="' + token + '"]');
            if (!$item.length) return;
            var total = parseInt(job.total_size, 10) || 0;
            var down = parseInt(job.downloaded_size, 10) || 0;
            var pct = total > 0 ? Math.min(100, Math.round((down / total) * 100)) : 0;
            $item.find(".download_item_bar_fill").css("width", pct + "%");
            $item.find(".download_item_sizes").text(fmtSize(down) + (total > 0 ? " / " + fmtSize(total) : ""));
            $item.find(".download_item_speed").text(job.speed > 0 ? fmtSize(job.speed) + "/s" : "");
        });
    }

    function stopTimers() {
        if (dlRefreshTimer) { clearInterval(dlRefreshTimer); dlRefreshTimer = null; }
        if (dlLiveTimer) { clearInterval(dlLiveTimer); dlLiveTimer = null; }
    }

    function show() {
        if (typeof $ === "undefined" || !$("#downloads").length) return;
        if (typeof $(".sidenav").hide_side_nav === "function") {
            try { $(".sidenav").hide_side_nav(); } catch (e) { }
        }
        refresh();
        try {
            $("#downloads").openpopup();
            $("#downloads").on_closepopup(stopTimers);
        } catch (e) { }
        if (!dlRefreshTimer) dlRefreshTimer = setInterval(refresh, 3000);
        if (!dlLiveTimer) dlLiveTimer = setInterval(liveTick, 1000);
    }

    // أحداث التقدم من الـ main process (Electron/SQLite)
    if (useIPC) {
        try {
            what_window.ipcRenderer.on("downloads:progress", function (event, job) {
                if (!job || !job.job_token) return;
                running[job.job_token] = job;
                emit("progress", job);
                if (typeof $ !== "undefined" && $("#downloads").hasClass("show")) {
                    refresh();
                }
            });
        } catch (e) { }
    }

    // ربط الأزرار (تحديث / قائمة إدارة / تشغيل / بوب أب الحذف)
    if (typeof $ !== "undefined" && typeof document !== "undefined") {
        $(document).on("click", "#downloads_refresh_btn", function () { refresh(); });
        $(document).on("click", ".download_item [data-dl='menu']", function () {
            openDownloadMenu($(this).attr("data-token"));
        });
        $(document).on("click", ".download_item [data-dl='play']", function () {
            playFile($(this).attr("data-token"));
        });
        $(document).on("click", "#dl_menu_list .dl_menu_btn", function () {
            dlMenuAction($(this).attr("data-dl-menu"));
        });
        $(document).on("click", "#dl_delete_popup [data-dl-del]", function () {
            dlDeleteAction($(this).attr("data-dl-del"));
        });
    }

    return {
        download: download,
        finish: finish,
        fail: fail,
        pause: pause,
        resume: resume,
        cancelSelf: cancelSelf,
        deleteJob: deleteJob,
        deleteRecord: deleteRecord,
        playFile: playFile,
        setStatusCallback: setStatusCallback,
        getRunning: getRunning,
        getSettings: getSettings,
        setSettings: setSettings,
        list: list,
        show: show,
        refresh: refresh
    };
})();
