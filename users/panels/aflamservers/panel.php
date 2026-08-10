<?php
// File: panel.php

$jsonFile = 'servers.json';
// Load existing servers
$servers = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add' || $action === 'edit') {
            $key = trim($_POST['key']);
            $url = trim($_POST['url']);

            if (!empty($key) && !empty($url)) {
                $servers[$key] = $url;
                file_put_contents($jsonFile, json_encode($servers, JSON_PRETTY_PRINT));
            }
        }

        if ($action === 'delete') {
            $key = $_POST['key'];
            unset($servers[$key]);
            file_put_contents($jsonFile, json_encode($servers, JSON_PRETTY_PRINT));
        }

        // Refresh page after action
        header("Location: panel.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Domains Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f2f2f2;
            margin: 0;
        }

        h2 {
            margin-top: 0;
        }

        .main_form {
            background: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
        }

        input[type=text],
        input[type=url] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type=submit],
        button {
            padding: 10px 20px;
            margin-right: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background: #3498db;
            color: white;
        }

        button[type=button] {
            background: #2ecc71;
        }

        button.delete-btn {
            background: #e74c3c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        @media (max-width: 768px) {

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
                width: 100%;
            }

            tr {
                margin-bottom: 15px;
                box-shadow: 0 0 10px #ccc;
                border-radius: 8px;
                overflow: hidden;
                background: #fff;
            }

            td {
                padding: 10px;
                border-bottom: 1px solid #eee;
                position: relative;
            }

            td::before {
                content: attr(data-label);
                font-weight: bold;
                display: block;
                margin-bottom: 5px;
                color: #333;
            }

            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            input[type=text],
            input[type=url] {
                margin-bottom: 10px;
            }
        }

        a {
            display: inline-block;
        }

        .delete {
            background: #e74c3c;
        }

        *,
        ::after,
        ::before {
            box-sizing: border-box;
        }
    </style>
</head>

<body>

    <h2>Add / Edit Server Domain</h2>
    <form class="main_form" method="POST">
        <input type="hidden" name="action" value="add">
        <label>Key:</label>
        <input type="text" name="key" required>
        <label>URL:</label>
        <input type="text" name="url" required>
        <input type="submit" value="Save">
    </form>

    <h2>Existing Servers</h2>
    <table>
        <tr>
            <th>Key</th>
            <th>URL</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($servers as $key => $url): ?>
            <tr>
                <form method="POST">
                    <td><input type="text" name="key" value="<?= htmlspecialchars($key) ?>" required></td>
                    <td><input type="text" name="url" value="<?= htmlspecialchars($url) ?>" required></td>
                    <td>
                        <input type="hidden" name="action" value="edit">
                        <input type="submit" value="Update">
                </form>
                <a href="<?= htmlspecialchars($url) ?>" target="_blank">
                    <button type="button">Open</button>
                </a>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="key" value="<?= htmlspecialchars($key) ?>">
                    <input type="hidden" name="action" value="delete">
                    <button class="delete" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

</body>

</html>