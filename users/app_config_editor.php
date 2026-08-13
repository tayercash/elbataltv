<?php
$user_ip = getUserIP();

// Allowed IP addresses
// $allowed_ips = ["197.56.76.221"]; // Replace with your allowed IPs

// // Check if the request is from an allowed IP
// if (!in_array($user_ip, $allowed_ips)) {
//     http_response_code(403);
//     echo 'Access denied: Unauthorized IP address : ' . $user_ip;
//     exit;
// }


if (session_status() === PHP_SESSION_NONE) {
    if (session_name() !== 'ELBATAL_ADMIN') {
        session_name('ELBATAL_ADMIN');
    }
    session_start();
}

if (empty($_SESSION["can_join"]) || $_SESSION["can_join"] !== true) {

    http_response_code(403);

    echo 'Access denied: Unauthorized';

    exit;

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$_POST = json_decode(file_get_contents('php://input'), true);
    // auth: session-based (ELBATAL_ADMIN)
}

$json_file = 'app_config_data.json';
$existing_data = array();

if (file_exists($json_file)) {
    $json_data = file_get_contents($json_file);
    $existing_data = json_decode($json_data, true);

    // Check if the existing data is a valid array
    if (!is_array($existing_data)) {
        $existing_data = array(); // Initialize as an empty array if invalid
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the existing data from the JSON file

    if (isset($_POST["page_action"])) {
        $page_action = $_POST["page_action"];
        if ($page_action == "update_app_config") {

            $assets = array_map('trim', explode("\n", $_POST['assets']));
            $assets = array_filter($assets, function ($asset) {
                return !empty($asset);
            });
            // Get form data and merge it with the existing data
            $updated_data = array(
                "app_mainpage" => $_POST['app_mainpage'],
                "app_version" => $_POST['app_version'],
                "Latest_exe_version" => $_POST['Latest_exe_version'],
                "Latest_Apk_version" => $_POST['Latest_Apk_version'],
                "Latest_ios_version" => $_POST['Latest_ios_version'], // الحقل الجديد
                "apk_link" => $_POST['apk_link'],
                "Latest_exe_dl_Link" => $_POST['Latest_exe_dl_Link'],
                "ios_dl_link" => $_POST['ios_dl_link'], // الحقل الجديد
                "assets" => $assets,
                "loader_show" => isset($_POST['loader_show']) ? filter_var($_POST['loader_show'], FILTER_VALIDATE_BOOLEAN) : false // Default to false if not set
            );

            // Merge the new data with the existing data
            $merged_data = array_merge($existing_data, $updated_data);

            // Convert merged data to JSON format
            $json_data = json_encode($merged_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

            // Save the updated JSON to the file
            if (file_put_contents($json_file, $json_data)) {
                echo "Data successfully updated.";
                $existing_data = $merged_data; // Initialize an empty array
            } else {
                echo "Error saving data.";
            }
        } else if ($page_action == "update_assets") {

            $assets = array_map('trim', explode("\n", $_POST['assets']));
            $assets = array_filter($assets, function ($asset) {
                return !empty($asset);
            });

            // Get form data and merge it with the existing data
            $updated_data = array(
                "app_version" => $_POST['app_version'],
                "assets" => $assets,
            );

            // Merge the new data with the existing data
            $merged_data = array_merge($existing_data, $updated_data);

            // Convert merged data to JSON format
            $json_data = json_encode($merged_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

            // Save the updated JSON to the file
            if (file_put_contents($json_file, $json_data)) {
                echo "Data successfully updated.";
                $existing_data = $merged_data; // Initialize an empty array
            } else {
                echo "Error saving data.";
            }
        }
    }
    exit();
}

$data = $existing_data; // Initialize an empty array
// Set default values if the keys are not set
$app_mainpage = isset($data['app_mainpage']) ? $data['app_mainpage'] : '';
$app_version = isset($data['app_version']) ? $data['app_version'] : '';
$Latest_exe_version = isset($data['Latest_exe_version']) ? $data['Latest_exe_version'] : '';
$Latest_Apk_version = isset($data['Latest_Apk_version']) ? $data['Latest_Apk_version'] : '';
$apk_link = isset($data['apk_link']) ? $data['apk_link'] : '';
$Latest_exe_dl_Link = isset($data['Latest_exe_dl_Link']) ? $data['Latest_exe_dl_Link'] : '';
$assets = isset($data['assets']) ? implode("\n", $data['assets']) : ''; // Convert array to comma-separated string
$loader_show = isset($data['loader_show']) ? ($data['loader_show'] ? 'true' : 'false') : 'true';
$Latest_ios_version = isset($data['Latest_ios_version']) ? $data['Latest_ios_version'] : '';
$ios_dl_link = isset($data['ios_dl_link']) ? $data['ios_dl_link'] : '';


function getUserIP()
{
    $candidates = [];

    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $candidates[] = trim($_SERVER['HTTP_CF_CONNECTING_IP']);
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        foreach (explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']) as $ip) {
            $candidates[] = trim($ip);
        }
    }

    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $candidates[] = trim($_SERVER['HTTP_X_REAL_IP']);
    }

    $candidates[] = $_SERVER['REMOTE_ADDR'];

    foreach ($candidates as $ip) {
        $ip = preg_replace('/^::ffff:/', '', $ip);
        $ip = strtok($ip, ' ');
        $ip = trim($ip, '"\'');

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return '0.0.0.0';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update App Information</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1440px;
            margin: 1rem auto;
            background-color: #fff;
            padding: 1rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        textarea {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
        }

        textarea {
            min-height: 150px;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .message {
            text-align: center;
            font-size: 14px;
            color: green;
            display: none;
        }

        .error {
            color: red;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    <!-- Include crypto-js -->

</head>

<body>
    <div class="container">
        <h2>Update App Information</h2>
        <form id="appInfoForm">
            <div class="form-group">
                <label>App Main Page URL:</label>
                <input type="text" name="app_mainpage" value="<?php echo $app_mainpage; ?>" required>
            </div>

            <div class="form-group">
                <label>App Version:</label>
                <input type="text" name="app_version" value="<?php echo $app_version; ?>" required>
            </div>

            <div class="form-group">
                <label>Latest EXE Version:</label>
                <input type="text" name="Latest_exe_version" value="<?php echo $Latest_exe_version; ?>" required>
            </div>

            <div class="form-group">
                <label>Latest APK Version:</label>
                <input type="text" name="Latest_Apk_version" value="<?php echo $Latest_Apk_version; ?>" required>
            </div>

            <div class="form-group">
                <label>APK Link:</label>
                <input type="text" name="apk_link" value="<?php echo $apk_link; ?>" required>
            </div>

            <div class="form-group">
                <label>Latest iOS Version (IPA):</label>
                <input type="text" name="Latest_ios_version" value="<?php echo $Latest_ios_version; ?>" required>
            </div>

            <div class="form-group">
                <label>iOS Update Link</label>
                <input type="text" name="ios_dl_link" value="<?php echo $ios_dl_link; ?>" required placeholder="itms-services://?action=download-manifest&url=...">
            </div>

            <div class="form-group">
                <label>Latest EXE Download Link:</label>
                <input type="text" name="Latest_exe_dl_Link" value="<?php echo $Latest_exe_dl_Link; ?>" required>
            </div>

            <div class="form-group">
                <label>Assets (one per line):</label>
                <textarea name="assets" required><?php echo $assets; ?></textarea>
            </div>

            <div class="form-group">
                <label>Show Loader:</label>
                <input type="checkbox" name="loader_show" id="loader_show" <?php echo $loader_show == 'true' ? 'checked' : ''; ?>>
            </div>
            <input type="hidden" name="page_action" id="page_action" value="update_app_config">

            <div class="form-group">
                <input type="submit" value="Save Data">
            </div>
        </form>
        <div class="message" id="responseMessage"></div>
    </div>

    <script>
        $(document).ready(function() {
            $('#appInfoForm').on('submit', function(event) {
                event.preventDefault(); // Prevent the form from submitting the traditional way

                // Add loader_show checkbox value (true/false)
                var loader_show_value = $('#loader_show').is(':checked') ? 'true' : 'false';

                // Include the loader_show value in the form data
                var formData = $(this).serializeArray();

                // Convert the array to an object
                const formObject = {};
                formData.forEach(item => {
                    formObject[item.name] = item.value;
                });
                formObject['loader_show'] = loader_show_value

                console.log(formObject);
                // formData.push({ name: 'assets', value: loader_show_value });


                $.ajax({
                    url: 'app_config_editor.php',
                    type: 'POST',
                    data: JSON.stringify(formObject),
                    success: function(response, textStatus, xhr) {
                        var tHeader = xhr.getResponseHeader('t');
                        console.log(tHeader);
                        $('#responseMessage').html(response).removeClass('error').fadeIn();
                    },
                    error: function() {
                        $('#responseMessage').text('Error saving data.').addClass('error').fadeIn();
                    }
                });
            });
        });
    </script>

</body>

</html>