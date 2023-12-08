<?php
session_start();
$_SESSION;

//Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

include("../config.php");

$error_msg = "NA";

//Check if session is set- or route to sign-in page
if (!isset($_SESSION["user_id"])) {
    header("Location: ../sign-in.php");
}

//getting the category list with data
$subsQuery = "SELECT * from dt_subscribers where company_ID = '" . $_SESSION["user_id"] . "';";
$subsList = $pdo->query($subsQuery)->fetchAll();
//getting user logo from info table
$userDataQuery = "SELECT user_logo from dt_user_info_group where user_ID = '" . $_SESSION["user_id"] . "';";
$userDataList = $pdo->query($userDataQuery)->fetchAll();


if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $formName = $_POST["frmname"];

    if ($formName == "newform") { //this request is for inserting categories

        // get variables from front-end elements
        $subscriber_name = $_POST['subscriber_name'];
        $subscriber_email = $_POST['subscriber_email'];
        $subscriber_dept = $_POST['subscriber_dept'];
        $subscriber_pass = random_str(12);

        //inserting category into DB with the right values
        $insertCategory = $pdo->prepare("INSERT INTO dt_subscribers(`company_ID`, `subscriber_name`, `subscriber_pass`, `subscriber_email`, `department`) VALUES (?,?,?,?,?)");

        if ($insertCategory->execute([
            $_SESSION["user_id"], $subscriber_name, password_hash($subscriber_pass, PASSWORD_DEFAULT), $subscriber_email, $subscriber_dept
        ])) {

            try {
                //Server settings
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
                $mail->isSMTP();                                            //Send using SMTP
                $mail->Host       = '184.168.96.211';                     //Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                $mail->Username   = 'mike@webxsolutionz.com';                     //SMTP username
                $mail->Password   = 'ghQ0s7vc@3OK';                               //SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            
                //Recipients
                $mail->setFrom('no-reply@svglobalbusiness.com', 'Mailer');
                $mail->addAddress($subscriber_email, $subscriber_name);     //Add a recipient
                //$mail->addAddress('ellen@example.com');               //Name is optional
                //$mail->addReplyTo('info@example.com', 'Information');
                //$mail->addCC('smak.group@gmail.com');
                $mail->addBCC('smak.group@gmail.com');
            
                //Attachments
                //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
                //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
            
                //Content
                $mail->isHTML(true);                                  //Set email format to HTML
                $mail->Subject = "Credential for School AgEd";
                $mail->Body    = 'Hi<br><br> Welcome to School AgEd App.<br> Here is your credentials:<br>User: '.$subscriber_email.'<br>Password: '.$subscriber_pass.'<br><br>Thanks<br>Webmaster';
                //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            
                $mail->send();
                echo 'Message has been sent';
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }

            header("Location: subscriber.php");
        }
    } else if ($formName == "updateform") { //this request is for updating categories

        // get variables from front-end elements
        $subscriber_name = $_POST['subscriber_name'];

        // //inserting category into DB with the right values
        // $sql = "UPDATE dt_subscribers SET `subscriber_name` = '$subscriber_name' WHERE uID = $category_id;";
        // $stmt = $pdo->prepare($sql);
        // if ($stmt->execute()) {
        //     $check = move_uploaded_file($tempFile, $targetFile);
        //     header("Location: category.php");
        // }
    }
}


?>
<!DOCTYPE php>
<php dir="ltr" lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!-- Tell the browser to be responsive to screen width -->
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="keywords" content="wrappixel, admin dashboard, php css dashboard, web dashboard, bootstrap 5 admin, bootstrap 5, css3 dashboard, bootstrap 5 dashboard, Monsterlite admin bootstrap 5 dashboard, frontend, responsive bootstrap 5 admin template, Monster admin lite design, Monster admin lite dashboard bootstrap 5 dashboard template">
        <meta name="description" content="Monster Lite is powerful and clean admin dashboard template, inpired from Bootstrap Framework">
        <meta name="robots" content="noindex,nofollow">
        <title>Subscribers</title>
        <link rel="canonical" href="https://www.wrappixel.com/templates/monster-admin-lite/" />
        <!-- Favicon icon -->
        <link rel="icon" type="image/png" sizes="16x16" href="./assets/images/logo1.png">
        <!-- Custom CSS -->
        <link href="./assets/plugins/chartist/dist/chartist.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
        <!-- Font Awesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet" />
        <!-- MDB -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/4.4.0/mdb.min.css" rel="stylesheet" />
        <!-- Custom CSS -->

        <link href="css/style.min.css" rel="stylesheet">
        <link href="css/style.front.css" rel="stylesheet">

        <!-- datatables CSS -->
        <link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/datetime/1.1.2/css/dataTables.dateTime.min.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" rel="stylesheet" />
    </head>

    <body>
        <style>
            .dt-buttons {
                margin-left: 20px !important;
            }

            .dataTables_wrapper {
                margin-top: 5px;
            }
        </style>
        <div class="preloader">
            <div class="lds-ripple">
                <div class="lds-pos"></div>
                <div class="lds-pos"></div>
            </div>
        </div>
        <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
            <header class="topbar" data-navbarbg="skin6" style="background:#ffff; box-shadow:none;">
                <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                    <div class="navbar-header" data-logobg="skin6">
                        <a class="navbar-brand d-md-none d-sm-flex justify-content-center" href="index.php">
                            <!-- Logo icon -->
                            <img src="./assets/images/school-logo.png" alt="homepage" class="dark-logo img-fluid" />
                        </a>
                        <a class="nav-toggler waves-effect waves-light text-dark d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                    </div>
                    <!-- ============================================================== -->
                    <!-- End Logo -->
                    <!-- ============================================================== -->
                    <div class="navbar-collapse collapse #f2f7f8 justify-content-between" id="navbarSupportedContent" data-navbarbg="skin5">
                        <div>
                        </div>
                        <ul class="navbar-nav">
                            <li class="nav-item dropdown">
                                <a class="nav-link" href="#" role="button" aria-expanded="false">
                                    <i class="bi bi-bell"></i>
                                </a>
                                <a class="nav-link" href="support.php" role="button" aria-expanded="false">
                                    <i class="bi bi-headset"></i>
                                </a>
                                <a class="nav-link" href="account.php" role="button" aria-expanded="false">
                                    <i class="bi bi-person-fill"></i>
                                </a>
                                <a class="nav-link" href="../logout.php" role="button" aria-expanded="false">
                                    <i class="bi bi-box-arrow-right"></i>
                                </a>
                                <a class="nav-link ms-2" href="./account.php" role="button" aria-expanded="false">
                                    <img src="<?= $userDataList[0]["user_logo"] == "NA" ? "./assets/images/logo_placeholder.png" : $userDataList[0]["user_logo"] ?>" alt="user" class="img-fluid" width="120" />
                                </a>
                                <ul class="dropdown-menu show" aria-labelledby="navbarDropdown"></ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <aside class="left-sidebar" data-sidebarbg="skin6">
                <!-- Sidebar scroll-->
                <div class="scroll-sidebar pt-0">
                    <!-- Sidebar navigation-->
                    <nav class="sidebar-nav">
                        <!-- <a class="navbar-brand justify-content-center" href="index.php">
                        <img src="./assets/images/logo.png" alt="homepage" class="dark-logo" />

                        <h5 class="ms-2 mb-0 text-light fw-normal">Group Admin</h5>
                    </a> -->
                        <ul id="sidebarnav">
                            <!-- User Profile-->
                            <li class="sidebar-item mb-5"> <a class="sidebar-link sidebar-link p-0" href="index.php" aria-expanded="false">
                                    <img src="./assets/images/sidebar-logo1.png" alt="homepage" class="dark-logo img-fluid" /></a></li>
                            <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="index.php" aria-expanded="false"><i class=" me-3 fa-solid fa-house"></i><span class="hide-menu">Dashboard</span></a></li>
                            <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="reports.php" aria-expanded="false">
                                    <i class="me-3 bi bi-pie-chart-fill"></i><span class="hide-menu">Reports</span></a>
                            </li>
                            <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="material.php" aria-expanded="false"><i class="me-3 bi bi-grid-fill"></i><span class="hide-menu">Materials</span></a></li>
                            <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="testing-main.php" aria-expanded="false"><i class="me-3 far fa-clone"></i><span class="hide-menu">Testing</span></a></li>
                            <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="subscriber.php" aria-expanded="false"><i class="me-3 bi bi-people-fill"></i><span class="hide-menu">Subscribers</span></a></li>
                            <li class="sidebar-item"> <a class="sidebar-link sidebar-link" href="account.php" aria-expanded="false"><i class="me-3 bi bi-person-fill"></i><span class="hide-menu">Account</span></a></li>
                            <li class="sidebar-item">
                                <a class="sidebar-link sidebar-link" href="support.php" aria-expanded="false"><i class="me-3 bi bi-headset"></i><span class="hide-menu">Support</span></a>
                            </li>
                        </ul>

                    </nav>
                    <!-- End Sidebar navigation -->
                </div>
                <!-- End Sidebar scroll-->
            </aside>
            <!-- Page wrapper  -->
            <!-- ============================================================== -->
            <div class="page-wrapper subscriber mt-3">
                <div class="container-fluid">
                    <div class="d-flex align-items-center mb-4">
                        <h5 class="fw-normal me-5 text-primary">Subscribers</h5>
                        <p nowrap>Optimize agent and employee performance with personalized materials and testing.</p>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-8 col-xl-9 my-1">
                            <!-- <div class="input-group align-items-center">
                                <i class="bi bi-search"></i>
                                <input type="text" class="form-control" aria-label="Text input with 2 dropdown buttons" placeholder="Search...">
                                <a href="#" class="text-secondary">
                                    <i class="bi bi-mic-fill"></i>
                                </a>
                            </div> -->
                        </div>
                        <div class="col-sm-12 col-md-2 col-lg-1 col-xl-1 my-1">
                            <!-- <button class="btn btn-light text-primary w-100">Filter</button> -->
                        </div>
                        <div class="col-sm-12 col-md-4 col-lg-3 col-xl-2 my-1">
                            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#exampleModal">New Subscribers</button>
                        </div>
                    </div>
                    <div class="table-responsive my-5" style="height: 50vh;">
                        <!-- <table class="table align-middle"> -->
                        <table class="table align-middle" id="example">
                            <thead>
                                <tr>
                                    <th scope="col">
                                        <div class="d-flex">
                                            <div class="form-check">
                                                <input class="form-check-input border-0" type="checkbox" value="" id="flexCheckDefault">
                                            </div>
                                            <span>Select User</span>
                                        </div>
                                    </th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Dapartment</th>
                                    <th scope="col">User ID</th>
                                    <th scope="col">Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subsList as $subscriber) {
                                ?>
                                    <tr>
                                        <th scope="row" class="d-flex align-items-center">
                                            <input class="form-check-input border-0 me-4" type="checkbox" value="" id="flexCheckDefault">
                                            <!-- </div> -->
                                            <span class="d-flex align-items-center">
                                                <img src=<?php if ($subscriber["image"] == "NA") {
                                                                echo "./assets/images/user_placeholder.png";
                                                            } else {
                                                                echo "../dt_subscriber/" . $subscriber["image"];
                                                            } ?> alt="user" class="img-fluid" width="50" />
                                                <span class="ms-4"><a href='subscriber-detail.php?uid=<?= $subscriber["uID"] ?>' class="text-secondary"><?= $subscriber["subscriber_name"] ?></a></span>
                                            </span>
                                        </th>
                                        <td><?= $subscriber["subscriber_email"] ?? '' ?></td>
                                        <td><?= $subscriber["department"] ?></td>
                                        <td><?= $subscriber["uID"] ?></td>
                                        <td>
                                            <div class="edit-delete">
                                                <!-- <span class="pe-3 delete"><i class="bi bi-trash3" onclick="deleteSubscriber(<?= $subscriber['uID'] ?>)"></i></span> -->
                                                <button class="btn btn-primary" onclick="deleteSubscriber(<?= $subscriber['uID'] ?>)"><i class="bi bi-trash3"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- MODAL START -->
                    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content p-5" style="border-radius:20px;">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="model-head">
                                        <h4 class="fw-normal">New Subscriber</h4>
                                    </div>
                                    <div class="modal-body px-0">
                                        <div class="mb-2">
                                            <label for="subscriber_name" class="form-label">Subscriber Name</label>
                                            <input required type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" name="subscriber_name">
                                        </div>
                                    </div>
                                    <div class="modal-body px-0">
                                        <div class="mb-2">
                                            <label for="subscriber_email" class="form-label">Subscriber Email</label>
                                            <input required type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" name="subscriber_email">
                                        </div>
                                    </div>
                                    <div class="modal-body px-0">
                                        <div class="mb-2">
                                            <label for="subscriber_dept" class="form-label">Subscriber Department</label>
                                            <input required type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" name="subscriber_dept">
                                        </div>
                                    </div>
                                    <div class="text-end pt-5">
                                        <button type="button" class="btn btn-light px-5 mx-3" data-bs-dismiss="modal" style="border-radius:10px !important; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px">Cancel</button>
                                        <button type="submit" class="btn btn-primary px-5 mx-3" style="border-radius:10px !important; box-shadow:rgba(9, 9, 24, 0.1) 0px 0px 6px">Submit</button>
                                    </div>
                                    <input type="hidden" name="frmname" value="newform" />
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- MODAL END -->
                </div>
            </div>
            <!-- ============================================================== -->
        </div>
        <!-- MDB -->
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/4.4.0/mdb.min.js"></script>
        <script src="./assets/plugins/jquery/dist/jquery.min.js"></script>
        <!-- Bootstrap tether Core JavaScript -->
        <script src="./assets/plugins/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script src="js/app-style-switcher.js"></script>
        <!--Wave Effects -->
        <script src="js/waves.js"></script>
        <!--Menu sidebar -->
        <script src="js/sidebarmenu.js"></script>
        <!--Custom JavaScript -->
        <script src="js/custom.js"></script>
        <!--This page JavaScript -->
        <!--flot chart-->
        <script src="./assets/plugins/flot/jquery.flot.js"></script>
        <script src="./assets/plugins/flot.tooltip/js/jquery.flot.tooltip.min.js"></script>
        <script src="js/pages/dashboards/dashboard1.js"></script>

        <!-- datatables JS -->
        <script src="assets/js/pages/dashboard-custom.js"></script>
        <script src="assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
        <script src="assets/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>

        <script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
        <script src="https://cdn.datatables.net/datetime/1.1.2/js/dataTables.dateTime.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.print.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.3.5/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.3.5/js/buttons.html5.min.js"></script>

        <script>
            var table;
            $(document).ready(function() {
                table = $('#example').DataTable({
                    aLengthMenu: [
                        [25, 50, 100, 200, -1],
                        [25, 50, 100, 200, "All"]
                    ],
                    dom: 'lBfrtip',
                    buttons: [
                        {
                            extend: 'csv',
                            footer: true,
                            title: 'Subscriber List',
                            exportOptions: {
                                    columns: [0, 1, 2]
                            }
                        }, {
                            extend: 'excel',
                            footer: true,
                            title: 'Subscriber List',
                            exportOptions: {
                                    columns: [0, 1, 2]
                            }
                        }, {
                            extend: 'pdf',
                            footer: true,
                            title: 'Subscriber List',
                            exportOptions: {
                                    columns: [0, 1, 2]
                            }
                        }, {
                            extend: 'print',
                            footer: true,
                            title: 'Subscriber List',
                            exportOptions: {
                                columns: [0, 1, 2]
                            }
                        }

                    ]
                });
                $('#example_filter input').addClass('myClass');
            });

            //delete subscriber from DB [START]
            function deleteSubscriber(id) {
                var result = confirm("Are you sure you want to delete this Subscriber?");
                if (result) {
                    $.ajax({
                        type: "GET",
                        url: "delete-subscriber.php",
                        data: {
                            deleteId: id
                        },
                        dataType: "html",
                        success: function(data) {
                            if (data == "deleted") {
                                alert("This Subscriber was successfully deleted!");
                                window.location.reload();
                            } else {
                                alert(data);
                            }

                        }
                    });
                }

            };
            //delete subscriber from DB [END]
        </script>

        <script type="text/javascript">
            //remove confirm form resubmission issue [START]
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
            //remove confirm form resubmission issue [END]
        </script>
    </body>

</php>