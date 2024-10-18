<?php
session_start(); // Start the session

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // If logged in, redirect to the dashboard
    header("Location: server/dashboard.php");
    exit(); // Make sure to exit after a redirect
} else {
    // If not logged in, redirect to the login page
    header("Location: server/login.php");
    exit(); // Make sure to exit after a redirect
}
?>
