<?php

require_once "config/database.php";

$message = "";

$messageType = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name =
        trim($_POST["name"]);

    $email =
        trim($_POST["email"]);

    $password =
        $_POST["password"];

    $confirm =
        $_POST["confirm_password"];


    if (
        $password !== $confirm
    ) {

        $message =
            "Passwords do not match.";

        $messageType =
            "error";

    } else {


        $check =
            $conn->prepare(
                "SELECT id
                 FROM users
                 WHERE email = ?"
            );


        $check->bind_param(
            "s",
            $email
        );


        $check->execute();


        $result =
            $check->get_result();


        if (
            $result->num_rows > 0
        ) {

            $message =
                "An account with this email already exists.";

            $messageType =
                "error";

        } else {


            $hashedPassword =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            $stmt =
                $conn->prepare(
                    "INSERT INTO users
                    (name, email, password)
                    VALUES (?, ?, ?)"
                );


            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $hashedPassword
            );


            if ($stmt->execute()) {

                header(
                    "Location: login.php?registered=1"
                );

                exit;

            } else {

                $message =
                    "Registration failed. Please try again.";

                $messageType =
                    "error";

            }

        }

    }

}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Sign Up | ExamSystem
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
        👤+
    </div>


    <h2>
        Create Your Account
    </h2>


    <p class="form-subtitle">

        Register to start taking online examinations.

    </p>


    <?php if ($message): ?>

        <div class="<?= $messageType ?>">

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <form method="POST">


        <div class="form-group">

            <label>
                Full Name
            </label>

            <input
                type="text"
                name="name"
                placeholder="Enter your full name"
                required>

        </div>


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
                placeholder="Create a password"
                minlength="6"
                required>

        </div>


        <div class="form-group">

            <label>
                Confirm Password
            </label>

            <input
                type="password"
                name="confirm_password"
                placeholder="Confirm your password"
                minlength="6"
                required>

        </div>


        <button
            type="submit"
            class="form-button">

            Create Account

        </button>


    </form>


    <p class="form-link">

        Already have an account?

        <a href="login.php">
            Login
        </a>

    </p>


</div>


</body>

</html>