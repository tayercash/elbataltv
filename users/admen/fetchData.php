<?php
header("Content-Type: text/plain; charset=utf-8");
include "../config_db.php";
include "auth.php";
// Include SQL query processing class 
require 'ssp.class.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$dbDetails = array(
    'host' => $servername,
    'user' => $username,
    'pass' => $password,
    'db' => $dbname
);
// Table's primary key 
$primaryKey = 'id';

// Array of database columns which should be read and sent back to DataTables. 
// The `db` parameter represents the column name in the database.  
// The `dt` parameter represents the DataTables column identifier. 
$columns = array(
    array('db' => 'id', 'dt' => 0),
    array('db' => 'username', 'dt' => 1),
    array('db' => 'email', 'dt' => 2),
    array('db' => 'user_icon', 'dt' => 3),
    array('db' => 'google_linked', 'dt' => 4),
    array('db' => 'google_linked_email', 'dt' => 5),
    array('db' => 'google_linked_id', 'dt' => 6),
    array('db' => 'role', 'dt' => 7),
    array('db' => 'active', 'dt' => 8),
    array('db' => 'status', 'dt' => 9),
    array('db' => 'created_at', 'dt' => 10),
    array('db' => 'type', 'dt' => 11),
    array('db' => 'has_pro', 'dt' => 12),
    array('db' => 'pro_until', 'dt' => 13),
    array(
        'db' => 'pro_until', 'dt' => 14,
        'formatter' => function ($d, $row) {
            return strtotime($d);
        }
    )
    
);


// Output data as json format 
echo json_encode(
    SSP::simple($_GET, $dbDetails, $users_table_name, $primaryKey, $columns)
);