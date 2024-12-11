<?php
require_once 'db.php';
session_start();
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'company'){
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

// Handle new job posting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_title'])) {
    $job_title = trim($_POST['job_title']);
    $job_desc = trim($_POST['job_description']);
    $job_type = trim($_POST['job_type']);
    $company_id = $_SESSION['user_id'];

    if (empty($job_title) || empty($job_desc) || empty($job_type)) {
        $error = "All fields are required.";
    } else {
        $insert = "INSERT INTO hiringcafe_jobs (company_id, job_title, job_description, job_type) VALUES (?,?,?,?)";
        $stmt = mysqli_prepare($conn, $insert);
        mysqli_stmt_bind_param($stmt, "isss", $company_id, $job_title, $job_desc, $job_type);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Job posted successfully!";
        } else {
            $error = "Error posting job.";
        }
        mysqli_stmt_close($stmt);
    }
}

// If shortlisting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id']) && !isset($_POST['message_text'])) {
    $application_id = (int)$_POST['application_id'];
    $update = "UPDATE hiringcafe_applications SET status='shortlisted' WHERE id=?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "i", $application_id);
    if (mysqli_stmt_execute($stmt)) {
        $success = "Candidate shortlisted successfully!";
    } else {
        $error = "Error shortlisting candidate.";
    }
    mysqli_stmt_close($stmt);
}

// If sending a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id']) && isset($_POST['message_text'])) {
    $application_id = (int)$_POST['application_id'];
    $message_text = trim($_POST['message_text']);
    if (empty($message_text)) {
        $error = "Message cannot be empty.";
    } else {
        // Get user_id from application
        $app_query = "SELECT user_id, job_id FROM hiringcafe_applications WHERE id=?";
        $stmt = mysqli_prepare($conn, $app_query);
        mysqli_stmt_bind_param($stmt, "i", $application_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $user_id, $job_id);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        $company_id = $_SESSION['user_id'];

        $insert_message = "INSERT INTO hiringcafe_messages (company_id, user_id, message_text) VALUES (?,?,?)";
        $stmt = mysqli_prepare($conn, $insert_message);
        mysqli_stmt_bind_param($stmt, "iis", $company_id, $user_id, $message_text);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Message sent to candidate!";
        } else {
            $error = "Error sending message.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch jobs posted by this company
$company_id = $_SESSION['user_id'];
$query = "SELECT * FROM hiringcafe_jobs WHERE company_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $company_id);
mysqli_stmt_execute($stmt);
$jobs_result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

// Fetch applications for company's jobs
$app_query = "SELECT a.id as app_id, u.id as user_id, u.name, u.email, u.resume_url, j.job_title, a.status
              FROM hiringcafe_applications a
              JOIN hiringcafe_user u ON a.user_id = u.id
              JOIN hiringcafe_jobs j ON a.job_id = j.id
              WHERE j.company_id = ?
              ORDER BY a.created_at DESC";

$stmt = mysqli_prepare($conn, $app_query);
mysqli_stmt_bind_param($stmt, "i", $company_id);
mysqli_stmt_execute($stmt);
$applications_result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard</title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --highlight-color: #f39c12; /* New highlight color */
            --text-color: #333;
            --background-color: #f7f9fc;
            --card-background: #ffffff;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 20px;
            text-align: center;
        }

        nav {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            padding: 10px 0;
            text-align: center;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-size: 16px;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .container {
            width: 85%;
            max-width: 1200px;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        h1, h2, h3 {
            color: #333;
        }

        input[type="text"], input[type="submit"], select, textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        textarea {
            min-height: 100px;
        }

        input[type="submit"] {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: #fff;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .error {
            color: #dc3545;
            font-size: 14px;
            padding: 10px;
            border: 1px solid #dc3545;
            background-color: #f8d7da;
            border-radius: 5px;
        }

        .success {
            color: #28a745;
            font-size: 14px;
            padding: 10px;
            border: 1px solid #28a745;
            background-color: #d4edda;
            border-radius: 5px;
        }

        .job-item, .application-item {
            background-color: #f9f9f9;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .job-item h4, .application-item h4 {
            margin: 0 0 10px;
        }

        .job-item p, .application-item p {
            color: #555;
            line-height: 1.6;
        }

        .application-item p strong {
            color: #28a745;
        }

        .message-form {
            margin-top: 15px;
        }

        .message-form textarea {
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .container {
                width: 95%;
            }

            nav {
                text-align: left;
            }
        }
    </style>
</head>
<body>
<header style="background-color: white;">
    <h1 style="color: white;">Company Dashboard</h1>
</header>


<nav>
    <a href="index.php">Home</a>
    <a href="logout.php">Logout</a>
</nav>

<div class="container">
    <h2>Welcome, Company!</h2>

    <h3>Post a New Job</h3>
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="job_title" placeholder="Job Title" required />
        <textarea name="job_description" placeholder="Job Description" required></textarea>
        <select name="job_type" required>
            <option value="" disabled selected>--Select Type--</option>
            <option value="Full-time">Full-time</option>
            <option value="Part-time">Part-time</option>
            <option value="Remote">Remote</option>
        </select>
        <input type="submit" value="Post Job" />
    </form>

    <h3>Your Posted Jobs</h3>
    <?php while ($job = mysqli_fetch_assoc($jobs_result)): ?>
        <div class="job-item">
            <h4><?php echo htmlspecialchars($job['job_title']); ?> (<?php echo htmlspecialchars($job['job_type']); ?>)</h4>
            <p><?php echo nl2br(htmlspecialchars($job['job_description'])); ?></p>
            <p><small>Posted on: <?php echo $job['created_at']; ?></small></p>
        </div>
    <?php endwhile; ?>

    <h3>Applications for Your Jobs</h3>
    <?php while ($app = mysqli_fetch_assoc($applications_result)): ?>
        <div class="application-item">
            <h4>Candidate: <?php echo htmlspecialchars($app['name']); ?> applied for <?php echo htmlspecialchars($app['job_title']); ?></h4>
            <p>Email: <?php echo htmlspecialchars($app['email']); ?></p>
            <?php if ($app['resume_url']): ?>
                <p>Resume: <a href="<?php echo htmlspecialchars($app['resume_url']); ?>" target="_blank">View Resume</a></p>
            <?php endif; ?>
            <p>Status: <?php echo htmlspecialchars($app['status']); ?></p>
            
            <?php if ($app['status'] === 'applied'): ?>
            <form method="POST">
                <input type="hidden" name="application_id" value="<?php echo $app['app_id']; ?>" />
                <input type="submit" value="Shortlist Candidate" />
            </form>
            <?php else: ?>
                <p><strong>Candidate shortlisted</strong></p>
            <?php endif; ?>

            <h5>Send a Message to Candidate</h5>
            <form method="POST" class="message-form">
                <input type="hidden" name="application_id" value="<?php echo $app['app_id']; ?>" />
                <textarea name="message_text" placeholder="Write your message to candidate..." required></textarea>
                <input type="submit" value="Send Message" />
            </form>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>
