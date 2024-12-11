<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'employee') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = (int)$_POST['job_id'];
    $user_id = $_SESSION['user_id'];

    // Check if already applied
    $check = "SELECT id FROM hiringcafe_applications WHERE user_id=? AND job_id=?";
    $stmt = mysqli_prepare($conn, $check);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $job_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo "<div class='alert alert-warning'>You have already applied for this job. <a href='index.php'>Go back</a></div>";
        exit;
    }
    mysqli_stmt_close($stmt);

    $insert = "INSERT INTO hiringcafe_applications (user_id, job_id) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $insert);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $job_id);
    if (mysqli_stmt_execute($stmt)) {
        echo "<div class='alert alert-success'>Application submitted successfully! <a href='index.php'>Go back</a></div>";
    } else {
        echo "<div class='alert alert-danger'>Error applying to the job. <a href='index.php'>Go back</a></div>";
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Application Status</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 50%;
            margin: 100px auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        .container h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 30px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 16px;
            text-align: center;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Job Application Status</h2>
        <?php
        // The alerts are echoed from the PHP section above
        ?>
    </div>

    <div class="footer">
        &copy; 2024 HiringCafe. All Rights Reserved.
    </div>

</body>
</html>
