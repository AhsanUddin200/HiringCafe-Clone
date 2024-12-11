<?php
require_once 'db.php';

$error = "";
$success = "";

// Function to check email existence
function checkEmailExists($conn, $email, $userType) {
    $table = $userType === 'employee' ? 'hiringcafe_user' : 'hiringcafe_company';
    $query = "SELECT id FROM $table WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

// Function to insert user/company
function insertUser($conn, $userType, $name, $email, $hashedPassword) {
    $table = $userType === 'employee' ? 'hiringcafe_user' : 'hiringcafe_company';
    $field = $userType === 'employee' ? 'name' : 'company_name';
    $query = "INSERT INTO $table ($field, email, password) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashedPassword);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $success;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userType = $_POST['user_type']; // 'employee' or 'company'
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required. Please fill out all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format. Please enter a valid email.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match. Please try again.";
    } else {
        // Check if email already exists
        if (checkEmailExists($conn, $email, $userType)) {
            $error = "Email already registered. Please use another one.";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert into database
            if (insertUser($conn, $userType, $name, $email, $hashedPassword)) {
                // Redirect to login page after successful signup
                header("Location: login.php");
                exit;  // Ensure no further code is executed
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>HiringCafe - Signup</title>
<style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
    .container { width: 400px; margin: 50px auto; background: #fff; padding: 20px; 
                 box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 8px; }
    .logo { text-align: center; margin-bottom: 20px; }
    .logo img { max-width: 150px; height: auto; }
    h1 { text-align: center; margin-bottom: 20px; }
    form { display: flex; flex-direction: column; }
    label { margin-bottom: 5px; font-weight: bold; }
    input[type="text"], input[type="email"], input[type="password"], select { 
        padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; }
    input[type="submit"] { padding: 10px; background: #32a9b9; color: #fff; 
                           border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
    input[type="submit"]:hover { background: #32a9b9; }
    .error { color: #d00; margin-bottom: 10px; }
    .success { color: #32a9b9; margin-bottom: 10px; }
    .login-link { text-align: center; margin-top: 10px; }
</style>
</head>
<body>
<div class="container">
    <div class="logo">
        <img src="https://cdn.prod.website-files.com/6289e146ede95040eb0f4a3e/652c1f42e212bf1bb178e291_hiring-cafe-logo.png" alt="HiringCafe Logo">
    </div>
    <h1>Signup</h1>
    <?php if(!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if(!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="user_type">Account Type:</label>
        <select name="user_type" id="user_type" required>
            <option value="" disabled selected>--Select--</option>
            <option value="employee">Candidate (Employee)</option>
            <option value="company">Company (Employer)</option>
        </select>

        <label for="name">Name / Company Name:</label>
        <input type="text" name="name" id="name" placeholder="Enter Full Name or Company Name" required />

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" placeholder="Enter your Email" required />

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" placeholder="Enter Password" required />

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required />

        <input type="submit" value="Signup" />
    </form>
    <div class="login-link">
        Already have an account? <a href="login.php">Login here</a>.
    </div>
</div>
</body>
</html>
