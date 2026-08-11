// ============================================================
// ElbatalTV Downloads Manager
// محرك تحميل داخلي يتكامل مع لوحة إدارة التحميلات
// users/downloads/api.php — بدون تطبيقات خارجية (ADM/1DM)
// ============================================================
var ElDownloads = (function () {
    var API_URL = (typeof elbatal_api !== "undefined" ? elbatal_api : "https://new.elbatal-app.com/users/") + "downloads/api.php";
    var running = {};
    var statusCallback = null;

    function randString(len) {
        var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        var out = "";
        for (var i = 0; i < len; i++) out += chars.charAt(Math.floor(Math.random() * chars.length));
        return out;
    }

    function safeFixSingleQuotes(str) {
        str = str || "";
        if (typeof fixSingleQuotes === "function") {
            try { return fixSingleQuotes(str); } catch (e) { }
        }
        return str.replace(/([{,])\s*'([^']+)'\s*:/g, '$1"$2":')
            .replace(/:\s*'([^']*)'/g, ':"$1"');
    }

    function getIdentity() {
        var identity = { device_id: null, user_id: null };
        if (typeof what_window !== "undefined" && what_window.dev_id) {
            identity.device_id = what_window.dev_id;
        }
        if (typeof user_data !== "undefined" && user_data.user_id) {
            identity.user_id = user_data.user_id;
        }
        if (!identity.device_id) {
            try {
                identity.device_id = localStorage.getItem("el_downloads_dev_id");
                if (!identity.device_id) {
                    identity.device_id = "dev-" + randString(8) + "-" + Date.now().toString(36);
                    localStorage.setItem("el_downloads_dev_id", identity.device_id);
                }
            } catch (e) {
                identity.device_id = "dev-" + randString(10);
            }
        }
        return identity;
    }

    function getPlatform() {
        if (typeof mouscripts !== "undefined") return "android";
        if (typeof what_window !== "undefined" && what_window.electron) return "electron";
        return "web";
    }

    function post(data) {
        return new Promise(function (resolve, reject) {
            var params = {
                type: "POST",
                url: API_URL,
                data: data,
                dataType: "json",
                success: function (res) {
                    if (typeof res === "string") {
                        try { res = JSON.parse(res); } catch (e) { reject(new Error("bad response")); return; }
                    }
                    resolve(res);
                },
                fail: function (code, msg) {
                    reject(new Error(msg || "network error (" + code + ")"));
                }
            };

            if (typeof $.MouAjax === "function") {
                try {
                    $.MouAjax(params);
                    return;
                } catch (e) { }
            }
            params.error = function (xhr) { reject(new Error("HTTP " + xhr.status)); };
            delete params.fail;
            $.ajax(params);
        });
    }

    function emit(status, job) {
        if (statusCallback && typeof statusCallback === "function") {
            try { statusCallback(status, job); } catch (e) { }
        }
    }

    function waitForSlot(job) {
        return new Promise(function (resolve, reject) {
            var attempts = 0;
            var loop = setInterval(function () {
                post({ action: "poll", job_token: job.job_token, device_id: getIdentity().device_id })
                    .then(function (res) {
                        if (!res.success) {
                            clearInterval(loop);
                            reject(new Error(res.message || "poll error"));
                            return;
                        }
                        var d = res.data;
                        if (d.go === true) {
                            clearInterval(loop);
                            job.status = d.status;
                            emit("started", job);
                            resolve(job);
                        } else if (d.stop === true) {
                            clearInterval(loop);
                            emit("cancelled", job);
                            reject(new Error("stopped: " + d.reason));
                        }
                        // else wait and try again
                    })
                    .catch(function (err) {
                        attempts++;
                        if (attempts > 30) {
                            clearInterval(loop);
                            reject(err);
                        }
                    });
            }, 3000);
        });
    }

    function reportProgress(job, downloaded, speed, total) {
        post({
            action: "progress",
            job_token: job.job_token,
            device_id: getIdentity().device_id,
            downloaded_size: Math.round(downloaded),
            total_size: Math.round(total || 0),
            speed: Math.round(speed || 0)
        }).then(function (res) {
            if (res.success && res.data && res.data.stop === true) {
                job._stop = true;
                emit("cancelled", job);
            }
        }).catch(function () { });
    }

    function streamDownload(job, file) {
        var headers = {};
        try { headers = JSON.parse(safeFixSingleQuotes(file.custom_headers || "{}")); } catch (e) { }
        headers["MOuCustomREQUEST"] = "NICE";

        fetch(file.file_link, { method: "GET", headers: headers })
            .then(function (response) {
                if (!response.ok) throw new Error("HTTP " + response.status);
                var total = parseInt(response.headers.get("Content-Length") || "0", 10) || 0;
                if (!response.body) throw new Error("stream not supported");

                if (total > 0) reportProgress(job, 0, 0, total);

                var reader = response.body.getReader();
                var chunks = [];
                var received = 0;
                var lastReport = Date.now();
                var lastBytes = 0;

                function pump() {
                    if (job._stop) {
                        reader.cancel();
                        return;
                    }
                    return reader.read().then(function (r) {
                        if (r.done) {
                            var type = response.headers.get("Content-Type") || "application/octet-stream";
                            var blob = new Blob(chunks, { type: type });
                            var url = URL.createObjectURL(blob);
                            var a = document.createElement("a");
                            a.href = url;
                            a.download = (job.file_title || "download") + "." + (job.file_ext || "mp4");
                            document.body.appendChild(a);
                            a.click();
                            setTimeout(function () {
                                document.body.removeChild(a);
                                URL.revokeObjectURL(url);
                            }, 4000);
                            emit("completed", job);
                            ElDownloads.finish(job.job_token, received);
                            return;
                        }
                        chunks.push(r.value);
                        received += r.value.byteLength;
                        job.downloaded = received;
                        var now = Date.now();
                        if (now - lastReport >= 1000) {
                            var speed = (received - lastBytes) / ((now - lastReport) / 1000);
                            lastReport = now;
                            lastBytes = received;
                            reportProgress(job, received, speed, total);
                        }
                        return pump();
                    });
                }
                return pump();
            })
            .catch(function (err) {
                var message = err && err.message ? err.message : "download error";
                if (typeof mouscripts !== "undefined" && typeof mouscripts.download_file_now === "function") {
                    // fallback: native downloader الخاص بالتطبيق (ليس تطبيق خارجي)
                    try {
                        mouscripts.download_file_now(
                            file.file_link,
                            file.file_dir || "",
                            (job.file_title || "download") + "." + (job.file_ext || "mp4"),
                            job.job_token,
                            true,
                            file.custom_headers || "{}"
                        );
                        emit("native", job);
                        return;
                    } catch (e) { }
                }
                emit("failed", job);
                ElDownloads.fail(job.job_token, message);
            });
    }

    function download(file) {
        var identity = getIdentity();
        var isElectron = typeof what_window !== "undefined" && what_window.electron;
        var ext = (file.file_ext || "").toLowerCase();

        return post({
            action: "start",
            file_link: file.file_link,
            file_title: file.file_name || file.file_title || "",
            file_ext: file.file_ext || "",
            custom_headers: file.custom_headers || "{}",
            platform: getPlatform(),
            device_id: identity.device_id,
            user_id: identity.user_id || ""
        }).then(function (res) {
            if (!res.success) throw new Error(res.message || "register error");
            var job = res.data;
            job.file_title = file.file_name || file.file_title || res.data.file_title || "download";
            job.file_ext = file.file_ext || res.data.file_ext || "mp4";
            job._stop = false;
            running[job.job_token] = job;
            emit("queued", job);

            return waitForSlot(job).then(function (readyJob) {
                if (ext === "m3u8" && isElectron) {
                    // HLS عبر محمل Electron الأصلي (مشروح النتيجة غير معروف من صفحة الويب)
                    what_window.ipcRenderer.send("download-m3u8", [file.file_link, readyJob.file_title + "." + (readyJob.file_ext || "mp4"), JSON.parse(safeFixSingleQuotes(file.custom_headers || "{}"))]);
                    readyJob.native = true;
                    emit("native", readyJob);
                    return readyJob;
                }
                streamDownload(readyJob, file);
                return readyJob;
            });
        });
    }

    function finish(job_token, downloaded_size) {
        return post({
            action: "complete",
            job_token: job_token,
            device_id: getIdentity().device_id,
            downloaded_size: downloaded_size || 0
        });
    }

    function fail(job_token, message) {
        return post({
            action: "fail",
            job_token: job_token,
            device_id: getIdentity().device_id,
            error_msg: message || "error"
        });
    }

    function pause(job_token) {
        return post({ action: "pause", job_token: job_token, device_id: getIdentity().device_id });
    }

    function resume(job_token) {
        return post({ action: "resume", job_token: job_token, device_id: getIdentity().device_id });
    }

    function setStatusCallback(cb) {
        statusCallback = cb;
    }

    function getRunning() {
        return running;
    }

    return {
        download: download,
        finish: finish,
        fail: fail,
        pause: pause,
        resume: resume,
        setStatusCallback: setStatusCallback,
        getRunning: getRunning
    };
})();
