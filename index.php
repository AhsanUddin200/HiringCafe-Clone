    <?php
    require_once 'db.php';
    session_start();

    // Filtering logic
    $filter = "";
    if (isset($_GET['job_type']) && in_array($_GET['job_type'], ['Full-time', 'Part-time', 'Remote'])) {
        $filter = $_GET['job_type'];
    }

    $query = "SELECT j.id, j.job_title, j.job_description, j.job_type, c.company_name 
            FROM hiringcafe_jobs j
            JOIN hiringcafe_company c ON j.company_id = c.id";

    if (!empty($filter)) {
        $query .= " WHERE j.job_type = '" . mysqli_real_escape_string($conn, $filter) . "'";
    }

    $query .= " ORDER BY j.created_at DESC";
    $result = mysqli_query($conn, $query);
    ?>
 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>HiringCafe - Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --highlight-color: #f39c12; /* New highlight color */
            --text-color: #333;
            --background-color: #f7f9fc;
            --card-background: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            line-height: 1.6;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            text-align: center;
            padding: 20px 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 600;
        }

        .navbar {
            background-color: rgba(255,255,255,0.1);
            padding: 15px 0;
            text-align: center;
        }

        .navbar a {
            color: black;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 300;
            transition: color 0.3s ease;
        }

        .navbar a:hover {
            color: var(--highlight-color); /* Use highlight color on hover */
        }

        .navbar .highlight-button {
            color: var(--highlight-color); /* Make the Hiring/Employee buttons stand out */
            font-weight: bold;
            border: 2px solid var(--highlight-color);
            padding: 5px 15px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .navbar .highlight-button:hover {
            background-color: var(--highlight-color);
            color: white;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 15px;
        }

        .filter-form {
            background-color: var(--card-background);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .filter-form label {
            display: block;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .filter-form select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .filter-form input[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .filter-form input[type="submit"]:hover {
            background-color: var(--secondary-color);
        }

        .job-listing {
            background-color: var(--card-background);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .job-listing:hover {
            transform: translateY(-5px);
        }

        .job-listing h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .job-listing p {
            margin-bottom: 10px;
        }

        .job-listing form input[type="submit"] {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .job-listing form input[type="submit"]:hover {
            background-color: var(--primary-color);
        }

        .footer {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            text-align: center;
            padding: 15px 0;
            position: relative;
            bottom: 0;
            width: 100%;
        }
        
    </style>
</head>
<body>
    <header class="header">
        <h1>HiringCafe</h1>
    </header>
    <nav class="navbar">
        <a href="index.php">Home</a>
        <a href="signup.php" class="highlight-button">Hiring</a> <!-- Hiring button with highlight -->
        <a href="signup.php" class="highlight-button">Employee</a> <!-- Employee button with highlight -->
        <?php
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
            if ($_SESSION['user_type'] === 'employee') {
                echo '<a href="employee_dashboard.php">Employee Dashboard</a>';
                echo '<a href="employee_profile.php">My Profile</a>';
            } else {
                echo '<a href="company_dashboard.php">Company Dashboard</a>';
            }
            echo '<a href="logout.php">Logout</a>';
        } else {
            echo '<a href="signup.php">Signup</a>';
            echo '<a href="login.php">Login</a>';
        }
        ?>
    </nav>
    <div class="container">
        <h2 style="margin-bottom: 20px; color: var(--primary-color);">Latest Job Postings</h2>
        <form method="GET" class="filter-form">
            <label for="job_type">Filter by type:</label>
            <select name="job_type" id="job_type">
                <option value="">All</option>
                <option value="Full-time" <?php if($filter=='Full-time') echo 'selected';?>>Full-time</option>
                <option value="Part-time" <?php if($filter=='Part-time') echo 'selected';?>>Part-time</option>
                <option value="Remote" <?php if($filter=='Remote') echo 'selected';?>>Remote</option>
            </select>
            <input type="submit" value="Filter" />
        </form>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <div class="job-listing">
            <h3><?php echo htmlspecialchars($row['job_title']); ?></h3>
            <p><strong>Company:</strong> <?php echo htmlspecialchars($row['company_name']); ?></p>
            <p><strong>Type:</strong> <?php echo htmlspecialchars($row['job_type']); ?></p>
            <p><?php echo nl2br(htmlspecialchars($row['job_description'])); ?></p>
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['user_type'] === 'employee'): ?>
                <form method="POST" action="apply_job.php">
                    <input type="hidden" name="job_id" value="<?php echo $row['id']; ?>">
                    <input type="submit" value="Apply Now" />
                </form>
            <?php else: ?>
                <p><em>Login as an employee to apply</em></p>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
    <footer class="footer">
        <p>&copy; 2024 HiringCafe</p>
    </footer>
</body>
</html>

