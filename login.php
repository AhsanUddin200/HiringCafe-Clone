<?php
require_once 'db.php';
session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userType = $_POST['user_type']; // 'employee' or 'company'
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($userType === 'employee') {
        $query = "SELECT id, password FROM hiringcafe_user WHERE email = ?";
    } else {
        $query = "SELECT id, password FROM hiringcafe_company WHERE email = ?";
    }

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $user_id, $hashed_password);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (isset($user_id)) {
        if (password_verify($password, $hashed_password)) {
            $_SESSION['loggedin'] = true;
            $_SESSION['user_type'] = $userType;
            $_SESSION['user_id'] = $user_id;
            
            if ($userType === 'employee') {
                header("Location: employee_dashboard.php");
            } else {
                header("Location: company_dashboard.php");
            }
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>HiringCafe - Login</title>
<style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
    .container { width: 400px; margin: 50px auto; background: #fff; padding: 20px; 
                 box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 8px; }
    .logo { text-align: center; margin-bottom: 20px; }
    .logo img { max-width: 150px; height: auto; }
    h1 { text-align: center; margin-bottom: 20px; }
    form { display: flex; flex-direction: column; }
    label { margin-bottom: 5px; font-weight: bold; }
    input[type="email"], input[type="password"], select { 
        padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 5px; }
    input[type="submit"] { padding: 10px; background: #32a9b9;; color: #fff; 
                           border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
    input[type="submit"]:hover { background: #218838; }
    .error { color: #d00; margin-bottom: 10px; }
    .signup-link { text-align: center; margin-top: 10px; }
</style>
</head>
<body>
<div class="container">
    <div class="logo">
        <img src="https://cdn.prod.website-files.com/6289e146ede95040eb0f4a3e/652c1f42e212bf1bb178e291_hiring-cafe-logo.png " alt="HiringCafe Logo">
    </div>
    <h1>Login</h1>
    <?php if(!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="user_type">Account Type:</label>
        <select name="user_type" required>
            <option value="" disabled selected>--Select--</option>
            <option value="employee">Candidate (Employee)</option>
            <option value="company">Company (Employer)</option>
        </select>
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" placeholder="Enter your Email" required />
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" placeholder="Enter your Password" required />
        <input type="submit" value="Login" />
    </form>
    <div class="signup-link">
        Don't have an account? <a href="signup.php">Signup here</a>.
    </div>
</div>
</body>
</html>

