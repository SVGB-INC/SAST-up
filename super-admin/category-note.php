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


$bookID = $_GET["bid"];

$chapIDNow = "NA";
$topicIDNow = "NA";

if (isset($_GET["chapID"])) {
    $chapIDNow = $_GET["chapID"];
}
if (isset($_GET["tID"])) {
    $topicIDNow = $_GET["tID"];
}

if ($chapIDNow == "") {
    $chapIDNow = "NA";
}
if ($topicIDNow == "") {
    $topicIDNow = "NA";
}


$chapIDNowClear = "NA";
$topicIDNowClear = "NA";

if (isset($_GET["filterChap"])) {
    $chapIDNowClear = $_GET["filterChap"];
}
if (isset($_GET["filterTopic"])) {
    $topicIDNowClear = $_GET["filterTopic"];
}

if ($chapIDNowClear == "") {
    $chapIDNowClear = "NA";
}
if ($topicIDNowClear == "") {
    $topicIDNowClear = "NA";
}


//getting the chapter list with data -- all chapters loaded
$chapterQuery = "SELECT * from dt_chapters_super where book_ID = '" . $bookID . "';";
$chapterList = $pdo->query($chapterQuery)->fetchAll();


//getting the topic list with data -- all topics loaded for all chapters
$topicQuery = "SELECT * from dt_topics_super where chapter_ID IN (SELECT uID from dt_chapters_super where book_ID = '" . $bookID . "');";
$topicList = $pdo->query($topicQuery)->fetchAll(PDO::FETCH_ASSOC);



//checking for filters 
if (isset($_GET["filterChap"])) {
    //getting the chapter list with data -- all chapters loaded
    $chapterQuery = "SELECT * from dt_chapters_super where book_ID = '" . $bookID . "' AND chapter_name LIKE '%" . $_GET["filterChap"] . "%';";
    $chapterList = $pdo->query($chapterQuery)->fetchAll();
}
if (isset($_GET["filterTopic"])) {
    //getting the topic list with data -- all topics loaded for all chapters
    $topicQuery = "SELECT * from dt_topics_super where chapter_ID IN (SELECT uID from dt_chapters_super where book_ID = '" . $bookID . "') AND topic_name LIKE '%" . $_GET["filterTopic"] . "%'";
    $topicList = $pdo->query($topicQuery)->fetchAll(PDO::FETCH_ASSOC);

    //getting the chapter list with data -- all chapters loaded -- according to the topic
    $chapterQuery = "SELECT * from dt_chapters_super where book_ID = '" . $bookID . "' AND uID IN (SELECT chapter_ID from dt_topics_super where chapter_ID IN (SELECT uID from dt_chapters_super where book_ID = '" . $bookID . "') AND topic_name LIKE '%" . $_GET["filterTopic"] . "%');";
    $chapterList = $pdo->query($chapterQuery)->fetchAll();
}
if (isset($_GET["filterTopic"]) && isset($_GET["filterChap"])) {
    //getting the chapter list with data -- all chapters loaded
    $chapterQuery = "SELECT * from dt_chapters_super where book_ID = '" . $bookID . "' AND chapter_name LIKE '%" . $_GET["filterChap"] . "%';";
    $chapterList = $pdo->query($chapterQuery)->fetchAll();

    //getting the topic list with data -- all topics loaded for all chapters
    $topicQuery = "SELECT * from dt_topics_super where topic_name LIKE '%" . $_GET["filterTopic"] . "%' AND chapter_ID IN (SELECT uID from dt_chapters_super where book_ID = '" . $bookID . "' AND chapter_name LIKE '%" . $_GET["filterChap"] . "%');";
    $topicList = $pdo->query($topicQuery)->fetchAll(PDO::FETCH_ASSOC);
}


//getting the book name
$bookQuery = "SELECT book, book_author from dt_books_super where uID = '" . $bookID . "' LIMIT 1;";
$bookList = $pdo->query($bookQuery)->fetch();

//getting the category name
$categoryQuery = "SELECT category, uID from dt_categories_super where uID = (select cID from dt_books_super where uID = '" . $bookID . "') LIMIT 1;";
$categoryList = $pdo->query($categoryQuery)->fetch();

//getting the questions and answers
$questionQuery = "SELECT uID, test_id, test_topic, test_question, correct_answer from dt_test_questions_super;";
$questionList = $pdo->query($questionQuery)->fetchAll(PDO::FETCH_ASSOC);
$answersQuery = "SELECT question_id, answer from dt_test_questions_answers_super where user_ID = '" . $_SESSION["user_id"] . "';";
$answersList = $pdo->query($answersQuery)->fetchAll(PDO::FETCH_ASSOC);



if ($_SERVER['REQUEST_METHOD'] === "POST") {

    //functions for topics [START]
    if (!empty($_POST['topic'])) {

        $formName = $_POST["frmname"];

        if ($formName == "newFrm") { //this request is for inserting new topics

            // get variables from front-end elements
            $page_number = $_POST["page_number"];
            $book_notes = addslashes($_POST["book_notes"]);
            $page_url = $_POST["page_url"];
            $web_notes = addslashes($_POST["web_notes"]);
            $video_note_url = $_POST["video_note_url"];
            $video_notes = addslashes($_POST["video_notes"]);
            $pdf_notes_file = $_FILES["pdf_notes"];
            $source_ref_link = $_POST["source_ref_link"];
            $topic_question = addslashes($_POST["topic_question"]);
            $topic_question_notes = addslashes($_POST["topic_question_notes"]);
            $chapterID = $_POST["selectedChapterID"];
            $topic_name = addslashes($_POST["topic_name"]);

            //Upload PDF
            // Define upload path and URL
            $target_dir = "uploads/";
            $target_file = $target_dir . time().'-'.str_replace(' ','-',basename($pdf_notes_file["name"]));

            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
            
            // Check if file is a PDF
            if($imageFileType != "pdf") {
                $error_msg = "Sorry, only PDF files are allowed.";
                $uploadOk = 0;
            }
            
            // Check if file already exists
            if (file_exists($target_file)) {
                $error_msg = "Sorry, file already exists.";
                $uploadOk = 0;
            }
            
            // Check file size
            if ($pdf_notes_file["size"] > 50000000) {
                $error_msg = "Sorry, your file is too large.";
                $uploadOk = 0;
            }
            
            // Upload file
            if ($uploadOk != 0) {
                if (move_uploaded_file($pdf_notes_file["tmp_name"], $target_file)) {
                    $error_msg = "The file ". basename( $pdf_notes_file["name"]). " has been uploaded.";
                    $pdf_notes = $target_file;
                } else {
                    $error_msg = "Sorry, there was an error uploading your file.";
                }
            }

            //inserting chapter into DB with the right values
            $insertTopic = $pdo->prepare("INSERT INTO dt_topics_super(`chapter_ID`, `page_number`, `book_notes`, `page_url`, `web_notes`, `video_note_url`, `video_notes`, `pdf_notes`, `source_ref_link`, `topic_question`, `topic_question_notes`, `topic_name`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");

            if ($insertTopic->execute([
                $chapterID, $page_number, $book_notes, $page_url, $web_notes, $video_note_url, $video_notes, $pdf_notes, $source_ref_link, $topic_question, $topic_question_notes, $topic_name
            ])) {
                header("Location: category-note.php?bid=" . $bookID);
            }
        } else if ($formName == "updateFrm") { //this request is for updating topics

            // get variables from front-end elements
            $topic_id = $_POST["topic_id_update"];
            $page_number = $_POST["page_number_update"];
            $book_notes = addslashes($_POST["book_notes_update"]);
            $page_url = $_POST["page_url_update"];
            $web_notes = addslashes($_POST["web_notes_update"]);
            $video_note_url = $_POST["video_note_url_update"];
            $video_notes = addslashes($_POST["video_notes_update"]);
            $pdf_notes_file = $_FILES["pdf_notes_update"];
            $source_ref_link = $_POST["source_ref_link_update"];
            $topic_question = addslashes($_POST["topic_question_update"]);
            $topic_question_notes = addslashes($_POST["topic_question_notes_update"]);
            $topic_name = addslashes($_POST["topic_name_update"]);

            $update_query = "UPDATE dt_topics_super SET `page_number` = '" . rtrim($page_number, ' ') . "', `book_notes` = '" . rtrim($book_notes, ' ') . "', `page_url` = '" . rtrim($page_url, ' ') . "', `web_notes`= '" . rtrim($web_notes, ' ') . "', `video_note_url` = '" . rtrim($video_note_url, ' ') . "', `video_notes` = '" . rtrim($video_notes, ' ') . "', `source_ref_link` = '" . rtrim($source_ref_link, ' ') . "', `topic_question` = '" . rtrim($topic_question, ' ') . "', `topic_question_notes` = '" . rtrim($topic_question_notes, ' ') . "', `topic_name` = '" . rtrim($topic_name, ' ')."'";

            //Upload PDF
            if($pdf_notes_file["size"]>0){
                // Define upload path and URL
                $target_dir = "uploads/";
                $target_file = $target_dir . time().'-'.str_replace(' ','-',basename($pdf_notes_file["name"]));

                $uploadOk = 1;
                $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
                
                // Check if file is a PDF
                if($imageFileType != "pdf") {
                    $error_msg = "Sorry, only PDF files are allowed.";
                    $uploadOk = 0;
                }
                
                // Check if file already exists
                if (file_exists($target_file)) {
                    $error_msg = "Sorry, file already exists.";
                    $uploadOk = 0;
                }
                
                // Check file size
                if ($pdf_notes_file["size"] > 50000000) {
                    $error_msg = "Sorry, your file is too large.";
                    $uploadOk = 0;
                }
                
                // Upload file
                if ($uploadOk != 0) {
                    if (move_uploaded_file($pdf_notes_file["tmp_name"], $target_file)) {
                        $error_msg = "The file ". basename( $pdf_notes_file["name"]). " has been uploaded.";
                        $pdf_notes = $target_file;
                        $update_query .= ", `pdf_notes` = '" . rtrim($pdf_notes, ' ') . "'";
                    } else {
                        $error_msg = "Sorry, there was an error uploading your file.";
                    }
                }

            }

            $update_query .= " WHERE uID = $topic_id";

           //inserting chapter into DB with the right values
            $insertTopic = $pdo->prepare($update_query);

            if ($insertTopic->execute()) {
                header("Location: category-note.php?bid=" . $bookID);
            }
        }
    }
    //functions for topics [END]

    //functions for chapters [START]
    if (!empty($_POST['chapter'])) {

        $formName = $_POST["frmname"];

        if ($formName == "newFrm") { //this request is for inserting new books

            // get variables from front-end elements
            $chapter_name = $_POST['chapter_name'];
            $chapter_number = $_POST['chapter_number'];

            //inserting chapter into DB with the right values
            $insertChapter = $pdo->prepare("INSERT INTO dt_chapters_super(`book_ID`, `chapter_name`, `chapter_number`) VALUES (?,?,?)");

            if ($insertChapter->execute([
                $bookID, $chapter_name, $chapter_number
            ])) {
                header("Location: category-note.php?bid=" . $bookID);
            }
        } else if ($formName == "updateFrm") { //this request is for updating books

            // get variables from front-end elements
            $chapter_id = $_POST['chapter_id_update'];
            $chapter_name = $_POST['chapter_name_update'];
            $chapter_number = $_POST['chapter_number_update'];

            //inserting chapter into DB with the right values
            $insertChapter = $pdo->prepare("UPDATE dt_chapters_super SET `chapter_name` = '" . rtrim($chapter_name, '') . "', `chapter_number` = '" . rtrim($chapter_number, ' ') . "' WHERE uID = $chapter_id");

            if ($insertChapter->execute()) {
                header("Location: category-note.php?bid=" . $bookID);
            }
        }
    }
    //functions for chapters [END]
}

ob_end_flush();
?>
<!-- Page wrapper  -->
<!-- ============================================================== -->
<div class="page-wrapper">
    <div class="container-fluid">
        <!-- Cover-img  -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="background:#ffff !important">
                <li class="breadcrumb-item"><a href="material.php"><?= $categoryList[0] ?></a></li>
                <li class="breadcrumb-item"><a href="topic-category.php?cid=<?= $categoryList[1]  ?>"><?= $bookList[0] ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Chapter & Topic</li>
            </ol>
        </nav>
        <div class="category-img">
        </div>
        <!-- Title  -->
        <div class="my-3 category-title">
            <h5><?= $categoryList[0] ?></h5>
            <h2><?= $bookList[0] ?></h2>
        </div>
        <!-- Form  -->
        <div class="category-input">
            <div class="row my-5">
                <div class="col-md-3">
                    <select id="chapterList" class="form-select my-2" aria-label="Default select example" onchange="chapterChange(this.value)">
                        <?php foreach ($chapterList as $chapter) { ?>
                            <option value="<?= $chapter['uID'] ?>">Chapter <?= $chapter['chapter_number'] ?>: <?= $chapter['chapter_name'] ?></option>
                        <?php  }  ?>
                    </select>
                </div>
                <div class="col-md-1 d-flex justify-content-start">
                    <div class="edit-delete">
                        <input hidden id="editChapterID" name="editChapterID" type="text" value="<?= $chapterList[0][0] ?>">
                        <span class="pe-2 edit"><i name="editChapter" id="editChapter" class="bi bi-pencil-square" onclick="editChapterFunc()"></i></span>
                        <span class="pe-3 delete"><i name="delChapter" id="delChapter" class="bi bi-trash3" onclick="delChapterFunc()"></i></span>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group my-2">
                        <span class="input-group-text bg-transparent "><i class="bi bi-search"></i></span>
                        <input type="text" id="filterParamChap" class="form-control border-start-0 border-end-0" placeholder="Search Chapter" aria-label="Amount (to the nearest dollar)">
                        <span class="input-group-text bg-transparent"><i class="bi bi-mic-fill"></i></span>
                    </div>
                </div>
                <div class="col-md-1"><button type="button" class="btn btn-outline-primary w-100 my-2" onclick="filterClick()"><?= $chapIDNowClear != "NA" ? "Clear" : "Filter" ?></button>
                </div>
                <div class="col-md-2"><button class="btn btn-primary w-100 my-2 text-uppercase" type="button" data-bs-toggle="modal" data-bs-target="#chapterModal">NEW Chapter</button></div>
                <div hidden class="col-md-2"><button id="updateBtnChapter" class="btn btn-primary w-100 my-2 text-uppercase" type="button" data-bs-toggle="modal" data-bs-target="#updateChapterModal">UPDATE CHAPTER</button></div>
                <div class="col-md-3">
                    <select id="topicList" class="form-select my-2" aria-label="Default select example" onchange="topicChange(this.value)">
                        <?php foreach ($topicList as $topic) {
                            if ($topic['chapter_ID'] == $chapterList[0][0]) {
                        ?>
                                <option value="<?= $topic['uID'] ?>"><?= $topic['topic_name'] ?></option>
                        <?php }
                        }  ?>
                    </select>
                </div>
                <div class="col-md-1  d-flex justify-content-start">
                    <div class="edit-delete">
                        <input hidden id="editTopicID" name="editTopicID" type="text" value="<?= $topicList[0][0] ?>">
                        <span class="pe-2 edit"><i name="editTopic" id="editTopic" class="bi bi-pencil-square" onclick="editTopicFunc()"></i></span>
                        <span class="pe-3 delete"><i name="delTopic" id="delTopic" class="bi bi-trash3" onclick="delTopicFunc()"></i></span>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group my-2">
                        <span class="input-group-text bg-transparent "><i class="bi bi-search"></i></span>
                        <input type="text" id="filterParamTopic" class="form-control border-start-0 border-end-0" placeholder="Search Topic" aria-label="Amount (to the nearest dollar)">
                        <span class="input-group-text bg-transparent"><i class="bi bi-mic-fill"></i></span>
                    </div>
                </div>
                <div class="col-md-1"><button type="button" class="btn btn-outline-primary w-100 my-2" onclick="filterClick()"><?= $topicIDNowClear != "NA" ? "Clear" : "Filter" ?></button>
                </div>
                <div class="col-md-2"><button class="btn btn-primary w-100 my-2 text-uppercase" type="button" data-bs-toggle="modal" data-bs-target="#exampleModal">NEW Topic</button>
                </div>
                <div hidden class="col-md-2"><button id="updateBtnTopic" class="btn btn-primary w-100 my-2 text-uppercase" type="button" data-bs-toggle="modal" data-bs-target="#updateTopicModal">UPDATE TOPIC</button></div>

            </div>
            <!--META Table Start-->
            <?php if ($error_msg != "NA") { ?>
                        <div class="alert alert-danger" role="alert">
                        <?= $error_msg ?>
                        </div>
                    <?php } ?>
            <div class="table-responsive">
                <table class="table table-bordered">

                    <thead style="background-color: #a88f59;">
                        <tr>
                            <th style="text-align:center" class="text-white" scope="col">Subject Name</th>
                            <th style="text-align:center" class="text-white" scope="col">Book Details</th>
                            <th style="text-align:center" class="text-white" scope="col">Chapter Info</th>
                            <th style="text-align:center" class="text-white" scope="col">Topic Info</th>
                            <!-- <th style="text-align:center" class="text-white" scope="col">Question</th> -->

                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="vertical-align : middle;text-align:center;" scope="row" rowspan="2"><strong>Subject Title:</strong> <?= $categoryList[0] ?></td>
                            <td><strong>Book Title:</strong> <?= $bookList[0] ?></td>
                            <td><strong>Chapter Number:</strong> <span id="chapterTableNumber"><?php if (!isset($chapterList[0]["chapter_number"])) {
                                                                                                    echo "NA";
                                                                                                } else {
                                                                                                    echo $chapterList[0]["chapter_number"];
                                                                                                } ?></span></td>
                            <td><strong>Title:</strong> <span id="topicTableName"><?php if (!isset($topicList[0]['topic_name'])) {
                                                                                        echo "NA";
                                                                                    } else {
                                                                                        echo $topicList[0]['topic_name'];
                                                                                    } ?></span></td>
                            <!-- <td style="vertical-align : middle;text-align:center;" rowspan="2"><strong>Correct/Incorrect</strong></td> -->
                        </tr>
                        <tr>
                            <td><strong>Book Author:</strong> <?= $bookList[1] ?></td>
                            <td rowspan="2"><strong>Title:</strong> <span id="chapterTableName"><?php if (!isset($chapterList[0][2])) {
                                                                                                    echo "NA";
                                                                                                } else {
                                                                                                    echo $chapterList[0][2];
                                                                                                } ?></span></td>
                            <td><strong>Page Number:</strong> <span id="topic_PageNum">0</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!--META Table END-->

            <!--REAL Table-->
            <div class="row border m-0 mt-5 border-dark">
                <div class="col-md-3 border-top p-0 border-end" style="background-color: #a88f59;">
                    <div class="p-2 ">
                        <p class="fw-normal text-white ms-2 mb-0">Book Notes</p>
                    </div>
                    <ul class="list-unstyled p-2">
                        <!-- <li class="ms-3 fw-normal text-white">1</li> -->
                    </ul>
                </div>

                <!-- <div class="col-md-9 border-top p-0 ">
                        <ul class=" p-2">
                           <p class="fw-normal ms-2 mb-0" id="topic_bookNotes">NA</p>
                        
                        </ul>
                     </div> -->

                <div class="col-md-9 border-top p-0 ">
                    <ul class="p-2" id="topic_bookNotes">
                        <!-- <li class="ms-5 fw-normal"> Details</li> -->
                    </ul>
                </div>

                <div class="col-md-3 border-top p-0 border-end" style="background-color: #a88f59;">
                    <div class="p-2">
                        <p class="fw-normal m-0 text-white mb-0">Web Notes: Page URL</p>
                    </div>
                </div>
                <div class="col-md-9 border-end d-flex justify-content-center border-top p-0" style="background-color: #a88f59;">
                    <div class="p-2">
                        <p class="fw-normal m-0 mb-0"><a href="#" target="_blank" class="text-white" style="text-align:left" id="topic_webNotesURL">www.xyz.com</a>
                        </p>
                    </div>
                </div>
                <!-- <div class="col-md-3 border-top  p-0" style="background-color: #a88f59;">
                        <div class="p-2">
                        </div>
                     </div> -->


                <div class="col-md-3 col-height border-top p-0 border-end" style="background-color: #a88f59;">
                    <div class="p-2 ">
                        <p class="fw-normal text-white m-0 mb-0">Web Notes</p>
                    </div>
                </div>

                <!-- <div class="col-md-9 border-top p-0 ">
                        <ul class=" p-2">
                           <p class="fw-normal ms-2 mb-0" id="topic_webNotes">NA</p>                           
                        </ul>
                     </div> -->

                <div class="col-md-9 border-top p-0 ">
                    <ul class=" p-2" id="topic_webNotes">
                        <!-- <p class="fw-normal ms-2 mb-0" id="topic_webNotes">NA</p> -->
                        <!-- <li class="ms-5 fw-normal">Details</li> -->
                    </ul>
                </div>


                <div class="col-md-3 border-top p-0 border-end  d-flex align-items-center" style="background-color: #a88f59;">
                    <div class="p-2 ">
                        <p class="fw-normal text-white mb-0">Video Notes: Page URL</p>
                    </div>
                </div>
                <div class="col-md-9 border-top border-end p-0 d-flex align-items-center justify-content-center " style="background-color: #a88f59;">
                    <div class="p-2">
                        <p class="fw-normal m-0 mb-0"><a href="#" target="_blank" class=" text-white" id="topic_videoURL">www.xyz.com</a>
                        </p>
                    </div>
                </div>
                <!-- <div class="col-md-3 border-top p-0 d-flex align-items-center" style="background-color: #a88f59;">
                        <div class="p-2">
                        </div>
                     </div> -->

                <div class="col-md-3 border-top border-end p-0 " style="background-color: #a88f59;">
                    <div class="p-2 ">
                        <p class="fw-normal m-0 text-white mb-0"> Video Notes</p>
                    </div>
                </div>
                <div class="col-md-4 border-top border-end p-0  ">
                    <div class="p-2">
                        <!-- <p class="fw-normal m-0"><a href="#" class="text-body">
                                 Thumbnail Image:</a>
                           </p> -->
                        <iframe width="420" height="315" id="topic_videoURL_live">
                        </iframe>

                    </div>
                </div>
                <div class="col-md-5 border-top p-0  ">
                    <!-- <div class="p-2">
                           <p class="fw-normal ms-2"><strong>Main Point</strong></p>
                        </div> -->
                    <ul class=" p-2">
                        <p class="fw-normal ms-2 mb-0" id="topic_videoNotes">NA</p>
                        <!-- <li class="ms-5 fw-normal"> Details</li> -->
                    </ul>
                </div>

                <div class="col-md-3 border-top border-end p-0 " style="background-color: #a88f59;">
                    <div class="p-2 ">
                        <p class="fw-normal m-0 text-white mb-0"> PDF Notes</p>
                    </div>
                </div>
                <div class="col-md-4 border-top border-end p-0  ">
                    <div class="p-2">
                        <!-- <p class="fw-normal m-0"><a href="#" class="text-body">
                                 Thumbnail Image:</a>
                           </p> -->
                           <embed type='application/pdf' width='100%' height='300px' id="topic_pdf_live">

                    </div>
                </div>
                <div class="col-md-5 border-top p-0  ">
                    <!-- <div class="p-2">
                           <p class="fw-normal ms-2"><strong>Main Point</strong></p>
                        </div> -->
                    <ul class=" p-2">
                        <p class="fw-normal ms-2 mb-0" id="topic_pdfNotes">NA</p>
                       
                    </ul>
                </div>


                <div class="col-md-3 border-top border-end p-0 " style="background-color: #a88f59;">
                    <div class="p-2">
                        <p class="fw-normal m-0 text-white mb-0">Topic Question</p>
                    </div>
                </div>
                <div class="col-md-9 border-end border-top p-0" style="background-color: #a88f59;">
                    <div class="p-2">
                        <p class="fw-normal m-0 mb-0"><a href="#" class="text-white" id="topic_question">Sample Question</a>
                        </p>
                    </div>
                </div>
                <!-- <div class="col-md-2 border-top p-0" style="background-color: #a88f59;">
                        <div class="p-2">
                        </div>
                     </div> -->

                <div class="col-md-3 border-top  border-end  col-height " style="background-color: #a88f59;">
                    <div class="">
                        <p class="fw-normal text-white mb-0">Topic Notes</p>
                    </div>
                </div>
                <div class="col-md-9 border-top border-end p-0 ">
                    <div class="p-2 ">
                        <p class="fw-normal ms-2 mb-0" id="topic_question_notes">NA</p>
                    </div>
                </div>
                <div class="col-md-3 border-top p-0 " style="background-color: #a88f59;">
                    <div class="px-2 py-3">
                        <div class="d-flex justify-content-between">
                            <h5 id="studyQuestion" class="text-white fw-normal text-white">NA</h5>
                            <!-- <h5 class="text-white fw-normal"><a href="testing-main.php" class="text-white">What are mortgages?</a></h5> -->
                            <!-- <a href="" class="text-white text-decoration-underline">View Test</a> -->
                        </div>

                        <div class="mb-0 pt-2">
                            <!-- <h5 class="text-white pb-3 fw-normal">Question 4 What are mortgages 4 What are mortgages
                              </h5> -->
                            <h6 id="studyStatus" hidden class="card-subtitle pb-3 text-danger fw-bold">Incorrect</h6>
                            <a href="#" id="studyLink" hidden class="btn card-btn btn-primary bg-white" style="color: #a88f59!important;">STUDY</a>
                        </div>
                    </div>
                </div>
            </div>
            <!--REAL Table END-->
        </div>

        <!-- modal new topic -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content p-5" style="border-radius:20px;">
                    <form method="POST" enctype="multipart/form-data">
                        <input hidden id="selectedChapter" name="selectedChapterID" type="text" value="<?= $chapterList[0][0] ?>">
                        <div class="model-head">
                            <h4 class="fw-normal">New Topic</h4>
                        </div>
                        <div class="modal-body px-0">
                            <div class="mb-3">
                                <label for="topic_name" class="form-label">Topic Name</label>
                                <input type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="topic_name" name="topic_name">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Book: Page Number</label>
                                <input type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" name="page_number">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlTextarea1" class="form-label">Book Notes</label>
                                <textarea class="form-control" id="exampleFormControlTextarea1" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" rows="4" name="book_notes"></textarea>
                                <!-- <div class="editable form-control" contenteditable="true" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px; overflow-y:auto; min-height:9em" name="book_notes">
                                    <ul id="book_notes_ul">
                                       <li></li>
                                    </ul>
                                 </div> -->
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Page URL</label>
                                <input type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" name="page_url">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Web Notes
                                </label>
                                <textarea class="form-control" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" rows="4" id="exampleFormControlInput1" name="web_notes"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Video Notes Page URL

                                </label>
                                <input type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" name="video_note_url">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Video Notes</label>
                                <textarea class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" rows="4" id="exampleFormControlInput1" name="video_notes"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">PDF Notes</label>
                                <input type="file" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" aria-describedby="inputGroupFilePDF" aria-label="Upload" name="pdf_notes" accept="application/pdf">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Source Reference Links</label>
                                <input type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" name="source_ref_link">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Topic Question</label>
                                <input type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" name="topic_question">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlTextarea1" class="form-label">Topic Question Notes</label>
                                <textarea class="form-control" id="exampleFormControlTextarea1" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" rows="4" name="topic_question_notes"></textarea>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-light px-5 mx-3" data-bs-dismiss="modal" style="border-radius:10px !important; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px">Cancel</button>
                            <input type="submit" class="btn btn-primary px-5 mx-3" style="border-radius:10px !important; box-shadow:rgba(9, 9, 24, 0.1) 0px 0px 6px" value="Submit" name="topic" />
                        </div>
                        <input type="hidden" name="frmname" value="newFrm" />
                    </form>
                </div>
            </div>
        </div>
        <!-- model -->

        <!-- modal update topic -->
        <div class="modal fade" id="updateTopicModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content p-5" style="border-radius:20px;">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="model-head">
                            <h4 class="fw-normal">Update Topic</h4>
                        </div>
                        <div class="modal-body px-0">
                            <div class="mb-3">
                                <label for="topic_name_update" class="form-label">Topic Name</label>
                                <input type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" name="topic_name_update" id="topic_name_update">
                            </div>
                            <div class="mb-3">
                                <input required type="text" hidden class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" name="topic_id_update" id="topic_id_update" value="<?= $topicList[0][0] ?>">

                                <label for="exampleFormControlInput1" class="form-label">Book: Page Number</label>
                                <input type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="page_number_update" name="page_number_update">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlTextarea1" class="form-label">Book Notes</label>
                                <textarea class="form-control" id="book_notes_update" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" rows="4" name="book_notes_update"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Page URL</label>
                                <input type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="page_url_update" name="page_url_update">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Web Notes
                                </label>
                                <textarea class="form-control" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" rows="4" id="web_notes_update" name="web_notes_update"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Video Notes Page URL

                                </label>
                                <input type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="video_note_url_update" name="video_note_url_update">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Video Notes</label>
                                <textarea class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" rows="4" id="video_notes_update" name="video_notes_update"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">PDF Notes</label>
                                <input type="file" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" aria-describedby="inputGroupFilePDF" aria-label="Upload" name="pdf_notes_update" accept="application/pdf">
                            </div>
                           <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Source Reference Links</label>
                                <input type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="source_ref_link_update" name="source_ref_link_update">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Topic Question</label>
                                <input type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="topic_question_update" name="topic_question_update">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlTextarea1" class="form-label">Topic Question Notes</label>
                                <textarea class="form-control" id="topic_question_notes_update" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" rows="4" name="topic_question_notes_update"></textarea>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-light px-5 mx-3" data-bs-dismiss="modal" style="border-radius:10px !important; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px">Cancel</button>
                            <input type="submit" class="btn btn-primary px-5 mx-3" style="border-radius:10px !important; box-shadow:rgba(9, 9, 24, 0.1) 0px 0px 6px" value="Submit" name="topic" />
                        </div>
                        <input type="hidden" name="frmname" value="updateFrm" />
                    </form>
                </div>
            </div>
        </div>
        <!-- model -->

        <!-- chapterModal book -->
        <div class="modal fade" id="chapterModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content p-5" style="border-radius:20px;">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="model-head">
                            <h4 class="fw-normal">New Chapter</h4>
                        </div>
                        <div class="modal-body px-0">
                            <div class="mb-3">
                                <label for="exampleFormControlInput1" class="form-label">Chapter Number</label>
                                <input required type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="exampleFormControlInput1" name="chapter_number">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlTextarea1" class="form-label">Chapter Name</label>
                                <textarea required class="form-control" id="exampleFormControlTextarea1" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" rows="4" name="chapter_name"></textarea>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-light px-5 mx-3" data-bs-dismiss="modal" style="border-radius:10px !important; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px">Cancel</button>
                            <input type="submit" class="btn btn-primary px-5 mx-3" style="border-radius:10px !important; box-shadow:rgba(9, 9, 24, 0.1) 0px 0px 6px" value="Submit" name="chapter" />
                        </div>
                        <input type="hidden" name="frmname" value="newFrm" />
                    </form>
                </div>
            </div>
        </div>
        <!-- modal book -->

        <!-- update chapterModal -->
        <div class="modal fade" id="updateChapterModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content p-5" style="border-radius:20px;">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="model-head">
                            <h4 class="fw-normal">Update Chapter</h4>
                        </div>
                        <div class="modal-body px-0">
                            <div class="mb-3">
                                <input required type="text" hidden class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" name="chapter_id_update" id="chapter_id_update" value="<?= $chapterList[0][0] ?>">

                                <label for="exampleFormControlInput1" class="form-label">Chapter Number</label>
                                <input required type="text" class="form-control form-control-lg" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" id="chapter_number_update" name="chapter_number_update" value="<?= $chapterList[0]["chapter_number"] ?>">
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlTextarea1" class="form-label">Chapter Name</label>
                                <textarea required class="form-control" id="chapter_name_update" style="border-radius:10px; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px" rows="4" name="chapter_name_update"><?= $chapterList[0][2] ?></textarea>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-light px-5 mx-3" data-bs-dismiss="modal" style="border-radius:10px !important; box-shadow:rgb(48 51 128 / 10%) 0px 0px 6px">Cancel</button>
                            <input type="submit" class="btn btn-primary px-5 mx-3" style="border-radius:10px !important; box-shadow:rgba(9, 9, 24, 0.1) 0px 0px 6px" value="Submit" name="chapter" />
                        </div>
                        <input type="hidden" name="frmname" value="updateFrm" />
                    </form>
                </div>
            </div>
        </div>
        <!-- modal book -->
    </div>
</div>
<!-- ============================================================== -->
</div>
<?php require_once('footer.php'); ?>
<script type="text/javascript">
    //remove confirm form resubmission issue [START]
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    //remove confirm form resubmission issue [END]

    //window onload function here [START]
    window.onload = function() {
        var topicList = <?php echo json_encode($topicList); ?>;
        var chapIDNow = <?php echo json_encode($chapIDNow); ?>;
        var tID = <?php echo json_encode($topicIDNow); ?>;

        if (chapIDNow != "NA" && tID != "NA") {
            chapterChange(chapIDNow, tID);
        } else {
            topicChange(topicList[0]['uID']);
        }



        // console.log(topicList[0]['uID']);
        // console.log(chapIDNow);
        // console.log(tID);



    };
    //window onload function here [END]


    //handle chapter change drop down [START]
    function chapterChange(currChapter, tID) {

        if (!tID) {
            tID = "NA";
        }


        //update the hidden element in the modal form
        document.getElementById("selectedChapter").value = currChapter;

        document.getElementById("chapter_id_update").value = currChapter;

        var chapterList = <?php echo json_encode($chapterList); ?>;
        var topicList = <?php echo json_encode($topicList); ?>;

        //resetting metadata and topic table before doing anything else
        document.getElementById("topicTableName").innerHTML = "NA";
        document.getElementById("topic_PageNum").innerHTML = "NA";
        document.getElementById("topic_bookNotes").innerHTML = "";
        document.getElementById("topic_webNotesURL").innerHTML = "NA";
        document.getElementById("topic_webNotesURL").href = "NA";
        document.getElementById("topic_webNotes").innerHTML = "";
        document.getElementById("topic_videoURL").innerHTML = "NA";
        document.getElementById("topic_videoURL").href = "NA";
        document.getElementById("topic_videoNotes").innerHTML = "NA";
        document.getElementById("topic_pdfNotes").innerHTML = "NA";
        document.getElementById("topic_question").innerHTML = "NA";
        document.getElementById("topic_question_notes").innerHTML = "NA";


        //update the metadata table and the select option for chapters
        var select = document.getElementById("chapterList");
        select.innerHTML = "";
        var counterNow = 0;
        for (var i = 0; i < chapterList.length; i++) {
            var opt = chapterList[i];
            if (opt["uID"] == currChapter) {
                document.getElementById("chapterTableNumber").innerHTML = opt["chapter_number"].replace(/\\/g, '');
                document.getElementById("chapterTableName").innerHTML = opt["chapter_name"].replace(/\\/g, '');

                document.getElementById("chapter_number_update").value = opt["chapter_number"].replace(/\\/g, '');
                document.getElementById("chapter_name_update").value = opt["chapter_name"].replace(/\\/g, '');
            }
            if (opt["uID"] == currChapter) {
                select.innerHTML += "<option selected value=\"" + opt['uID'] + "\">" + "Chapter " + opt["chapter_number"] + ": " + opt['chapter_name'].replace(/\\/g, '') + "</option>";

            } else {
                select.innerHTML += "<option value=\"" + opt['uID'] + "\">" + "Chapter " + opt["chapter_number"] + ": " + opt['chapter_name'].replace(/\\/g, '') + "</option>";

            }

            counterNow++;
        }

        //update the topic dropdown list
        var firstCounter = 0
        var select = document.getElementById("topicList");
        select.innerHTML = ""
        for (var i = 0; i < topicList.length; i++) {
            var opt = topicList[i];
            if (opt["chapter_ID"] == currChapter) {
                if (firstCounter === 0) {
                    firstCounter = 1;

                    //update the metadata for the first topic
                    document.getElementById("topicTableName").innerHTML = opt['topic_name'].replace(/\\/g, '');

                    //update the topic table below for the first topic
                    if (tID == "NA") {
                        topicChange(opt['uID']);
                    }

                }
                if (tID != "NA") {
                    if (opt["uID"] == tID) {
                        select.innerHTML += "<option selected value=\"" + opt['uID'] + "\">" + opt['topic_name'].replace(/\\/g, '') + "</option>";
                    } else {
                        select.innerHTML += "<option value=\"" + opt['uID'] + "\">" + opt['topic_name'].replace(/\\/g, '') + "</option>";
                    }
                } else {
                    select.innerHTML += "<option value=\"" + opt['uID'] + "\">" + opt['topic_name'].replace(/\\/g, '') + "</option>";
                }
            }
        }

        if (tID != "NA") {
            topicChange(tID);
        }

    }
    //handle chapter change drop down [END]         

    //handle topic change drop down [START]
    function topicChange(currTopic) {

        var topicList = <?php echo json_encode($topicList); ?>;

        document.getElementById("topic_id_update").value = currTopic;

        //update the metadata table and topic table
        for (var i = 0; i < topicList.length; i++) {
            var opt = topicList[i];
            if (opt["uID"] == currTopic) {

                //updating metadata table
                document.getElementById("topicTableName").innerHTML = opt["topic_name"].trim().replace(/\\/g, '');
                getQuestion(opt["topic_name"].trim());

                //updating topic table below
                document.getElementById("topic_PageNum").innerHTML = opt["page_number"].trim().replace(/\\/g, '');

                document.getElementById("page_number_update").value = opt["page_number"].trim().replace(/\\/g, '');

                var lines = opt["book_notes"].split('\n');

                document.getElementById("topic_bookNotes").innerHTML = "";
                document.getElementById("book_notes_update").value = "";

                var bookNotesList = document.getElementById("topic_bookNotes");
                var notesUpdate = "";
                lines.map((item) => {
                    if (item != "\r" && item != "" && item != " ") {
                        var li = document.createElement("li");
                        li.classList.add("ms-5");
                        li.classList.add("fw-normal");
                        li.appendChild(document.createTextNode(item.replace(/\\/g, '')));
                        li.style.listStyle  = 'none';
                        bookNotesList.appendChild(li);
                        notesUpdate += item + "\n";
                    }
                });
                document.getElementById("book_notes_update").value = notesUpdate.trim().replace(/\\/g, '');

                document.getElementById("topic_webNotesURL").innerHTML = opt["page_url"].trim().replace(/\\/g, '');
                document.getElementById("topic_webNotesURL").href = opt["page_url"].trim().replace(/\\/g, '');

                document.getElementById("page_url_update").value = opt["page_url"].trim().replace(/\\/g, '');


                document.getElementById("web_notes_update").value = "";

                var lines = opt["web_notes"].split('\n');
                document.getElementById("topic_webNotes").innerHTML = "";
                document.getElementById("web_notes_update").value = "";

                var bookNotesList = document.getElementById("topic_webNotes");
                var notesUpdate = "";
                lines.map((item) => {
                    if (item != "\r" && item != "" && item != " ") {
                        var li = document.createElement("li");
                        li.classList.add("ms-5");
                        li.classList.add("fw-normal");
                        li.appendChild(document.createTextNode(item.replace(/\\/g, '')));
                        li.style.listStyle  = 'none';
                        bookNotesList.appendChild(li);
                        notesUpdate += item + "\n";
                    }
                });
                document.getElementById("web_notes_update").value = notesUpdate.trim().replace(/\\/g, '');

                // document.getElementById("topic_webNotes").innerHTML = opt["web_notes"];

                document.getElementById("topic_videoURL").innerHTML = opt["video_note_url"].trim().replace(/\\/g, '');
                document.getElementById("topic_videoURL").href = opt["video_note_url"].trim().replace(/\\/g, '');

                function getId(url) {
                    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
                    const match = url.match(regExp);

                    return (match && match[2].length === 11) ?
                        match[2] :
                        null;
                }

                document.getElementById("topic_videoURL_live").src = "about:blank";
                if (opt["video_note_url"] != "" && opt["video_note_url"] != " " && opt["video_note_url"] != "\r") {
                    if (opt["video_note_url"].includes("youtube")) {
                        const videoId = getId(opt["video_note_url"].trim());
                        var srcNow = "//www.youtube.com/embed/" + videoId;
                        document.getElementById("topic_videoURL_live").src = srcNow;
                    }
                    document.getElementById("topic_videoURL_live").src = srcNow;
                }
                document.getElementById("video_note_url_update").value = opt["video_note_url"].trim();


                document.getElementById("topic_pdf_live").src = "about:blank";
                if (opt["pdf_notes"] != null && opt["pdf_notes"] != "" && opt["pdf_notes"] != " " && opt["pdf_notes"] != "\r") {
                        var srcNow = opt["pdf_notes"].trim();
                    document.getElementById("topic_pdf_live").src = srcNow;
                }



                var lines = opt["video_notes"].split('\n');
                document.getElementById("topic_videoNotes").innerHTML = "";
                document.getElementById("video_notes_update").value = "";
                var bookNotesList = document.getElementById("topic_videoNotes");
                var notesUpdate = "";
                lines.map((item) => {
                    if (item != "\r" && item != "" && item != " ") {
                        var li = document.createElement("li");
                        li.classList.add("ms-5");
                        li.classList.add("fw-normal");
                        li.appendChild(document.createTextNode(item.replace(/\\/g, '')));
                        li.style.listStyle  = 'none';
                        bookNotesList.appendChild(li);
                        notesUpdate += item + "\n";
                    }
                });
                document.getElementById("video_notes_update").value = notesUpdate.trim().replace(/\\/g, '');

                // var lines = opt["pdf_notes"].split('\n');
                // document.getElementById("topic_pdfNotes").innerHTML = "";
                // document.getElementById("pdf_notes_update").value = "";
                // var bookNotesList = document.getElementById("topic_pdfNotes");
                // var notesUpdate = "";
                // lines.map((item) => {
                //     if (item != "\r" && item != "" && item != " ") {
                //         var li = document.createElement("li");
                //         li.classList.add("ms-5");
                //         li.classList.add("fw-normal");
                //         li.appendChild(document.createTextNode(item.replace(/\\/g, '')));
                //         li.style.listStyle  = 'none';
                //         bookNotesList.appendChild(li);
                //         notesUpdate += item + "\n";
                //     }
                // });
                // document.getElementById("pdf_notes_update").value = notesUpdate.trim().replace(/\\/g, '');

                // document.getElementById("topic_videoNotes").innerHTML = opt["video_notes"];

                document.getElementById("source_ref_link_update").value = opt["source_ref_link"].trim().replace(/\\/g, '');

                document.getElementById("topic_question").innerHTML = opt["topic_question"].trim().replace(/\\/g, '');

                document.getElementById("topic_question_update").value = opt["topic_question"].trim().replace(/\\/g, '');

                var lines = opt["topic_question_notes"].split('\n');
                document.getElementById("topic_question_notes").innerHTML = "";
                document.getElementById("topic_question_notes_update").value = "";

                var bookNotesList = document.getElementById("topic_question_notes");
                var notesUpdate = "";
                lines.map((item) => {
                    if (item != "\r" && item != "" && item != " ") {
                        var li = document.createElement("li");
                        li.classList.add("ms-5");
                        li.classList.add("fw-normal");
                        li.appendChild(document.createTextNode(item.replace(/\\/g, '')));
                        li.style.listStyle  = 'none';
                        bookNotesList.appendChild(li);
                        notesUpdate += item + "\n";
                    }
                });
                document.getElementById("topic_question_notes_update").value = notesUpdate.trim().replace(/\\/g, '');

                document.getElementById("topic_name_update").value = opt["topic_name"].trim().replace(/\\/g, '');

                // document.getElementById("topic_question_notes").innerHTML = opt["topic_question_notes"];
            }
        }
    }
    //handle topic change drop down [END]


    //get relevant questions for bottom [START]
    function getQuestion(topicName) {

        console.log(topicName);

        // http://localhost:3000/testing.php?tid=7

        var questionList = <?php echo json_encode($questionList); ?>;
        var answerList = <?php echo json_encode($answersList); ?>;

        var questionCounter = 0;
        var answerCounter = 0;

        for (var i = 0; i < questionList.length; i++) {
            var opt = questionList[i];
            if (opt["test_topic"] == topicName.trim()) {
                questionCounter++;
                document.getElementById("studyQuestion").innerHTML = opt["test_question"].trim().replace(/\\/g, '');
                document.getElementById("studyLink").href = "testing.php?tid=" + opt["test_id"]

                for (var j = 0; j < answerList.length; j++) {
                    var optAnswer = answerList[j];
                    if (optAnswer["question_id"] == opt["uID"]) {

                        answerCounter++;
                        if (optAnswer["answer"] == opt["correct_answer"]) {
                            document.getElementById("studyStatus").innerHTML = "Correct";
                        } else {
                            document.getElementById("studyStatus").innerHTML = "Incorrect";
                        }
                        break;
                    }
                }
                break;
            }
        }

        if (questionCounter == 0) {
            document.getElementById("studyQuestion").innerHTML = "No Questions";
            $("#studyQuestion").parent().parent().parent().hide();
            document.getElementById("studyStatus").style.display = "none";
            document.getElementById("studyLink").style.display = "none";
        }
        if (answerCounter == 0) {
            document.getElementById("studyStatus").innerHTML = "No Answer";
        }


    }
    //get relevant questions for bottom [END]

    //update and delete functions for chapter and topics [START]
    //chapters
    function editChapterFunc() {
        const chapterID = document.getElementById("chapter_id_update").value;
        // alert(chapterID);            
        document.getElementById("updateBtnChapter").click();
    }

    function delChapterFunc() {
        const chapterID = document.getElementById("chapter_id_update").value;
        // alert(chapterID);
        var result = confirm("Are you sure you want to delete this chapter?");
        if (result) {
            $.ajax({
                type: "GET",
                url: "delete-chapter.php",
                data: {
                    deleteId: chapterID
                },
                dataType: "html",
                success: function(data) {
                    alert("This chapter was successfully deleted");
                    window.location.reload();
                }
            });
        }
    }

    //topics
    function editTopicFunc() {
        const topicID = document.getElementById("topic_id_update").value;
        // alert(topicID);
        document.getElementById("updateBtnTopic").click();

    }

    function delTopicFunc() {
        const topicID = document.getElementById("topic_id_update").value;
        // alert(topicID);
        var result = confirm("Are you sure you want to delete this topic?");
        if (result) {
            $.ajax({
                type: "GET",
                url: "delete-topic.php",
                data: {
                    deleteId: topicID
                },
                dataType: "html",
                success: function(data) {
                    alert("This topic was successfully deleted");
                    window.location.reload();
                }
            });
        }
    }
    //update and delete functions for chapter [END]

    //handle UL change in book notes [START]
    $('#book_notes_ul').click(function() {
        alert("Cha");
    });
    //handle UL change in book notes [END]

    //filter click handle [START]
    function filterClick() {
        var filterParamChap = document.getElementById("filterParamChap").value;
        var filterParamTopic = document.getElementById("filterParamTopic").value;

        if (filterParamChap != "" && filterParamTopic == "") //just chapter filter
        {
            window.location.href = window.location.href.split('&')[0] + "&filterChap=" + filterParamChap;
        } else if (filterParamChap != "" && filterParamTopic != "") //both chapter and topic filter
        {
            window.location.href = window.location.href.split('&')[0] + "&filterChap=" + filterParamChap + "&filterTopic=" + filterParamTopic;
        } else if (filterParamChap == "" && filterParamTopic != "") //just topic filter
        {
            window.location.href = window.location.href.split('&')[0] + "&filterTopic=" + filterParamTopic;
        } else if (filterParamChap == "" && filterParamTopic == "") //no filters at all
        {

            window.location.href = window.location.href.split('&')[0];
        }

    }
    //filter click handle [END]     
</script>