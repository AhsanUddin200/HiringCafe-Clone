<?php
session_start();
require_once 'db.php';

// Ensure only logged-in employees can access
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'employee'){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all jobs
$job_query = "SELECT j.id, j.job_title, j.job_description, j.job_type, c.company_name
              FROM hiringcafe_jobs j
              JOIN hiringcafe_company c ON j.company_id = c.id
              ORDER BY j.created_at DESC";
$job_result = mysqli_query($conn, $job_query);

// Fetch messages sent to this candidate
$message_query = "SELECT m.id, m.message_text, m.created_at, co.company_name
                  FROM hiringcafe_messages m
                  JOIN hiringcafe_company co ON m.company_id = co.id
                  WHERE m.user_id = ?
                  ORDER BY m.created_at DESC";
$stmt = mysqli_prepare($conn, $message_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$messages_result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Employee Dashboard</title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --background-color: #f7f9fc;
            --text-color: #333;
            --card-background: #ffffff;
            --highlight-color: #f39c12;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--background-color);
            margin: 0;
            padding: 0;
        }

        header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 2rem;
            font-weight: 600;
        }

        nav {
            background-color: var(--secondary-color);
            text-align: center;
            padding: 10px 0;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 300;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: var(--highlight-color);
        }

        .container {
            width: 85%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 15px;
        }

        .job-listing, .message-item {
            background-color: var(--card-background);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .job-listing h4 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .job-listing p {
            color: var(--text-color);
            margin-bottom: 15px;
        }

        .apply-form input[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .apply-form input[type="submit"]:hover {
            background-color: var(--highlight-color);
        }

        .message-item p {
            color: var(--text-color);
        }

        .message-item small {
            color: #888;
        }

        .message-item p strong {
            color: var(--secondary-color);
        }

        .no-messages {
            text-align: center;
            font-size: 1.2rem;
            color: var(--secondary-color);
        }
    </style>
</head>
<body>

<header>
    Employee Dashboard
</header>

<nav>
    <a href="index.php">Home</a>
    <a href="employee_profile.php">My Profile</a>
    <a href="logout.php">Logout</a>
</nav>

<div class="container">
    <h2 style="color: var(--primary-color);">Welcome to Your Dashboard!</h2>
    <p style="font-size: 1.1rem; color: var(--text-color);">Here you can apply to job openings and view messages from companies.</p>

    <h3 style="color: var(--primary-color);">Available Jobs</h3>
    <?php while($job = mysqli_fetch_assoc($job_result)): ?>
        <div class="job-listing">
            <h4><?php echo htmlspecialchars($job['job_title']); ?> <span style="font-size: 1rem; color: var(--secondary-color);">[<?php echo htmlspecialchars($job['job_type']); ?>]</span></h4>
            <p><strong>Company:</strong> <?php echo htmlspecialchars($job['company_name']); ?></p>
            <p><?php echo nl2br(htmlspecialchars($job['job_description'])); ?></p>
            <form class="apply-form" method="POST" action="apply_job.php">
                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                <input type="submit" value="Apply Now">
            </form>
        </div>
    <?php endwhile; ?>

    <h3 style="color: var(--primary-color);">Messages from Companies</h3>
    <?php if(mysqli_num_rows($messages_result) > 0): ?>
        <?php while($msg = mysqli_fetch_assoc($messages_result)): ?>
            <div class="message-item">
                <p><strong>From: <?php echo htmlspecialchars($msg['company_name']); ?></strong></p>
                <p><?php echo nl2br(htmlspecialchars($msg['message_text'])); ?></p>
                <p><small>Sent on: <?php echo $msg['created_at']; ?></small></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-messages">You don't have any messages yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
