<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once "config/database.php";

/* =========================================
   CHECK LOGIN
========================================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}


/* =========================================
   GET EXAM ID
========================================= */

$exam_id = isset($_GET["id"]) ? intval($_GET["id"]) : 1;


/* =========================================
   GET EXAM DETAILS
========================================= */

$stmt = $conn->prepare(
    "SELECT *
     FROM exams
     WHERE id = ?"
);

$stmt->bind_param("i", $exam_id);
$stmt->execute();

$exam_result = $stmt->get_result();

if ($exam_result->num_rows === 0) {
    die("Exam not found.");
}

$exam = $exam_result->fetch_assoc();


/* =========================================
   GET QUESTIONS
========================================= */

$stmt = $conn->prepare(
    "SELECT *
     FROM questions
     WHERE exam_id = ?
     ORDER BY id ASC"
);

$stmt->bind_param("i", $exam_id);
$stmt->execute();

$questions = $stmt->get_result();


/* =========================================
   SUBMIT EXAM
========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $score = 0;
    $total_questions = 0;

    /* Get questions again for checking answers */

    $question_stmt = $conn->prepare(
        "SELECT *
         FROM questions
         WHERE exam_id = ?
         ORDER BY id ASC"
    );

    $question_stmt->bind_param("i", $exam_id);
    $question_stmt->execute();

    $all_questions = $question_stmt->get_result();


    /* Check answers */

    while ($question = $all_questions->fetch_assoc()) {

        $total_questions++;

        $question_id = $question["id"];

        $selected_answer = "";

        if (
            isset($_POST["answer"]) &&
            isset($_POST["answer"][$question_id])
        ) {
            $selected_answer =
                $_POST["answer"][$question_id];
        }


        if (
            $selected_answer ===
            $question["correct_answer"]
        ) {
            $score++;
        }
    }


    /* Calculate percentage */

    if ($total_questions > 0) {

        $percentage =
            ($score / $total_questions) * 100;

    } else {

        $percentage = 0;

    }


    /* =====================================
       SAVE RESULT
    ===================================== */

    $user_id = $_SESSION["user_id"];

    $result_stmt = $conn->prepare(
        "INSERT INTO results
        (
            user_id,
            exam_id,
            score,
            total_questions,
            percentage
        )
        VALUES (?, ?, ?, ?, ?)"
    );

    $result_stmt->bind_param(
        "iiiid",
        $user_id,
        $exam_id,
        $score,
        $total_questions,
        $percentage
    );

    $result_stmt->execute();


    /* Get result ID */

    $result_id = $conn->insert_id;


    /* Go to result page */

    header(
        "Location: result.php?id=" . $result_id
    );

    exit;
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($exam["title"]) ?>
        | ExamSystem
    </title>

    <link
        rel="stylesheet"
        href="css/online.css"
    >

</head>


<body>


<!-- =========================================
     HEADER
========================================= -->

<header class="exam-topbar">

    <div class="brand-name">

        Exam<span>System</span>

    </div>


    <div>

        Student:

        <strong>

            <?= htmlspecialchars(
                $_SESSION["user_name"]
            ) ?>

        </strong>

    </div>


    <div class="exam-timer">

        <span>⏱</span>

        <span id="timer">
            25:00
        </span>

    </div>

</header>



<!-- =========================================
     EXAM PAGE
========================================= -->

<section class="exam-page">


    <!-- EXAM INFORMATION -->

    <div class="exam-title-box">

        <div>

            <p class="section-tag">
                ONLINE EXAMINATION
            </p>


            <h1>

                <?= htmlspecialchars(
                    $exam["title"]
                ) ?>

            </h1>


            <p>

                <?= htmlspecialchars(
                    $exam["description"]
                ) ?>

            </p>

        </div>


        <div class="exam-details">

            <div>

                <strong>
                    <?= $questions->num_rows ?>
                </strong>

                <span>
                    Questions
                </span>

            </div>


            <div>

                <strong>
                    25
                </strong>

                <span>
                    Minutes
                </span>

            </div>

        </div>

    </div>



    <!-- =====================================
         EXAM FORM
    ====================================== -->

    <form
        method="POST"
        id="examForm"
    >


        <?php

        $number = 1;

        while (
            $question =
            $questions->fetch_assoc()
        ):

        ?>


        <!-- QUESTION -->

        <div class="exam-question">


            <div class="question-number">

                Question <?= $number ?>

            </div>


            <h2>

                <?= htmlspecialchars(
                    $question["question"]
                ) ?>

            </h2>


            <div class="options">


                <!-- OPTION A -->

                <label class="answer-option">

                    <input
                        type="radio"
                        name="answer[<?= $question["id"] ?>]"
                        value="A"
                    >

                    <span class="option-letter">
                        A
                    </span>

                    <span>

                        <?= htmlspecialchars(
                            $question["option_a"]
                        ) ?>

                    </span>

                </label>



                <!-- OPTION B -->

                <label class="answer-option">

                    <input
                        type="radio"
                        name="answer[<?= $question["id"] ?>]"
                        value="B"
                    >

                    <span class="option-letter">
                        B
                    </span>

                    <span>

                        <?= htmlspecialchars(
                            $question["option_b"]
                        ) ?>

                    </span>

                </label>



                <!-- OPTION C -->

                <label class="answer-option">

                    <input
                        type="radio"
                        name="answer[<?= $question["id"] ?>]"
                        value="C"
                    >

                    <span class="option-letter">
                        C
                    </span>

                    <span>

                        <?= htmlspecialchars(
                            $question["option_c"]
                        ) ?>

                    </span>

                </label>



                <!-- OPTION D -->

                <label class="answer-option">

                    <input
                        type="radio"
                        name="answer[<?= $question["id"] ?>]"
                        value="D"
                    >

                    <span class="option-letter">
                        D
                    </span>

                    <span>

                        <?= htmlspecialchars(
                            $question["option_d"]
                        ) ?>

                    </span>

                </label>


            </div>

        </div>


        <?php

        $number++;

        endwhile;

        ?>


        <!-- =================================
             SUBMIT AREA
        ================================== -->

        <div class="submit-area">

            <p>

                Make sure you have answered
                all questions before submitting.

            </p>


            <button
                type="submit"
                class="submit-exam-btn"
                id="submitButton"
            >

                Submit Examination

            </button>

        </div>


    </form>

</section>



<!-- =========================================
     25 MINUTE TIMER
========================================= -->

<script>

    /*
       Maximum exam time:
       25 minutes = 1500 seconds
    */

    let duration = 25 * 60;


    const timer =
        document.getElementById("timer");


    const examForm =
        document.getElementById("examForm");


    let timeExpired = false;


    /*
       Update timer
    */

    function updateTimer() {


        let minutes =
            Math.floor(duration / 60);


        let seconds =
            duration % 60;


        timer.textContent =

            String(minutes).padStart(2, "0")
            +
            ":"
            +
            String(seconds).padStart(2, "0");


        /*
           Time finished
        */

        if (duration <= 0) {

            timeExpired = true;


            clearInterval(timerInterval);


            alert(
                "Time is over. Your examination will be submitted automatically."
            );


            examForm.submit();


            return;

        }


        duration--;

    }


    /*
       Display timer immediately
    */

    updateTimer();


    /*
       Run every second
    */

    const timerInterval =
        setInterval(
            updateTimer,
            1000
        );



    /*
       Confirm manual submission
    */

    examForm.addEventListener(
        "submit",
        function(event) {


            /*
               If time expired,
               submit automatically
            */

            if (timeExpired) {

                return;

            }


            const confirmed =
                confirm(
                    "Are you sure you want to submit the examination?"
                );


            if (!confirmed) {

                event.preventDefault();

            }

        }
    );

</script>


</body>

</html>