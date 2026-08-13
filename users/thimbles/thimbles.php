<?php
header("Content-Type:application/json");
include_once('../config_db.php');


if (isset($_GET["action"]) && $_GET["action"] !== "") {
    $action = $_GET["action"];
    if ($action == "add_hints") {
        if (isset($_GET["user_id"])) {
            $user_id = $_GET["user_id"];
        } else {
            exit();
        }
        if (isset($_GET["txn_id"])) {
            $txn_id = $_GET["txn_id"];
        } else {
            exit();
        }
        $num_of_add_hints = isset($_GET["num_of_hints"]) ? (int) $_GET["num_of_hints"] : 0;

        $payment_info = $conn->query("SELECT * From payments WHERE txn_id=" . esc($txn_id));
        if ($payment_info->num_rows > 0) {
            $row = $payment_info->fetch_assoc();
            $successfull_url_status = $row["successfull_url_status"];

            if ($successfull_url_status == 0) {

                $thimbles_user_data = $conn->query("SELECT * From mou_thimbles WHERE acc_id=" . esc($user_id));
                if ($thimbles_user_data->num_rows > 0) {
                    $row = $thimbles_user_data->fetch_assoc();
                    $user_hints = $row["num_of_hints"];
                } else {
                    $user_hints = 5;
                    $insert = $conn->query("INSERT INTO mou_thimbles(acc_id,num_of_hints) VALUES (" . esc($user_id) . "," . esc($user_hints) . ")");
                }

                $new_num_of_hints = $user_hints + $num_of_add_hints;
                if ($conn->query("UPDATE mou_thimbles SET num_of_hints=" . esc($new_num_of_hints) . " WHERE acc_id=" . esc($user_id)) !== false) {
                    $ret_msg = "$num_of_add_hints hints have been added successfully \n new_hints_num : $new_num_of_hints";
                } else {
                    $ret_msg = "An error occurred while adding hints";
                }

                if ($conn->query("UPDATE payments SET successfull_url_status=1 WHERE txn_id=" . esc($txn_id)) === false) {
                    $ret_msg = "An error occurred while modifying the successful status of the payment";
                }
            } else if ($successfull_url_status == 1) {
                $ret_msg = "This payment has already been successfully completed";
            }
        } else {
            $ret_msg = "An error occurred during the payment process";
            exit();
        }
    }
    echo $ret_msg;
}
