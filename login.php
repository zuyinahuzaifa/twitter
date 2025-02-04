<?php
// Start session for user login
session_start();
include 'db.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'], $_POST['password'])) {

    $email = $conn->real_escape_string($_POST['email']);  // Prevent SQL injection
    $password = $_POST['password'];  // Plain text password entered by the user

    // Query to check if email exists in the database
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    // Check if user with the provided email exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();  // Fetch the user data from the database

        // Verify the password (assuming password is hashed in the database)
        if (password_verify($password, $user['password'])) {
            // Password is correct, start the session and redirect to home page
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            header('Location: home.php');  // Redirect to home page
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
