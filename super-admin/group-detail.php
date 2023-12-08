<?php
ob_start();
session_start();
$_SESSION;

include("../config.php");
require_once('header.php');

$error_msg = "NA";

//Check if session is set- or route to sign-in page
if (!isset($_SESSION["user_id"])) {
    header("Location: ../sign-in.php");
}
//getting user logo from info table
$userLogoQuery = "SELECT user_img from dt_user_info_group where user_ID = '" . $_SESSION["user_id"] . "';";
$userLogoList = $pdo->query($userLogoQuery)->fetch();
$subID = $_GET["uid"];

//getting subscriber details
$subQuery = "SELECT * from dt_user_info_group where uID = '" . $subID . "' LIMIT 1;";
$subList = $pdo->query($subQuery)->fetch();

//getting subscriber details
// $subTestQuery = "SELECT * from dt_subscribers_assignment where sub_ID = '" . $subID . "' and test_ID in (SELECT uID FROM dt_tests);";
// $subTestList = $pdo->query($subTestQuery)->fetchAll();

//getting test details
$testQuery = "SELECT uID, test_name from dt_tests;";
$testList = $pdo->query($testQuery)->fetchAll();



if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $formName = $_POST["frmname"];

    if ($formName == "newform") { //this request is for inserting categories

        // get variables from front-end elements
        $test_name = $_POST['test_name'];
        $due_date = $_POST['due_date'];


        //inserting category into DB with the right values
        $insertCategory = $pdo->prepare("INSERT INTO dt_subscribers_assignment(`sub_ID`, `test_ID`, `due_date`) VALUES (?,?,?)");

        if ($insertCategory->execute([
            $subID, $test_name, $due_date
        ])) {
            header("Location: subscriber-detail.php?uid=$subID");
        }
    } else if ($formName == "updateform") { //this request is for updating categories

        // // get variables from front-end elements
        // $subscriber_name = $_POST['subscriber_name'];

        // //inserting category into DB with the right values
        // $sql = "UPDATE dt_subscribers SET `subscriber_name` = '$subscriber_name' WHERE uID = $category_id;";
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
            <div class="page-wrapper">
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-md-8 col-sm-12">
                        <div class="col-md-11 col-sm-11">
                            <div class="my-3">
                                <h4>
                                    Activity
                                </h4>
                                <hr>
                            </div>
                            <div>
                                <h6 class="text-primary"><?= date('F') ?></h6>
                                <div class="d-flex justify-content-between align-items-lg-baseline">
                                    <div>
                                        <span><?= date('Y-M-D') ?> -> </span>
                                        <label class="fs-4 fw-bolder"><?php
                                                                        //get parent UID
                                                                        $parentUID = $subID;//$pdo->query("SELECT company_ID from dt_subscribers WHERE uID = '" . $_SESSION["user_id"] . "';")->fetchColumn();

                                                                        //getting the category list with data
                                                                        $topCategory = $pdo->query("SELECT uID from dt_categories_group where user_ID = '" . $parentUID . "' ORDER BY RAND() LIMIT 1;")->fetchColumn();

                                                                        $bookNow = $pdo->query("SELECT * from dt_books_group where cID = '" . $topCategory . "' ORDER BY RAND() LIMIT 1;")->fetch();

                                                                        if ($bookNow) { ?>
                                                <a href=<?= "../group-admin/category-note.php?bid=" . $bookNow["uID"] ?>><?= $bookNow["book"] ?></a>
                                            <?php } else { ?>
                                                <?php
                                                                            //get parent UID
                                                                            $parentUID = $subID;//$pdo->query("SELECT company_ID from dt_subscribers WHERE uID = '" . $_SESSION["user_id"] . "';")->fetchColumn();

                                                                            //getting the category list with data
                                                                            $topCategory = $pdo->query("SELECT uID from dt_categories_group where user_ID = '" . $parentUID . "' ORDER BY RAND() LIMIT 1;")->fetchColumn();

                                                                            $bookNow = $pdo->query("SELECT * from dt_books_group where cID = '" . $topCategory . "' ORDER BY RAND() LIMIT 1;")->fetch();

                                                                            if ($bookNow) { ?>
                                                    <a href=<?= "../group-admin/category-note.php?bid=" . $bookNow["uID"] ?>><?= $bookNow["book"] ?></a>
                                                <?php } ?>
                                            <?php }
                                            ?>
                                        </label>
                                    </div>
                                    <div>
                                        <?php
                                        if (rand(0, 1) == 0) { ?>
                                            <h6 class="text-success">Complete</h6>
                                        <?php } else { ?>
                                            <h6 class="text-danger">Incomplete</h6>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between align-items-lg-baseline">
                                    <div>
                                        <span><?= date('Y-M-D') ?> -> </span>
                                        <label class="fs-4 fw-bolder"><?php
                                                                        //get parent UID
                                                                        $parentUID = $subID;//$pdo->query("SELECT company_ID from dt_subscribers WHERE uID = '" . $_SESSION["user_id"] . "';")->fetchColumn();

                                                                        //getting the category list with data
                                                                        $topCategory = $pdo->query("SELECT uID from dt_categories_group where user_ID = '" . $parentUID . "' ORDER BY RAND() LIMIT 1;")->fetchColumn();

                                                                        $bookNow = $pdo->query("SELECT * from dt_books_group where cID = '" . $topCategory . "' ORDER BY RAND() LIMIT 1;")->fetch();

                                                                        if ($bookNow) { ?>
                                                <a href=<?= "../group-admin/category-note.php?bid=" . $bookNow["uID"] ?>><?= $bookNow["book"] ?></a>
                                            <?php } else { ?>
                                                <?php
                                                                            //get parent UID
                                                                            $parentUID = $subID;//$pdo->query("SELECT company_ID from dt_subscribers WHERE uID = '" . $_SESSION["user_id"] . "';")->fetchColumn();

                                                                            //getting the category list with data
                                                                            $topCategory = $pdo->query("SELECT uID from dt_categories_group where user_ID = '" . $parentUID . "' ORDER BY RAND() LIMIT 1;")->fetchColumn();

                                                                            $bookNow = $pdo->query("SELECT * from dt_books_group where cID = '" . $topCategory . "' ORDER BY RAND() LIMIT 1;")->fetch();

                                                                            if ($bookNow) { ?>
                                                    <a href=<?= "../group-admin/category-note.php?bid=" . $bookNow["uID"] ?>><?= $bookNow["book"] ?></a>
                                                <?php } ?>
                                            <?php }
                                            ?>
                                        </label>
                                    </div>
                                    <div>
                                        <?php
                                        if (rand(0, 1) == 0) { ?>
                                            <h6 class="text-success">Complete</h6>
                                        <?php } else { ?>
                                            <h6 class="text-danger">Incomplete</h6>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between align-items-lg-baseline">
                                    <div>
                                        <span><?= date('Y-M-D') ?> -> </span>
                                        <label class="fs-4 fw-bolder"><?php
                                                                        //get parent UID
                                                                        $parentUID = $subID;//$pdo->query("SELECT company_ID from dt_subscribers WHERE uID = '" . $_SESSION["user_id"] . "';")->fetchColumn();

                                                                        //getting the category list with data
                                                                        $topCategory = $pdo->query("SELECT uID from dt_categories_group where user_ID = '" . $parentUID . "' ORDER BY RAND() LIMIT 1;")->fetchColumn();

                                                                        $bookNow = $pdo->query("SELECT * from dt_books_group where cID = '" . $topCategory . "' ORDER BY RAND() LIMIT 1;")->fetch();

                                                                        if ($bookNow) { ?>
                                                <a href=<?= "../group-admin/category-note.php?bid=" . $bookNow["uID"] ?>><?= $bookNow["book"] ?></a>
                                            <?php } else { ?>
                                                <?php
                                                                            //get parent UID
                                                                            $parentUID = $subID;//$pdo->query("SELECT company_ID from dt_subscribers WHERE uID = '" . $_SESSION["user_id"] . "';")->fetchColumn();

                                                                            //getting the category list with data
                                                                            $topCategory = $pdo->query("SELECT uID from dt_categories_group where user_ID = '" . $parentUID . "' ORDER BY RAND() LIMIT 1;")->fetchColumn();

                                                                            $bookNow = $pdo->query("SELECT * from dt_books_group where cID = '" . $topCategory . "' ORDER BY RAND() LIMIT 1;")->fetch();

                                                                            if ($bookNow) { ?>
                                                    <a href=<?= "../group-admin/category-note.php?bid=" . $bookNow["uID"] ?>><?= $bookNow["book"] ?></a>
                                                <?php } ?>
                                            <?php }
                                            ?>
                                        </label>
                                    </div>
                                    <div>
                                        <?php
                                        if (rand(0, 1) == 0) { ?>
                                            <h6 class="text-success">Complete</h6>
                                        <?php } else { ?>
                                            <h6 class="text-danger">Incomplete</h6>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                            <!-- <div class="d-flex justify-content-between">
                                <div>
                                    <h5>Tests Assigned</h5>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Assignment</button>
                                </div>
                            </div> -->
                            <!-- <div class="Test mt-2 pt-1">
                                <h6 class="my-3">Testing</h6>
                                <table class="table">
                                    <thead>
                                        <?php foreach ($subTestList as $testNow) {
                                            //getting test
                                            $testQuery = "SELECT test_name from dt_tests_group where uID = '" . $testNow["test_ID"] . "' LIMIT 1;";
                                            $testListNow = $pdo->query($testQuery)->fetch();

                                            //getting test questions
                                            $testQuestionQuery = "SELECT test_question from dt_test_questions_group where test_id = '" . $testNow["test_ID"] . "' LIMIT 1;";
                                            $testQuestionNow = $pdo->query($testQuestionQuery)->fetch();
                                        ?>
                                            <tr>
                                                <th scope="col" class="text-primary"><a href="testing.php?tid=<?= $testNow["test_ID"] ?>"><?= $testListNow["test_name"] ?></a></th>
                                                <th scope="col"><?= !$testQuestionNow ? "NA" : $testQuestionNow['test_question'] ?></th>
                                            </tr>
                                        <?php } ?>
                                    </thead>
                                </table>
                            </div> -->
                        </div>
                        <div class="col-md-4 col-sm-12 border-start ps-4">
                            <div class="d-flex justify-content-center flex-column align-items-center">
                                <img style="width: 100px;height:100px;border-radius:50%; object-fit:cover;" src=<?php if (empty($subList["user_img"]) || $subList["user_img"] == "NA") {
                                    echo "./assets/images/user_placeholder.png";
                                } else {
                                    echo "../group-admin/" . $subList["user_img"];
                                } ?> class="rounded-circle w-25 image-fluid mb-3" alt="user">
                                <h5><?= ($subList["first_name"] ?? 'No').' '.($subList["last_name"] ?? 'Name') ?></h5>
                                <h6><?= $subList["user_occupation"] ?? '' ?></h6>
                                <!-- <h6><a href="#">User Setting</a></h6> -->
                            </div>
                            <div class="row my-5 border-top border-bottom">
                                <div class="col-md-4 col-xs-12 text-center border-end d-flex justify-content-center align-items-center ">
                                    <div class="p-2">
                                        <h5><?php
                                            //getting tests assigned
                                            $assignQuery = "SELECT count(*) from dt_subscribers_assignment where sub_ID = '" . $subID . "' and test_ID in (SELECT uID FROM dt_tests_group) LIMIT 1;";
                                            $assignList = $pdo->query($assignQuery)->fetch();
                                            ?>
                                            <h5><?php if ($assignList <= 0) {
                                                    echo "0";
                                                } else {
                                                    echo $assignList[0];
                                                }  ?></h5>
                                            <p class="fs-4">Tests Assigned</p>
                                    </div>

                                </div>
                                <div class="col-md-4 col-xs-12 text-center border-end d-flex justify-content-center align-items-center ">
                                    <div class="p-2">
                                        <?php
                                        //getting tests assigned
                                        $assignQuery = "SELECT count(*) from dt_subscribers_assignment where sub_ID = '" . $subID . "' and test_ID in (SELECT uID FROM dt_tests_group) LIMIT 1;";
                                        $assignList = $pdo->query($assignQuery)->fetch();
                                        ?>
                                        <h5><?php if ($assignList <= 0) {
                                                echo "0";
                                            } else {
                                                echo $assignList[0];
                                            }  ?></h5>
                                        <p class="fs-4">Test Taken</p>
                                    </div>
                                </div>
                                <div class="col-md-4 col-xs-12 text-center d-flex justify-content-center align-items-center ">
                                    <div class="p-2">
                                        <h5><?php

                                            $passCount = 0;
                                            $failCount = 0;
                                            $totCount = 0;

                                            $testListNow = $pdo->query("SELECT * FROM `dt_tests_group` WHERE uID IN (SELECT test_ID FROM dt_subscribers_assignment where sub_ID = '" . $subID . "')")->fetchAll(PDO::FETCH_ASSOC);
                                            $userListNow = $pdo->query("SELECT sb.uID, sb.subscriber_name, sa.due_date, tg.test_name, 'In Progress' AS `status`
FROM `dt_subscribers_assignment` AS sa 
INNER JOIN `dt_subscribers` AS sb ON sa.sub_ID = sb.uID
INNER JOIN `dt_tests_group` AS tg ON sa.test_ID = tg.uID 
where sa.sub_ID = '" . $subID . "' ")->fetchAll();

                                            foreach ($testListNow as $testNow) {
                                                foreach ($userListNow as $userNow) {
                                                    $testID = $testNow["uID"];
                                                    $totQuestions = $pdo->query("SELECT COUNT(*) FROM dt_test_questions_group WHERE test_id = '" . $testID . "';")->fetchColumn();

                                                    if ($totQuestions > 0) {
                                                        $ansQuestions = $pdo->query("SELECT COUNT(*) FROM dt_test_questions_answers_group WHERE user_ID = '" . $userNow["uID"] . "' AND question_id IN (SELECT uID FROM dt_test_questions_group WHERE test_id = '" . $testID . "') AND answer != '';")->fetchColumn();

                                                        $testRatio = $pdo->query("SELECT test_ratio FROM dt_tests_group WHERE uID = " . $testID . ";")->fetchColumn();

                                                        $correctQuestions = $pdo->query("SELECT count(*)
FROM dt_test_questions_group 
INNER JOIN dt_test_questions_answers_group
ON dt_test_questions_group.uID = dt_test_questions_answers_group.question_id 
WHERE dt_test_questions_group.test_id = '" . $testID . "' 
AND dt_test_questions_answers_group.user_ID = '" . $userNow["uID"] . "' 
AND dt_test_questions_group.correct_answer = dt_test_questions_answers_group.answer")->fetchColumn();

                                                        $correctRatio = ($correctQuestions / $totQuestions) * 100;
                                                        $statusPassFail = 'NA';
                                                        if (intval($correctRatio) >= intval($testRatio)) {
                                                            $passCount += 1;
                                                        } else {
                                                            $failCount += 1;
                                                        }
                                                        $totCount += 1;
                                                    }
                                                }
                                            }
                                            if ($totCount > 0) {
                                                echo ($passCount / $totCount) * 100;
                                            } else {
                                                echo 0;
                                            }
                                            ?>%</h5>
                                        <p class="fs-4">Test Average</p>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                    </div>
                </div>

                <!-- MODAL START -->
                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content p-5" style="border-radius:20px;">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="model-head">
                                    <h4 class="fw-normal">Assign Test</h4>
                                </div>
                                <div class="modal-body px-0">
                                    <div class="mb-2">
                                        <label for="subscriber_name" class="form-label">Test List</label>
                                        <!-- <input required type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" name="subscriber_name"> -->
                                        <select required class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" name="test_name">
                                            <option value="">Choose A Test</option>
                                            <?php foreach ($testList as $test) { ?>
                                                <option value="<?= $test['uID'] ?>"><?= $test['test_name'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="mb-2"><input class="form-control" type="date" value="" id="newdate" name="due_date"></div>

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
        <script type="text/javascript">
            //remove confirm form resubmission issue [START]
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
            //remove confirm form resubmission issue [END]
        </script>
    </body>

</php>