<?php

session_start();

require_once "config/database.php";


/* =====================================
   CHECK LOGIN
===================================== */

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;

}


$user_id = $_SESSION["user_id"];

$user_name = $_SESSION["user_name"];


/* =====================================
   GET AVAILABLE EXAMS
===================================== */

$exam_query = $conn->query(
    "SELECT
        id,
        title,
        description,
        duration,
        total_questions
     FROM exams
     ORDER BY id DESC"
);


/* =====================================
   GET STUDENT RESULTS
===================================== */

$result_stmt = $conn->prepare(
    "SELECT
        results.id,
        results.score,
        results.total_questions,
        results.percentage,
        results.exam_date,
        exams.title
     FROM results
     INNER JOIN exams
        ON results.exam_id = exams.id
     WHERE results.user_id = ?
     ORDER BY results.exam_date DESC"
);

$result_stmt->bind_param(
    "i",
    $user_id
);

$result_stmt->execute();

$results = $result_stmt->get_result();


/* =====================================
   DASHBOARD STATISTICS
===================================== */

$exam_count = $exam_query->num_rows;

$result_count = $results->num_rows;

$average_percentage = 0;


if ($result_count > 0) {

    $average_stmt = $conn->prepare(
        "SELECT AVG(percentage) AS average_percentage
         FROM results
         WHERE user_id = ?"
    );

    $average_stmt->bind_param(
        "i",
        $user_id
    );

    $average_stmt->execute();

    $average_result =
        $average_stmt->get_result();

    $average_data =
        $average_result->fetch_assoc();

    $average_percentage =
        floatval(
            $average_data["average_percentage"]
        );
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Student Dashboard | ExamSystem
    </title>

    <link rel="stylesheet"
          href="css/online.css">

</head>


<body>


<!-- =====================================
     NAVBAR
===================================== -->

<header class="dashboard-navbar">


    <a href="dashboard.php"
       class="dashboard-brand">

        🎓 Exam<span>System</span>

    </a>


    <nav>

        <a href="dashboard.php"
           class="dashboard-active">

            Dashboard

        </a>

        <a href="index.php">

            Home

        </a>

    </nav>


    <div class="dashboard-user">

        <div class="user-avatar">

            <?= strtoupper(
                substr($user_name, 0, 1)
            ) ?>

        </div>


        <div class="user-info">

            <strong>

                <?= htmlspecialchars(
                    $user_name
                ) ?>

            </strong>

            <span>
                Student
            </span>

        </div>


        <a href="logout.php"
           class="logout-btn">

            Logout

        </a>

    </div>


</header>



<!-- =====================================
     DASHBOARD CONTENT
===================================== -->

<main class="dashboard-container">


    <!-- =================================
         WELCOME SECTION
    ================================== -->

    <section class="dashboard-welcome">


        <div>

            <p class="dashboard-tag">
                STUDENT DASHBOARD
            </p>


            <h1>

                Welcome back,
                <?= htmlspecialchars(
                    $user_name
                ) ?>!

            </h1>


            <p>

                Choose an examination below
                and start testing your knowledge.

            </p>

        </div>


        <div class="welcome-icon">

            🎓

        </div>


    </section>



    <!-- =================================
         STATISTICS
    ================================== -->

    <section class="dashboard-stats">


        <div class="stat-card">

            <div class="stat-icon blue">
                📝
            </div>

            <div>

                <span>
                    Available Exams
                </span>

                <strong>
                    <?= $exam_count ?>
                </strong>

            </div>

        </div>



        <div class="stat-card">

            <div class="stat-icon green">
                ✓
            </div>

            <div>

                <span>
                    Exams Completed
                </span>

                <strong>
                    <?= $result_count ?>
                </strong>

            </div>

        </div>



        <div class="stat-card">

            <div class="stat-icon purple">
                📊
            </div>

            <div>

                <span>
                    Average Score
                </span>

                <strong>

                    <?= number_format(
                        $average_percentage,
                        1
                    ) ?>%

                </strong>

            </div>

        </div>


    </section>



    <!-- =================================
         AVAILABLE EXAMS
    ================================== -->

    <section class="dashboard-section">


        <div class="section-heading">


            <div>

                <p class="dashboard-tag">
                    EXAMINATIONS
                </p>

                <h2>
                    Available Exams
                </h2>

            </div>


            <span class="exam-count">

                <?= $exam_count ?>
                Exams Available

            </span>


        </div>



        <?php if ($exam_count > 0): ?>


            <div class="exam-grid">


                <?php while (
                    $exam =
                    $exam_query->fetch_assoc()
                ): ?>


                    <div class="exam-card">


                        <div class="exam-card-top">


                            <div class="exam-card-icon">

                                📝

                            </div>


                            <span class="available-badge">

                                Available

                            </span>


                        </div>



                        <h3>

                            <?= htmlspecialchars(
                                $exam["title"]
                            ) ?>

                        </h3>



                        <p class="exam-description">

                            <?= htmlspecialchars(
                                $exam["description"]
                            ) ?>

                        </p>



                        <div class="exam-meta">


                            <div>

                                <span>
                                    Questions
                                </span>

                                <strong>

                                    <?= $exam[
                                        "total_questions"
                                    ] ?>

                                </strong>

                            </div>


                            <div>

                                <span>
                                    Duration
                                </span>

                                <strong>

                                    <?= $exam[
                                        "duration"
                                    ] ?>

                                    min

                                </strong>

                            </div>


                        </div>



                        <a href="exam.php?id=<?= $exam["id"] ?>"
                           class="start-exam-btn">

                            Start Exam
                            <span>→</span>

                        </a>


                    </div>


                <?php endwhile; ?>


            </div>


        <?php else: ?>


            <div class="empty-state">

                <div>
                    📝
                </div>

                <h3>
                    No Exams Available
                </h3>

                <p>
                    There are currently no examinations
                    available. Please check again later.
                </p>

            </div>


        <?php endif; ?>


    </section>



    <!-- =================================
         PREVIOUS RESULTS
    ================================== -->

    <section class="dashboard-section">


        <div class="section-heading">

            <div>

                <p class="dashboard-tag">
                    PERFORMANCE
                </p>

                <h2>
                    Recent Results
                </h2>

            </div>

        </div>



        <?php if ($result_count > 0): ?>


            <div class="results-table-wrapper">


                <table class="results-table">


                    <thead>

                        <tr>

                            <th>
                                Examination
                            </th>

                            <th>
                                Score
                            </th>

                            <th>
                                Percentage
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php while (
                            $result =
                            $results->fetch_assoc()
                        ): ?>


                            <tr>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $result["title"]
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <?= $result["score"] ?>

                                    /

                                    <?= $result[
                                        "total_questions"
                                    ] ?>

                                </td>


                                <td>

                                    <span class="percentage-badge">

                                        <?= number_format(
                                            $result["percentage"],
                                            2
                                        ) ?>%

                                    </span>

                                </td>


                                <td>

                                    <?= date(
                                        "d M Y",
                                        strtotime(
                                            $result["exam_date"]
                                        )
                                    ) ?>

                                </td>


                                <td>

                                    <a
                                        href="result.php?id=<?= $result["id"] ?>"
                                        class="view-result">

                                        View Result

                                    </a>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    </tbody>


                </table>


            </div>


        <?php else: ?>


            <div class="empty-state small">

                <div>
                    📊
                </div>

                <h3>
                    No Results Yet
                </h3>

                <p>
                    Complete your first examination
                    to see your results here.
                </p>

            </div>


        <?php endif; ?>


    </section>


</main>



<!-- =====================================
     FOOTER
===================================== -->

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