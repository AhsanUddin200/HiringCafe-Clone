<?php
$host = "localhost";    // Your database host
$user = "root";         // Your database username
$pass = "";             // Your database password
$db_name = "hiringcafe"; // Database name

$conn = mysqli_connect($host, $user, $pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
