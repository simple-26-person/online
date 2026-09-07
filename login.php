<?php

session_start();

require_once "config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email =
        trim($_POST["email"]);

    $password =
        $_POST["password"];


    $stmt =
        $conn->prepare(
            "SELECT id, name, email, password, role
             FROM users
             WHERE email = ?"
        );


    $stmt->bind_param(
        "s",
        $email
    );


    $stmt->execute();


    $result =
        $stmt->get_result();


    if ($result->num_rows === 1) {

        $user =
            $result->fetch_assoc();


        if (
            password_verify(
                $password,
                $user["password"]
            )
        ) {

            $_SESSION["user_id"] =
                $user["id"];

            $_SESSION["user_name"] =
                $user["name"];

            $_SESSION["role"] =
                $user["role"];


            if (
                $user["role"] === "admin"
            ) {

                header(
                    "Location: admin/dashboard.php"
                );

            } else {

                header(
                    "Location: dashboard.php"
                );

            }

            exit;

        }

    }


    $error =
        "Invalid email or password.";

}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Login | ExamSystem
    </title>

    <link rel="stylesheet"
          href="css/online.css">

</head>

<body>


<header class="simple-header">

    <a href="index.php"
       class="brand-link">

        🎓 Exam<span>System</span>

    </a>

    <a href="index.php">
        ← Back to Home
    </a>

</header>



<div class="form-container">


    <div class="form-icon">
        👤
    </div>


    <h2>
        Welcome Back
    </h2>


    <p class="form-subtitle">
        Login to access your examination dashboard.
    </p>


    <?php if ($error): ?>

        <div class="error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <form method="POST">


        <div class="form-group">

            <label>
                Email Address
            </label>

            <input
                type="email"
                name="email"
                placeholder="Enter your email"
                required>

        </div>


        <div class="form-group">

            <label>
                Password
            </label>

            <input
                type="password"
                name="password"
                placeholder="Enter your password"
                required>

        </div>


        <button
            type="submit"
            class="form-button">

            Login

        </button>


    </form>


    <p class="form-link">

        Don't have an account?

        <a href="register.php">
            Create Account
        </a>

    </p>


</div>


</body>

</html>