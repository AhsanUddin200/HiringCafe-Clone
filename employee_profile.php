<?php
session_start();
require_once 'db.php';

if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_type'] !== 'employee'){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Fetch current user data
$query = "SELECT name, email, profile_summary, resume_url FROM hiringcafe_user WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $name, $email, $profile_summary, $resume_url);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['name']);
    $new_profile_summary = trim($_POST['profile_summary']);

    // Handle resume upload if any
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $resume_file = $target_dir . basename($_FILES['resume']['name']);
        $file_type = pathinfo($resume_file, PATHINFO_EXTENSION);

        // Simple check (improve in production)
        if(!in_array(strtolower($file_type), ['pdf', 'doc', 'docx'])) {
            $error = "Invalid file type. Upload PDF, DOC, or DOCX only.";
        } else {
            if (move_uploaded_file($_FILES['resume']['tmp_name'], $resume_file)) {
                // Update resume_url
                $resume_url = $resume_file;
            } else {
                $error = "Error uploading file.";
            }
        }
    }

    if (empty($error)) {
        $update = "UPDATE hiringcafe_user SET name = ?, profile_summary = ?, resume_url = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt, "sssi", $new_name, $new_profile_summary, $resume_url, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Profile updated successfully!";
            $name = $new_name;
            $profile_summary = $new_profile_summary;
        } else {
            $error = "Error updating profile.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<title>My Profile</title>
<style>
    :root {
        --primary-color: #3498db;
        --secondary-color: #2ecc71;
        --background-color: #f7f9fc;
        --text-color: #333;
        --card-background: #ffffff;
        --button-color: #007bff;
        --button-hover-color: #0056b3;
        --error-color: #e74c3c;
        --success-color: #2ecc71;
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
        padding: 20px 0;
        text-align: center;
        font-size: 2rem;
        font-weight: bold;
    }

    nav {
        background-color: var(--secondary-color);
        text-align: center;
        padding: 15px 0;
    }

    nav a {
        color: white;
        text-decoration: none;
        margin: 0 15px;
        font-weight: 300;
        transition: color 0.3s ease;
    }

    nav a:hover {
        color: var(--primary-color);
    }

    .container {
        width: 80%;
        max-width: 900px;
        margin: 30px auto;
        background: var(--card-background);
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
        color: var(--primary-color);
        margin-bottom: 20px;
    }

    label {
        font-size: 1rem;
        color: var(--text-color);
        margin-bottom: 10px;
        display: block;
    }

    input[type="text"], textarea {
        width: 100%;
        padding: 12px;
        margin-bottom: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
        font-size: 1rem;
    }

    input[type="file"] {
        margin-bottom: 20px;
    }

    .error {
        color: var(--error-color);
        font-size: 1rem;
        margin-bottom: 20px;
        padding: 10px;
        background-color: rgba(231, 76, 60, 0.1);
        border: 1px solid var(--error-color);
        border-radius: 5px;
    }

    .success {
        color: var(--success-color);
        font-size: 1rem;
        margin-bottom: 20px;
        padding: 10px;
        background-color: rgba(46, 204, 113, 0.1);
        border: 1px solid var(--success-color);
        border-radius: 5px;
    }

    input[type="submit"] {
        padding: 12px 20px;
        background-color: var(--button-color);
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        font-size: 1.1rem;
    }

    input[type="submit"]:hover {
        background-color: var(--button-hover-color);
    }

    .file-link {
        color: var(--primary-color);
        font-size: 1rem;
        text-decoration: none;
        display: inline-block;
        margin-top: 10px;
    }

    .file-link:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>

<header>
    Employee Profile
</header>

<nav>
    <a href="index.php">Home</a>
    <a href="employee_dashboard.php">Dashboard</a>
    <a href="logout.php">Logout</a>
</nav>

<div class="container">
    <h2>Update Your Profile</h2>

    <?php if($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" required />

        <label for="profile_summary">Profile Summary:</label>
        <textarea name="profile_summary" id="profile_summary" rows="5"><?php echo htmlspecialchars($profile_summary); ?></textarea>

        <label for="resume">Resume (PDF, DOC, DOCX):</label>
        <?php if($resume_url): ?>
            <p>Current Resume: <a href="<?php echo htmlspecialchars($resume_url); ?>" target="_blank" class="file-link">View Current Resume</a></p>
        <?php endif; ?>
        <input type="file" name="resume" id="resume" />

        <input type="submit" value="Update Profile" />
    </form>
</div>

</body>
</html>
