<?php
session_start();

// Check login session
if (!isset($_SESSION['email'])) {
    // If session is not set, check cookie
    if (isset($_COOKIE['user_email'])) {
        $_SESSION['email'] = $_COOKIE['user_email']; // Restore session from cookie
    } else {
        header("Location: log.html");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
</head>
<body>
    <h1>Welcome, <?php echo $_SESSION['email']; ?></h1>
    <p>This is your profile page.</p>
    <a href="logout.php">Logout</a>
</body>
</html>
