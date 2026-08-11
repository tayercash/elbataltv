<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(204);
    exit();
}

include_once __DIR__ . '/../config_db.php';

function api_response($success, $data = null, $message = "") {
    $res = array(
        "success" => (bool) $success,
        "message" => $message,
        "data" => $data
    );
    echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

function mou_custom_decode($txt, $num = 1) {
    $default = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    $custom = "ZYXWVUTSRQPONMLKJIHGFEDCBAzyxwvutsrqponmlkjihgfedcba9876543210+/";
    $decoded = $txt;
    for ($i = 1; $i <= $num; $i++) {
        $decoded = base64_decode(strtr($decoded, $custom, $default));
    }
    return urldecode(htmlspecialchars($decoded));
}

function get_device_info() {
    $user_id = null;
    $device_id = null;

    if (!empty($_POST["token"])) {
        $decoded = mou_custom_decode($_POST["token"]);
        $parts = explode("#", $decoded);
        if (count($parts) >= 2) {
            $user_id = intval($parts[0]);
            $device_id = $parts[1];
        }
    }
    if ($device_id === null && !empty($_POST["device_id"])) {
        $device_id = $_POST["device_id"];
    }
    if ($user_id === null && !empty($_POST["user_id"])) {
        $user_id = intval($_POST["user_id"]);
    }
    if ($device_id === null) {
        $device_id = "web-" . md5($_SERVER["REMOTE_ADDR"]);
    }

    return array($user_id, $device_id);
}

function get_settings($conn, $settings_table) {
    $defaults = array(
        "max_concurrent" => "3",
        "downloads_enabled" => "1",
        "pause_all" => "0",
        "max_retries" => "3"
    );
    $res = $conn->query("SELECT settings_key, settings_value FROM `$settings_table`");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $defaults[$row["settings_key"]] = $row["settings_value"];
        }
    }
    return $defaults;
}

function save_settings($conn, $settings_table, $data) {
    $allowed = array("max_concurrent", "downloads_enabled", "pause_all", "max_retries");
    foreach ($allowed as $key) {
        if (isset($data[$key])) {
            $value = $conn->real_escape_string((string) $data[$key]);
            $stmt = $conn->prepare("INSERT INTO `$settings_table` (`settings_key`, `settings_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `settings_value` = VALUES(`settings_value`)");
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }
    }
}

function require_admin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION["can_join"]) || $_SESSION["can_join"] !== true) {
        api_response(false, null, "unauthorized");
    }
}

function get_job_by_token($conn, $downloads_table, $job_token) {
    $stmt = $conn->prepare("SELECT * FROM `$downloads_table` WHERE job_token = ? LIMIT 1");
    $stmt->bind_param("s", $job_token);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->num_rows > 0 ? $res->fetch_assoc() : null;
}

function assert_job_owner($job) {
    if (empty($job)) {
        api_response(false, null, "job not found");
    }
    list($user_id, $device_id) = get_device_info();
    if (!empty($job["device_id"]) && $job["device_id"] !== $device_id) {
        api_response(false, null, "unauthorized");
    }
}

function recover_stale_jobs($conn, $downloads_table) {
    $conn->query("UPDATE `$downloads_table` SET status = 'queued', started_at = NULL
                  WHERE status = 'downloading' AND (updated_at IS NULL OR updated_at < NOW() - INTERVAL 120 SECOND)");
}

function count_active_downloads($conn, $downloads_table, $except_id = 0) {
    recover_stale_jobs($conn, $downloads_table);
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM `$downloads_table`
                            WHERE status = 'downloading' AND updated_at >= NOW() - INTERVAL 120 SECOND AND id <> ?");
    $stmt->bind_param("i", $except_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return intval($row["total"]);
}

$action = isset($_POST["action"]) ? trim($_POST["action"]) : "";

switch ($action) {

    // ============ DEVICE ACTIONS ============

    case "start":
        $file_link = isset($_POST["file_link"]) ? trim($_POST["file_link"]) : "";
        $file_title = isset($_POST["file_title"]) ? trim($_POST["file_title"]) : "";
        $file_ext = isset($_POST["file_ext"]) ? trim($_POST["file_ext"]) : "";
        $custom_headers = isset($_POST["custom_headers"]) ? $_POST["custom_headers"] : "{}";
        $platform = isset($_POST["platform"]) ? trim($_POST["platform"]) : "web";

        if ($file_link == "") {
            api_response(false, null, "file_link is required");
        }
        if ($file_title == "") {
            $file_title = basename(parse_url($file_link, PHP_URL_PATH));
        }

        list($user_id, $device_id) = get_device_info();

        $stmt = $conn->prepare("SELECT * FROM `$downloads_table`
                                WHERE file_link = ? AND device_id = ? AND status IN ('queued','downloading','paused') LIMIT 1");
        $stmt->bind_param("ss", $file_link, $device_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $existing = $res->fetch_assoc();
            api_response(true, array(
                "job_id" => intval($existing["id"]),
                "job_token" => $existing["job_token"],
                "status" => $existing["status"]
            ), "already exists");
        }

        $job_token = bin2hex(random_bytes(16));
        $stmt = $conn->prepare("INSERT INTO `$downloads_table`
            (`job_token`, `file_title`, `file_ext`, `file_link`, `custom_headers`, `platform`, `device_id`, `user_id`, `status`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'queued')");
        $stmt->bind_param("sssssssi", $job_token, $file_title, $file_ext, $file_link, $custom_headers, $platform, $device_id, $user_id);
        if ($stmt->execute()) {
            api_response(true, array(
                "job_id" => intval($conn->insert_id),
                "job_token" => $job_token,
                "status" => "queued"
            ), "queued");
        } else {
            api_response(false, null, "db error");
        }
        break;

    case "poll":
        $job_token = isset($_POST["job_token"]) ? trim($_POST["job_token"]) : "";
        if ($job_token == "") {
            api_response(false, null, "job_token is required");
        }

        $job = get_job_by_token($conn, $downloads_table, $job_token);
        assert_job_owner($job);

        $settings = get_settings($conn, $downloads_settings_table);
        $settings["max_concurrent"] = max(1, intval($settings["max_concurrent"]));

        if ($settings["downloads_enabled"] != "1") {
            api_response(true, array("go" => false, "stop" => false, "reason" => "disabled", "status" => $job["status"]));
        }
        if ($settings["pause_all"] == "1") {
            api_response(true, array("go" => false, "stop" => false, "reason" => "paused", "status" => $job["status"]));
        }
        if ($job["status"] == "cancelled") {
            api_response(true, array("go" => false, "stop" => true, "reason" => "cancelled", "status" => "cancelled"));
        }
        if ($job["status"] == "completed" || $job["status"] == "error") {
            api_response(true, array("go" => false, "stop" => true, "reason" => $job["status"], "status" => $job["status"]));
        }

        $active = count_active_downloads($conn, $downloads_table, $job["id"]);
        if ($active >= $settings["max_concurrent"]) {
            api_response(true, array("go" => false, "stop" => false, "reason" => "wait", "active" => $active, "max" => $settings["max_concurrent"], "status" => $job["status"]));
        }

        $conn->prepare("UPDATE `$downloads_table` SET status = 'downloading', started_at = IFNULL(started_at, NOW()), updated_at = NOW() WHERE id = ?")
            ->execute(array($job["id"]));

        api_response(true, array(
            "go" => true,
            "reason" => "ok",
            "active" => $active + 1,
            "max" => $settings["max_concurrent"],
            "status" => "downloading",
            "job_id" => intval($job["id"]),
            "file_title" => $job["file_title"],
            "file_ext" => $job["file_ext"],
            "custom_headers" => $job["custom_headers"]
        ));
        break;

    case "progress":
        $job_token = isset($_POST["job_token"]) ? trim($_POST["job_token"]) : "";
        $downloaded_size = isset($_POST["downloaded_size"]) ? intval($_POST["downloaded_size"]) : 0;
        $total_size = isset($_POST["total_size"]) ? intval($_POST["total_size"]) : 0;
        $speed = isset($_POST["speed"]) ? floatval($_POST["speed"]) : 0;

        if ($job_token == "") {
            api_response(false, null, "job_token is required");
        }

        $job = get_job_by_token($conn, $downloads_table, $job_token);
        assert_job_owner($job);

        $settings = get_settings($conn, $downloads_settings_table);
        if ($settings["pause_all"] == "1" || $job["status"] == "cancelled") {
            api_response(true, array("stop" => true, "status" => $settings["pause_all"] == "1" ? "paused" : "cancelled"));
        }

        if ($total_size > 0 && intval($job["total_size"]) == 0) {
            $conn->prepare("UPDATE `$downloads_table` SET total_size = ? WHERE id = ?")->execute(array($total_size, $job["id"]));
            $job["total_size"] = $total_size;
        }
        $final_total = intval($job["total_size"]);
        $progress = ($final_total > 0) ? round($downloaded_size / $final_total * 100, 2) : 0;

        $stmt = $conn->prepare("UPDATE `$downloads_table` SET downloaded_size = ?, progress = ?, speed = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("iddi", $downloaded_size, $progress, $speed, $job["id"]);
        $stmt->execute();

        api_response(true, array(
            "downloaded_size" => $downloaded_size,
            "total_size" => $final_total,
            "progress" => $progress,
            "status" => $job["status"]
        ));
        break;

    case "complete":
        $job_token = isset($_POST["job_token"]) ? trim($_POST["job_token"]) : "";
        if ($job_token == "") {
            api_response(false, null, "job_token is required");
        }
        $job = get_job_by_token($conn, $downloads_table, $job_token);
        assert_job_owner($job);
        $downloaded_size = isset($_POST["downloaded_size"]) ? intval($_POST["downloaded_size"]) : intval($job["downloaded_size"]);
        $conn->prepare("UPDATE `$downloads_table` SET status = 'completed', downloaded_size = ?, progress = 100, speed = 0, updated_at = NOW(), completed_at = NOW() WHERE id = ?")
            ->execute(array($downloaded_size, $job["id"]));
        api_response(true, null, "completed");
        break;

    case "fail":
        $job_token = isset($_POST["job_token"]) ? trim($_POST["job_token"]) : "";
        $error_msg = isset($_POST["error_msg"]) ? substr($_POST["error_msg"], 0, 500) : "";
        if ($job_token == "") {
            api_response(false, null, "job_token is required");
        }
        $job = get_job_by_token($conn, $downloads_table, $job_token);
        assert_job_owner($job);
        $conn->prepare("UPDATE `$downloads_table` SET status = 'error', error_msg = ?, speed = 0, updated_at = NOW() WHERE id = ?")
            ->execute(array($error_msg, $job["id"]));
        api_response(true, null, "failed");
        break;

    case "pause":
        $job_token = isset($_POST["job_token"]) ? trim($_POST["job_token"]) : "";
        if ($job_token == "") {
            api_response(false, null, "job_token is required");
        }
        $job = get_job_by_token($conn, $downloads_table, $job_token);
        assert_job_owner($job);
        $conn->prepare("UPDATE `$downloads_table` SET status = 'paused', speed = 0, updated_at = NOW() WHERE job_token = ?")
            ->execute(array($job_token));
        api_response(true, null, "paused");
        break;

    case "resume":
        $job_token = isset($_POST["job_token"]) ? trim($_POST["job_token"]) : "";
        if ($job_token == "") {
            api_response(false, null, "job_token is required");
        }
        $job = get_job_by_token($conn, $downloads_table, $job_token);
        assert_job_owner($job);
        $conn->prepare("UPDATE `$downloads_table` SET status = 'queued', started_at = NULL, updated_at = NOW() WHERE job_token = ?")
            ->execute(array($job_token));
        api_response(true, null, "resumed");
        break;

    // ============ ADMIN ACTIONS ============

    case "list":
        require_admin();

        $status = isset($_POST["status"]) && $_POST["status"] !== "" ? trim($_POST["status"]) : "";
        $search = isset($_POST["search"]) ? trim($_POST["search"]) : "";
        $limit = isset($_POST["limit"]) ? min(500, max(1, intval($_POST["limit"]))) : 100;
        $offset = isset($_POST["offset"]) ? max(0, intval($_POST["offset"])) : 0;

        $where = array();
        $params = array();
        $types = "";
        if ($status !== "") {
            $where[] = "status = ?";
            $params[] = $status;
            $types .= "s";
        }
        if ($search !== "") {
            $where[] = "(file_title LIKE ? OR file_link LIKE ?)";
            $params[] = "%" . $search . "%";
            $params[] = "%" . $search . "%";
            $types .= "ss";
        }
        $sql = "SELECT * FROM `$downloads_table`";
        if (count($where) > 0) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        $jobs = array();
        while ($row = $res->fetch_assoc()) {
            $row["id"] = intval($row["id"]);
            $row["total_size"] = intval($row["total_size"]);
            $row["downloaded_size"] = intval($row["downloaded_size"]);
            $row["progress"] = floatval($row["progress"]);
            $row["speed"] = floatval($row["speed"]);
            $row["user_id"] = $row["user_id"] !== null ? intval($row["user_id"]) : null;
            $jobs[] = $row;
        }
        api_response(true, $jobs);
        break;

    case "stats":
        require_admin();
        $stats = array();
        $res = $conn->query("SELECT status, COUNT(*) AS total FROM `$downloads_table` GROUP BY status");
        $counts = array("queued" => 0, "downloading" => 0, "paused" => 0, "completed" => 0, "error" => 0, "cancelled" => 0);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                if (isset($counts[$row["status"]])) {
                    $counts[$row["status"]] = intval($row["total"]);
                }
            }
        }
        $stats["counts"] = $counts;
        $stats["total"] = array_sum($counts);

        $active_speed = 0;
        $res = $conn->query("SELECT COALESCE(SUM(speed), 0) AS total_speed FROM `$downloads_table`
                             WHERE status = 'downloading' AND updated_at >= NOW() - INTERVAL 120 SECOND");
        if ($res && $row = $res->fetch_assoc()) {
            $active_speed = floatval($row["total_speed"]);
        }
        $stats["active_speed"] = $active_speed;

        $res = $conn->query("SELECT COALESCE(SUM(downloaded_size), 0) AS today_bytes FROM `$downloads_table`
                             WHERE completed_at IS NOT NULL AND DATE(completed_at) = CURDATE()");
        if ($res && $row = $res->fetch_assoc()) {
            $stats["today_completed_bytes"] = intval($row["today_bytes"]);
        } else {
            $stats["today_completed_bytes"] = 0;
        }

        $settings = get_settings($conn, $downloads_settings_table);
        $stats["settings"] = $settings;
        api_response(true, $stats);
        break;

    case "settings_get":
        require_admin();
        api_response(true, get_settings($conn, $downloads_settings_table));
        break;

    case "settings_save":
        require_admin();
        save_settings($conn, $downloads_settings_table, $_POST);
        api_response(true, get_settings($conn, $downloads_settings_table), "saved");
        break;

    case "cancel":
        require_admin();
        $job_id = isset($_POST["job_id"]) ? intval($_POST["job_id"]) : 0;
        if ($job_id == 0) {
            api_response(false, null, "job_id is required");
        }
        $conn->prepare("UPDATE `$downloads_table` SET status = 'cancelled', speed = 0, updated_at = NOW() WHERE id = ?")
            ->execute(array($job_id));
        api_response(true, null, "cancelled");
        break;

    case "resume_admin":
        require_admin();
        $job_id = isset($_POST["job_id"]) ? intval($_POST["job_id"]) : 0;
        if ($job_id == 0) {
            api_response(false, null, "job_id is required");
        }
        $conn->prepare("UPDATE `$downloads_table` SET status = 'queued', started_at = NULL, updated_at = NOW() WHERE id = ?")
            ->execute(array($job_id));
        api_response(true, null, "resumed");
        break;

    case "delete":
        require_admin();
        $job_id = isset($_POST["job_id"]) ? intval($_POST["job_id"]) : 0;
        if ($job_id == 0) {
            api_response(false, null, "job_id is required");
        }
        $conn->prepare("DELETE FROM `$downloads_table` WHERE id = ?")->execute(array($job_id));
        api_response(true, null, "deleted");
        break;

    case "add":
        require_admin();
        $file_link = isset($_POST["file_link"]) ? trim($_POST["file_link"]) : "";
        $file_title = isset($_POST["file_title"]) ? trim($_POST["file_title"]) : "";
        $file_ext = isset($_POST["file_ext"]) ? trim($_POST["file_ext"]) : "";
        $custom_headers = isset($_POST["custom_headers"]) ? $_POST["custom_headers"] : "{}";
        $device_id = isset($_POST["device_id"]) && trim($_POST["device_id"]) !== "" ? trim($_POST["device_id"]) : "admin";
        $platform = "admin";

        if ($file_link == "") {
            api_response(false, null, "file_link is required");
        }
        if ($file_title == "") {
            $file_title = basename(parse_url($file_link, PHP_URL_PATH));
        }

        $job_token = bin2hex(random_bytes(16));
        $stmt = $conn->prepare("INSERT INTO `$downloads_table`
            (`job_token`, `file_title`, `file_ext`, `file_link`, `custom_headers`, `platform`, `device_id`, `status`)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'queued')");
        $stmt->bind_param("sssssss", $job_token, $file_title, $file_ext, $file_link, $custom_headers, $platform, $device_id);
        if ($stmt->execute()) {
            api_response(true, array("job_id" => intval($conn->insert_id), "job_token" => $job_token, "device_id" => $device_id), "added");
        } else {
            api_response(false, null, "db error");
        }
        break;

    default:
        api_response(false, null, "unknown action");
}
