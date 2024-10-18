<?php
session_start();
require_once('../database/db_credentials.php');
require_once('../database/database.php');
$db = db_connect(); // This now returns a PDO connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['login'];
    $password = $_POST['pass'];

    // Prepare the SQL statement
    $sql = "SELECT * FROM Users WHERE Name = :username AND Password = :password";
    $stmt = $db->prepare($sql); // Use PDO's prepare method

    // Bind parameters
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR); // Be cautious about plain password storage

    // Execute the statement
    $stmt->execute();

    // Fetch data
    if ($stmt->rowCount() == 1) { // Use rowCount() to check for the number of rows returned
        // Login successful
        $row = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the user data
        $_SESSION['user_id'] = $row['ID'];
        $_SESSION['user_name'] = $row['Name'];
        header("Location: dashboard.php");
        db_disconnect($db); // Optional, as PDO will handle this
        exit;
    } else {
        // Login failed
        header("Location: ../server/login.php?error=login_failed");
        db_disconnect($db); // Optional, as PDO will handle this
        exit;
    }
} else {
    header("Location: login.php");
    db_disconnect($db); // Optional, as PDO will handle this
    exit;
}
?>
