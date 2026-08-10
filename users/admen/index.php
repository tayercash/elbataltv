<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../config_db.php';

include "auth.php";



$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/")) . "/";
$actual_link = mb_substr($actual_link, -1) == "/" ? substr_replace($actual_link, "", -1) : $actual_link;
$domainlink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$this_file_link = $actual_link . "/" . pathinfo(__FILE__, PATHINFO_FILENAME) . ".php";

$page_title = "Elbatal Tv Users";
$page_img = $actual_link . "/logo.jpg";

$now = new DateTime();
$now_time = $now->getTimestamp();
$today_date = date('Y-m-d', $now_time);
$yesterday_date = date('Y-m-d', strtotime("-1 days"));

$result = $conn->query("SELECT COUNT(id) AS NumberOfUseres FROM `$users_table_name`");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $NumberOfUseres = $row["NumberOfUseres"];
}

$result = $conn->query("SELECT COUNT(id) AS NumberOfUseresToday FROM `$users_table_name` WHERE DATE(`created_at`) = '$today_date'");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $NumberOfUseresToday = $row["NumberOfUseresToday"];
}

$result = $conn->query("SELECT COUNT(id) AS today_actives FROM `$users_table_name` WHERE DATE(`code_last_active`) = '$today_date'");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $today_actives = $row["today_actives"];
}
$result = $conn->query("SELECT COUNT(id) AS yesterday_actives FROM `$users_table_name` WHERE DATE(`code_last_active`) = '$yesterday_date'");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $yesterday_actives = $row["yesterday_actives"];
}
?>
<!DOCTYPE html>
<html dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $page_title; ?>
    </title>
    <meta content='<?php echo $page_title; ?>' property='og:title' />
    <meta content='<?php echo $page_title; ?>' property='og:site_name' />
    <meta content='website' property='og:type' />
    <meta content='<?php echo $page_title; ?>' property='og:image:alt' />
    <meta content='<?php echo $page_img; ?>' property='og:image' />
    <!--[ Twitter card ]-->
    <meta content='<?php echo $page_title; ?>' name='twitter:title' />
    <meta content='<?php echo $page_img; ?>' name='twitter:image:src' />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <!-- Google Fonts Roboto -->
    <link rel="stylesheet" href="files/fonts/noto-sans-arabic/font.css">
    <!-- <link href="files/bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="files/css/mdb.rtl.min.css" />
    <link href='files/css/dataTables.bootstrap5.min.css' rel='stylesheet' />
    <link href='files/css/responsive.dataTables.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="files/css/datatable.buttons.min.css" />
    <link rel="stylesheet" href="files/css/buttons.bootstrap5.min.css" />

    <style>
        *,
        :before,
        :after {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box
        }

        body {
            font-family: 'Noto Sans Arabic', sans-serif;
            overflow: hidden;
            overflow-y: auto;
        }

        .mou_bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            /* fallback for old browsers */
            background: #6a11cb;

            /* Chrome 10-25, Safari 5.1-6 */
            background: -webkit-linear-gradient(to right, rgba(106, 17, 203, 1), rgba(37, 117, 252, 1));

            /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
            background: linear-gradient(to right, rgba(106, 17, 203, 1), rgba(37, 117, 252, 1))
        }

        #orders_wrapper>div:nth-child(1),
        #orders_wrapper>div:nth-child(3) {
            direction: ltr;
        }

        #orders_filter>label {
            direction: rtl;
        }

        main {
            padding: 0.5rem;
        }

        .table_full_container {
            overflow: hidden;
            /* width: 100%; */
            /* max-width: 1100px; */
            display: block;
            position: relative;
            padding: 1rem;
            border-radius: 15px;
            box-shadow: rgba(0, 0, 0, 0.2) 0px 12px 28px 0px, rgba(0, 0, 0, 0.1) 0px 2px 4px 0px, rgba(255, 255, 255, 0.05) 0px 0px 0px 1px inset;
        }

        .table_container {
            width: 100%;
        }

        .order_data_body a {
            display: block;
        }

        td .btn {
            display: inline-block;
            margin: 0 2px;
        }

        .mou-info-table {
            border-collapse: collapse;
            width: 100%;

        }

        .mou-info-table tr {
            border-bottom: 1px solid #000;
        }

        .mou-info-table tr:first-child {
            border-top: 1px solid #000;
        }

        .mou-info-table tr td {
            background: #0000;
            padding: 8px;
        }

        .mou-info-table tr td:first-child {
            border-left: 1px solid #000;
        }

        .mou-info-table td:first-child {
            width: 24%;
        }

        .website_name {
            display: block;
            text-align: center;
            font-size: 60px;
            font-weight: 800;
        }

        tr.selected td:first-child {
            background: #332d2d;
            color: #fff !important;
        }

        div.dataTables_wrapper div.dataTables_info {
            float: right;
        }

        div.dataTables_wrapper div.dataTables_paginate {
            float: left;
        }

        .actions_btns {
            margin-bottom: 8px;
        }


        :root {
            --mdb-body-color: #000;
        }
    </style>
</head>

<body>
    <div class="mou_bg"></div>

    <div class="modal fade" id="order_data_modal" tabindex="-1" role="dialog" aria-labelledby="order_data_modal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">معلومات الحساب</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body order_data_body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="user_requests_modal" tabindex="-1" role="dialog" aria-labelledby="user_requests_modal" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">طلبات الحساب</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body order_data_body">
                    <div>معرف الحساب : <span class="user_id"></span></div>
                    <div>اسم المستخدم : <span class="user_name"></span></div>
                    <div>البريد الالكتروني : <span class="user_email"></span></div>

                    <div class="user_requests">
                        <table class='table table-striped dt-responsive display nowrap' id='user_requests' style='width:100%'>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>ميثود</th>
                                    <th>المرسل</th>
                                    <th>Product Id</th>
                                    <th>المبلغ</th>
                                    <th>التاريخ</th>
                                    <th>الحاله</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <main>

        <div class="row">
            <div class="col-xl-3 col-sm-6 col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between px-md-1">
                            <div class="align-self-center">
                                <i class="fas fa-chart-line text-success fa-3x"></i>
                            </div>
                            <div class="text-end">
                                <h3>Users</h3>
                                <p class="mb-0"><?php echo $NumberOfUseres ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between px-md-1">
                            <div class="align-self-center">
                                <i class="fas fa-chart-line text-success fa-3x"></i>
                            </div>
                            <div class="text-end">
                                <h3>New Users Today</h3>
                                <p class="mb-0"><?php echo $NumberOfUseresToday ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between px-md-1">
                            <div class="align-self-center">
                                <i class="fas fa-chart-line text-success fa-3x"></i>
                            </div>
                            <div class="text-end">
                                <h3>Today Actives</h3>
                                <p class="mb-0"><?php echo $today_actives ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6 col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between px-md-1">
                            <div class="align-self-center">
                                <i class="fas fa-chart-line text-success fa-3x"></i>
                            </div>
                            <div class="text-end">
                                <h3>Yesterday Actives</h3>
                                <p class="mb-0"><?php echo $yesterday_actives ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="table_full_container bg-white text-black">
            <div class="table_container">
                <div class="actions_btns">
                    <!-- <button type="button" class="btn btn-dark bg-gradient text-white" onclick="print_selected()"><i class="fa-solid fa-print"></i>
                        طباعة المحدد</button> -->
                </div>
                <table class='table table-striped dt-responsive display nowrap' id='orders' style='width:100%'>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>الاسم</th>
                            <th>الايميل</th>
                            <th>نوع الحساب</th>
                            <th>الإشتراك</th>
                            <th>الأوامر</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <script type="text/javascript" src="files/js/jquery.min.js"></script>
    <script src="files/js/mdb.min.js"></script>
    <!-- <script src="files/bootstrap-5.0.2-dist/js/bootstrap.min.js"></script> -->
    <script src="files/js/datatable.js"></script>
    <script src='files/js/jquery.dataTables.min.js'></script>
    <script src='files/js/datatable.buttons.min.js'></script>
    <script src='files/js/dataTables.bootstrap5.min.js'></script>
    <script src='files/js/dataTables.responsive.min.js'></script>
    <!-- <script src='https://cdnjs.cloudflare.com/ajax/libs/datatables.net-buttons-dt/2.0/.1/buttons.dataTables.min.js'></script> -->
    <!-- <script src='https://cdn.datatables.net/buttons/2.0.0/js/dataTables.buttons.min.js'></script> -->
    <script src='files/js/buttons.bootstrap5.min.js'></script>
    <script>
        var now_utc_time = <?php echo $now_time; ?>;
        setInterval(function() {
            now_utc_time = now_utc_time + 1;
        }, 1000)
    </script>
    <script>
        $dollar_to_pound = <?php echo $dollar_to_pound ?>;

        var Users_rols = {
            1: "ادمن",
            2: "مستخدم"
        }
        var orders_table = $('#orders').DataTable({
            "order": [
                [0, "desc"]
            ],
            responsive: true,
            "lengthMenu": [
                [10, 25, 50, 100, 200, -1],
                [10, 25, 50, 100, 200, "All"]
            ],
            buttons: [
                'pageLength'
            ],
            "processing": true,
            "serverSide": false,
            "ajax": "fetchData.php",
            dom: "Bfrtip",
            deferRender: true,
            "columns": [{
                    "data": 0,
                },
                {
                    "data": 1,
                },
                {
                    "data": 2,
                },
                {
                    "data": 11,
                    "render": function(data, type, row, meta) {
                        if (data == "user") {
                            return "مستخدم";
                        } else if (data == "admin") {
                            return "ادمن";
                        }
                        return data;
                    }
                },
                {
                    "data": 13,
                    "render": function(data, type, row, meta) {
                        // test_date = new Date(row[13]);
                        // result = test_date.getTime();
                        pro_until = row[14];
                        if (pro_until < now_utc_time) {
                            return "حساب مجاني";
                        } else {
                            return "حساب مدفوع";
                        }
                        return data;
                    }
                },
                {
                    "data": 4,
                    "render": function(data, type, row, meta) {
                        return `
                            <button type="button" class="btn btn-dark bg-gradient text-white" onclick="show_data(this)"><i class="fa-solid fa-eye"></i><data style="display:none;">${JSON.stringify(row)}</data></button>` +
                            `<button type="button" class="btn btn-dark bg-gradient text-white" onclick="edit_data(this,${meta.row})"><i class="fa-solid fa-edit"></i><data style="display:none;">${JSON.stringify(row)}</data></button>` +
                            `<button type="button" class="btn btn-dark bg-gradient text-primary" onclick="get_user_requsets('${row[0]}','${row[1]}','${row[2]}')"><i class="fas fa-list-ul"></i></button>`;
                    }
                }
            ],
            select: true,
            "language": {
                "sLengthMenu": "",
                "zeroRecords": "لا يوجد حسابات",
                "info": "يعرض حساب _PAGE_ من _PAGES_ من اجمالي _TOTAL_ حساب",
                "sSearch": "البحث : ",
                "paginate": {
                    "previous": "السابق",
                    "next": "التالي"
                }
            }
        });

        // orders_table.on('click', 'tbody tr td:first-child', function(e) {
        // $(this).parents("tr").toggleClass('selected');
        // e.currentTarget.classList.toggle('selected');
        // });

        function get_user_requsets(user_id, user_name, user_email) {
            $("#user_requests_modal .user_id").text(user_id);
            $("#user_requests_modal .user_name").text(user_name);
            $("#user_requests_modal .user_email").text(user_email);
            $('#user_requests_modal').modal("show");

            $("#user_requests tbody").html(`<div class="loader">جاري التحميل</div>`);
            $.ajax({
                type: "POST",
                url: "get_user_phone_payments_requests.php",
                data: {
                    action: "get_user_rquests",
                    user_id: user_id
                },
                success: function(data, textStatus, xhr) {
                    $("#user_requests .loader").remove();

                    for (var i = 0; i < Object.keys(data.messages).length; i++) {
                        message_code = Object.keys(data.messages)[i];
                        message = data.messages[message_code];
                        if (message_code == 200) {

                            user_requests = message;
                            for (i = 0; i < user_requests.length; i++) {
                                user_request = user_requests[i];

                                request_id = user_request["req_id"];
                                sender_phone = user_request["sender_phone"];
                                phone_cash_name = user_request["phone_cash_name"];
                                product_id = user_request["product_id"];
                                request_datetime = user_request["datetime"];
                                request_status = user_request["status"];
                                request_value = user_request["value"];
                                pounds_value = request_value * $dollar_to_pound;

                                from_user_id = user_request["from_user_id"];

                                if (request_status == 0) {
                                    request_status_div = `<button type="button" class="btn btn-dark bg-gradient text-success" onclick="submit_user_phone_payment_request(this,'${from_user_id}','${product_id}','${request_id}')"><i class="fa-solid fa-check"></i></button>`;
                                } else {
                                    request_status_div = `<span class="text-success"><i class="fa-solid fa-check"></i></span>`;
                                }

                                tr_table = `<tr>
                                <td>${request_id}</td>
                                <td>${phone_cash_name}</td>
                                <td>${sender_phone}</td>
                                <td>${product_id}</td>
                                <td>${pounds_value}</td>
                                <td>${request_datetime}</td>
                                <td>${request_status_div}</td>
                                </tr>`;
                                $("#user_requests tbody").append(tr_table);
                            }

                        }
                    }
                },
                error: function(jqXHR, error, errorThrown) {

                }
            });

        }

        function submit_user_phone_payment_request(this_btn, from_user_id, product_id, pay_id) {
            if (confirm("هل انت متأكد من الموافقه علي الطلب ؟") == false) return false;;


            $.ajax({
                type: "GET",
                url: "../api/elbatal_pro_backs.php",
                data: {
                    action: "extend",
                    phone_cash: "1",
                    user_id: from_user_id,
                    type: product_id,
                    pay_id: pay_id
                },
                success: function(data, textStatus, xhr) {
                    console.log(data);
                    for (var i = 0; i < Object.keys(data.messages).length; i++) {
                        message_code = Object.keys(data.messages)[i];
                        message = data.messages[message_code];
                        if (message_code == 200) {

                            updated_pro_until = data.messages.updated_pro_until;
                            console.log(updated_pro_until);

                            $(this_btn).parents("td").html(`<span class="text-success"><i class="fa-solid fa-check"></i></span>`);

                        }
                    }

                },
                error: function(jqXHR, error, errorThrown) {

                }
            });

        }

        function delete_order(order_id, order_code, row_num) {
            if (!confirm(`هل انت متأكد من حذف الطلب : ${order_code}`)) return;

            $.ajax({
                type: "POST",
                url: "api.php",
                data: {
                    "action": "delete_order",
                    "order_id": order_id,
                    "order_row_num": row_num,
                },
                success: function(res) {
                    if (res.status == true) {
                        this_row_num = res.row_num;
                        orders_table.row(this_row_num).remove().draw();
                    }
                }

            });


        }

        function edit_data(this_btn, row_num) {
            row_num_will_edit = row_num;
            row_data = JSON.parse($(this_btn).find("data").html());
            $("#id_will_edit").text(row_data[0]);
            $("#code").val(row_data[1]);
            $("#full_name").val(row_data[2]);
            $("#details").val(row_data[3]);
            $("#address_1").val(row_data[4]);
            $("#address_2").val(row_data[5]);
            $("#address_3").val(row_data[6]);
            $("#phone_num1").val(row_data[7]);
            $("#phone_num2").val(row_data[8]);
            $("#order_price").val(row_data[11]);
            $("#shipping_price").val(row_data[12]);
            $("#full_price").val(row_data[13]);
            $('#order_edit_data_modal').modal("show");
        }

        function submit_edit() {
            $("#submit_edit_form_btn").click();
        }

        $("#shipping_price , #order_price").on("input", function() {
            order_price = $("#order_price").val() == "" ? 0 : parseInt($("#order_price").val());
            shipping_price = $("#shipping_price").val() == "" ? 0 : parseInt($("#shipping_price").val());
            $("#full_price").val(order_price + shipping_price);
        })

        $("#edit_order").submit(function(e) {
            e.preventDefault();
            e.stopPropagation();


            if ($(this)[0].checkValidity()) {
                id_will_edit = $("#id_will_edit").text();

                if (!confirm(`هل انت متأكد من تعديل الطلب : ${id_will_edit}`)) return;

                new_data = {};
                new_data["code"] = $("#code").val();
                new_data["full_name"] = $("#full_name").val();
                new_data["details"] = $("#details").val();
                new_data["address_1"] = $("#address_1").val();
                new_data["address_2"] = $("#address_2").val();
                new_data["address_3"] = $("#address_3").val();
                new_data["phone_num1"] = $("#phone_num1").val();
                new_data["phone_num2"] = $("#phone_num2").val();
                new_data["order_price"] = $("#order_price").val();
                new_data["shipping_price"] = $("#shipping_price").val();
                new_data["full_price"] = $("#full_price").val();


                $.ajax({
                    type: "POST",
                    url: "api.php",
                    data: {
                        "action": "edit_order",
                        "order_id": id_will_edit,
                        "new_data": new_data,
                        "order_row_num": row_num_will_edit,
                    },
                    success: function(res) {
                        if (res.status == true) {
                            this_row_num = res.row_num;
                            orders_table.row(this_row_num).data([
                                id_will_edit,
                                new_data["code"],
                                new_data["full_name"],
                                new_data["details"],
                                new_data["address_1"],
                                new_data["address_2"],
                                new_data["address_3"],
                                new_data["phone_num1"],
                                new_data["phone_num2"],
                                null,
                                null,
                                new_data["order_price"],
                                new_data["shipping_price"],
                                new_data["full_price"]
                            ]).draw();
                            $('#order_edit_data_modal').modal("hide");

                            // alert("تم تعديل الطلب " + id_will_edit + " بنجاح");
                        }
                    }

                });

            }


        })

        function print_order(this_btn) {
            row_data = JSON.parse($(this_btn).find("data").html());
            rows_data = [];
            rows_data.push(row_data);
            print_rows(rows_data);
        }

        function show_data(this_btn) {
            row_data = JSON.parse($(this_btn).find("data").html());

            $("#order_id").text(row_data[1]);
            $("#order_name").text(row_data[2]);
            $("#order_details").text(row_data[3]);
            $("#order_address1").text(row_data[4]);
            $("#order_address2").text(row_data[5]);
            $("#order_address3").text(row_data[6]);
            $("#order_phonenum1").text(row_data[7]);
            $("#order_phonenum2").text(row_data[8]);
            $("#order_full_price").text(row_data[13] + " جنيه");

            $('#order_data_modal').modal("show");

        }

        function print_selected() {
            if (orders_table.rows('.selected').data().length > 0) {

                rows_data_for_print = [];

                for (let index = 0; index < orders_table.rows('.selected').data().length; index++) {
                    rows_data_for_print.push(orders_table.rows('.selected').data()[index]);
                }

                print_rows(rows_data_for_print);
            } else {
                alert("يرجي تحديد طلب او اكثر");
            }

        }

        function print_rows(rows_data, num_in_page = 1) {
            if (num_in_page == 1) {
                num_of_orders_class = " order_for_print_container_1";
            } else if (num_in_page == 2) {
                num_of_orders_class = " order_for_print_container_2";
            }
            html_for_print = `<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Zero Store</title><style>
        body {
            margin: 0;
            padding: 0 8px;
        }
        .mou-info-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 1.3rem;
        }
        .mou-info-table tr {
            border-bottom: 1px solid #000;
        }

        .mou-info-table tr:first-child {
            border-top: 1px solid #000;
        }

        .mou-info-table tr td {
            background: #0000;
            padding: 8px;
        }

        .mou-info-table tr td:first-child {
            border-left: 1px solid #000;
        }

        .mou-info-table td:first-child {
            width: 16%;
        }

        .website_name {
            display: block;
            text-align: center;
            font-size: 60px;
            font-weight: 800;
        }
        .order_for_print_container {
            display: block;
            position: relative;
            padding: 0 8px;
            padding-top: 8px !important;
        }
        .order_for_print_container_1 {
            height: calc(100vh - 16px);
        }
        .order_for_print_container_2 {
            height: calc(50vh - 16px);
        }
        .order_for_print {
            border: solid 2px;
            border-radius: 8px;
        }
        @page {
            size: auto;   /* auto is the initial value */
            margin: 0;  /* this affects the margin in the printer settings */
        }
        </style></head><body>`;


            for (let index = 0; index < rows_data.length; index++) {
                const row = rows_data[index];

                html_for_print += `
            <div class="order_for_print_container ${num_of_orders_class}">
                <div class="order_for_print">
                    <span class="website_name">Zero Store</span>
                    <table class="mou-info-table">
                        <tbody>
                            <tr>
                                <td>الإسم</td>
                                <td><span>${row[2]}</span></td>
                            </tr>
                            <tr>
                                <td>الطلب</td>
                                <td><span>${row[3]}</span></td>
                            </tr>
                            <tr>
                                <td>العنوان</td>
                                <td>
                                    <span>${row[4]}</span><br>
                                    <span>${row[5]}</span><br>
                                    <span>${row[6]}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>رقم التليفون</td>
                                <td>
                                    <span>${row[7]}</span><br>
                                    <span>${row[8]}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>الرقم المرجعي</td>
                                <td><span>${row[1]}</span></td>
                            </tr>
                            <tr>
                                <td>السعر الاجمالي</td>
                                <td><span>${row[13]} جنيه</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;

            }

            html_for_print += `<script>window.print();window.close();</scr` + `ipt></body></html>`;
            var params = [
                'height=' + screen.height,
                'width=' + screen.width,
                'fullscreen=yes' // only works in IE, but here for completeness
            ].join(',');
            var myWindow = window('', 'my div', params);
            myWindow.document.write(html_for_print);

            myWindow.onload = function() { // necessary if the div contain images
                myWindow.focus();
            };




        }
    </script>
</body>

</html>