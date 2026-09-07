<?php

session_start();

require_once "config/database.php";


/* ===============================
   CHECK LOGIN
================================ */

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;

}


/* ===============================
   GET RESULT ID
================================ */

$result_id =
    isset($_GET["id"])
    ? intval($_GET["id"])
    : 0;


if ($result_id <= 0) {

    die("Invalid result.");

}


/* ===============================
   GET RESULT
================================ */

$user_id =
    $_SESSION["user_id"];


$stmt = $conn->prepare(
    "SELECT
        results.id,
        results.score,
        results.total_questions,
        results.percentage,
        results.exam_date,
        exams.title,
        exams.description
     FROM results
     INNER JOIN exams
        ON results.exam_id = exams.id
     WHERE results.id = ?
     AND results.user_id = ?"
);


$stmt->bind_param(
    "ii",
    $result_id,
    $user_id
);


$stmt->execute();


$result =
    $stmt->get_result();


if ($result->num_rows === 0) {

    die("Result not found.");

}


$data =
    $result->fetch_assoc();


$score =
    intval($data["score"]);


$total =
    intval($data["total_questions"]);


$percentage =
    floatval($data["percentage"]);


/* ===============================
   PERFORMANCE MESSAGE
================================ */

if ($percentage >= 80) {

    $performance =
        "Excellent Performance!";

} elseif ($percentage >= 60) {

    $performance =
        "Good Performance!";

} elseif ($percentage >= 40) {

    $performance =
        "Keep Improving!";

} else {

    $performance =
        "More Practice Needed.";

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Examination Result | ExamSystem
    </title>

    <link rel="stylesheet"
          href="css/online.css">

</head>


<body>


<!-- ================= HEADER ================= -->

<header class="simple-header">

    <a href="index.php"
       class="brand-link">

        🎓 Exam<span>System</span>

    </a>


    <a href="index.php">
        Home
    </a>

</header>



<!-- ================= RESULT ================= -->

<section class="result-page">


    <div class="result-card">


        <div class="result-success-icon">
            ✓
        </div>


        <p class="section-tag">
            EXAMINATION COMPLETED
        </p>


        <h1>
            Your Result
        </h1>


        <h2 class="result-exam-name">

            <?= htmlspecialchars(
                $data["title"]
            ) ?>

        </h2>


        <p class="performance">

            <?= $performance ?>

        </p>



        <!-- SCORE -->

        <div class="result-score">

            <?= $score ?>

            <span>
                /
                <?= $total ?>
            </span>

        </div>


        <p class="score-label">
            Total Score
        </p>



        <!-- PERCENTAGE -->

        <div class="percentage-box">

            <strong>

                <?= number_format(
                    $percentage,
                    2
                ) ?>%

            </strong>

            <span>
                Percentage
            </span>

        </div>



        <!-- DETAILS -->

        <div class="result-details">


            <div>

                <span>
                    Correct Answers
                </span>

                <strong>
                    <?= $score ?>
                </strong>

            </div>


            <div>

                <span>
                    Incorrect / Unanswered
                </span>

                <strong>
                    <?= $total - $score ?>
                </strong>

            </div>


            <div>

                <span>
                    Total Questions
                </span>

                <strong>
                    <?= $total ?>
                </strong>

            </div>


        </div>



        <p class="result-date">

            Examination completed on:

            <?= date(
                "d M Y, h:i A",
                strtotime(
                    $data["exam_date"]
                )
            ) ?>

        </p>



        <div class="result-buttons">

            <a href="dashboard.php"
               class="primary-btn">

                Go to Dashboard

            </a>


            <a href="index.php"
               class="secondary-btn">

                Back to Home

            </a>

        </div>


    </div>


</section>



<footer>

    <div class="footer-brand">

        🎓 Exam<span>System</span>

    </div>


    <p>

        © 2026 ExamSystem.
        All Rights Reserved.

    </p>

</footer>


</body>

</html>