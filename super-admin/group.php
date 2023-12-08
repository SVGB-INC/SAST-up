<?php
ob_start();
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
// $mail = new PHPMailer(true);

include("../config.php");
require_once('header.php');

$error_msg = "NA";

//Check if session is set- or route to sign-in page
if (!isset($_SESSION["user_id"])) {
    header("Location: ../sign-in.php");
}

//getting the category list with data
$groupsQuery = "SELECT *, a.user_email as email from dt_users_group as a left join dt_user_info_group as b on b.user_ID=a.uID";
$groupsList = $pdo->query($groupsQuery)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $formName = $_POST["frmname"];

    if ($formName == "newform") { //this request is for inserting categories

        // get variables from front-end elements
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $user_email = $_POST['user_email'];
        $user_name = $_POST['user_name'];
        $user_comp_name = $_POST['user_comp_name'];
        $user_pass = random_str(12);

        try{
            //check for duplicate user names in the table
            $select_stmt = $pdo->prepare("SELECT user_name FROM dt_users_group WHERE user_name=:u_name");
            $select_stmt->execute(array(
                ':u_name' => $user_name,
            ));
            $row = $select_stmt->fetch(PDO::FETCH_ASSOC);

            if ($select_stmt->rowCount() > 0) {
                $error_msg = "This user name already exists. Kindly choose a new one.";
            } else {
                //check for duplicate user emails in the table
                $select_stmt = $pdo->prepare("SELECT user_email FROM dt_users_group WHERE user_name=:u_email");
                $select_stmt->execute(array(
                ':u_email' => $user_email,
                ));
                $row = $select_stmt->fetch(PDO::FETCH_ASSOC);

                if ($select_stmt->rowCount() > 0) {
                    $error_msg = "This user email already exists. Kindly choose a new one.";
                } else {

                    //inserting category into DB with the right values
                    $insertUser = $pdo->prepare("INSERT INTO dt_users_group(`user_name`, `user_pass`, `user_email`, `user_comp_name`) VALUES (?,?,?,?)");
                
                    if ($insertUser->execute([
                        $user_name, password_hash($user_pass, PASSWORD_DEFAULT), $user_email, $user_comp_name
                    ])) {
                        $id = $pdo->lastInsertId();
                        //insert the new unique user into the DB in user_ino as well.
                        $insert_stmt = $pdo->prepare("INSERT INTO dt_user_info_group(user_ID,first_name,last_name,user_email,user_employer) VALUES (?,?,?,?,?)");

                        $insert_stmt->execute([$id,$first_name,$last_name,$user_email,$user_comp_name]);
            
                        // try {
                        //     //Server settings
                        //     $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
                        //     $mail->isSMTP();                                            //Send using SMTP
                        //     $mail->Host       = 'smtp.example.com';                     //Set the SMTP server to send through
                        //     $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                        //     $mail->Username   = 'user@example.com';                     //SMTP username
                        //     $mail->Password   = 'secret';                               //SMTP password
                        //     $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                        //     $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
                        
                        //     //Recipients
                        //     $mail->setFrom('from@example.com', 'Mailer');
                        //     $mail->addAddress($group_email, 'Joe User');     //Add a recipient
                        //     //$mail->addAddress('ellen@example.com');               //Name is optional
                        //     //$mail->addReplyTo('info@example.com', 'Information');
                        //     //$mail->addCC('cc@example.com');
                        //     //$mail->addBCC('bcc@example.com');
                        
                        //     //Attachments
                        //     //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
                        //     //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
                        
                        //     //Content
                        //     $mail->isHTML(true);                                  //Set email format to HTML
                        //     $mail->Subject = "Credential for School AgEd";
                        //     $mail->Body    = 'Hi<br><br> Welcome to School AgEd App.<br> Here is your credentials:<br>User: '.$group_email.'<br>Password: '.$user_pass.'<br><br>Thanks<br>Webmaster';
                        //     //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
                        
                        //     $mail->send();
                        //     echo 'Message has been sent';
                        // } catch (Exception $e) {
                        //     echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                        // }
            
                        header("Location: group.php");
                    }
                }
            }
        }
        catch(PDOException $e){
            $error_msg = $e->getMessage();
        }
        
    } else if ($formName == "updateform") { //this request is for updating categories

        // get variables from front-end elements
        $first_name = $_POST['first_name'];

        // //inserting category into DB with the right values
        // $sql = "UPDATE dt_subscribers SET `first_name` = '$group_name' WHERE uID = $category_id;";
        // $stmt = $pdo->prepare($sql);
        // if ($stmt->execute()) {
        //     $check = move_uploaded_file($tempFile, $targetFile);
        //     header("Location: category.php");
        // }
    }
}

ob_end_flush();
?>

            <!-- Page wrapper  -->
            <!-- ============================================================== -->
            <div class="page-wrapper subscriber mt-3">
                <div class="container-fluid">
                    <div class="d-flex align-items-center mb-4">
                        <h5 class="fw-normal me-5 text-primary">Groups</h5>
                        <p nowrap></p>
                    </div>
                    <?php if ($error_msg != "NA") { ?>
                        <div class="alert alert-danger" role="alert">
                        <?= $error_msg ?>
                        </div>
                    <?php } ?>
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
                            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#exampleModal">New Group</button>
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
                                            <span></span>
                                        </div>
                                    </th>
                                    <th scope="col">Full Name</th>
                                    <th scope="col">Username</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Company</th>
                                    <th scope="col">Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                //echo "<pre>"; print_r($groupsList); exit;
                                foreach ($groupsList as $group) {
                                ?>
                                    <tr>
                                        <th scope="row" class="d-flex align-items-center">
                                            <input class="form-check-input border-0 me-4" type="checkbox" value="" id="flexCheckDefault">
                                            <!-- </div> -->
                                            <span class="d-flex align-items-center">
                                                <img src=<?php if (empty($group["user_img"]) || $group["user_img"] == "NA") {
                                                                echo "./assets/images/user_placeholder.png";
                                                            } else {
                                                                echo "../group-admin/" . $group["user_img"];
                                                            } ?> alt="user" class="img-fluid" width="50" />
                                            </span>
                                        </th>
                                        <td><a href='group-detail.php?uid=<?= $group["uID"] ?>' class="text-secondary"><?= $group["first_name"] ?> <?= $group["last_name"] ?></a></td>
                                        <td><a href='group-detail.php?uid=<?= $group["uID"] ?>' class="text-secondary"><?= $group["user_name"] ?? '' ?></a></td>
                                        <td><a href='group-detail.php?uid=<?= $group["uID"] ?>' class="text-secondary"><?= $group["email"] ?? '' ?></a></td>
                                        <td><?= $group["user_comp_name"] ?? '' ?></td>
                                        <td>
                                            <div class="edit-delete">
                                                <!-- <span class="pe-3 delete"><i class="bi bi-trash3" onclick="deleteGroup(<?= $group['uID'] ?>)"></i></span> -->
                                                <button class="btn btn-primary" onclick="deleteGroup(<?= $group['uID'] ?>,<?= $group['user_ID'] ?>)"><i class="bi bi-trash3"></i></button>
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
                                        <h4 class="fw-normal">New Group User</h4>
                                    </div>
                                    <div class="modal-body px-0">
                                        <div class="mb-2">
                                            <label for="first_name" class="form-label">First Name</label>
                                            <input required type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" name="first_name">
                                        </div>
                                    </div>
                                    <div class="modal-body px-0">
                                        <div class="mb-2">
                                            <label for="last_name" class="form-label">Last Name</label>
                                            <input required type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" name="last_name">
                                        </div>
                                    </div>
                                    <div class="modal-body px-0">
                                        <div class="mb-2">
                                            <label for="user_email" class="form-label">Email</label>
                                            <input required type="email" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" name="user_email">
                                        </div>
                                    </div>
                                    <div class="modal-body px-0">
                                        <div class="mb-2">
                                            <label for="user_comp_name" class="form-label">Company Name</label>
                                            <input required type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" name="user_comp_name">
                                        </div>
                                    </div>
                                    <div class="modal-body px-0">
                                        <div class="mb-2">
                                            <label for="user_comp_name" class="form-label">UserName</label>
                                            <input required type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" name="user_name">
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
        <?php require_once('footer.php'); ?>
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
            function deleteGroup(id,uid) {
                var result = confirm("Are you sure you want to delete this Group?");
                if (result) {
                    $.ajax({
                        type: "GET",
                        url: "delete-group.php",
                        data: {
                            deleteId: id,
                            deleteUid: uid
                        },
                        dataType: "html",
                        success: function(data) {
                            if (data == "deleted") {
                                alert("This Group was successfully deleted!");
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
