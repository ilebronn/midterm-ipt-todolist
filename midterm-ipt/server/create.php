<?php

session_start(); // Start the session

// Getting access to the database
require_once('../database/db_credentials.php');
require_once('../database/database.php');
$db = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form values
    $email = $_POST['email'];
    $uName = $_POST['login'];
    $pass = $_POST['pass'];

    // Insert user data into the Users table
    try {
        $sql = "INSERT INTO Users (Name, Email, Password) VALUES (:name, :email, :password)";
        $stmt = $db->prepare($sql);

        // Bind parameters to prevent SQL injection
        $stmt->bindParam(':name', $uName, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $pass, PDO::PARAM_STR); // Consider hashing the password

        // Execute the prepared statement
        $stmt->execute();

        // Check if the query was successful
        if ($stmt->rowCount() === 1) {
            // Registration successful, grab the ID of the newly inserted user
            $userID = $db->lastInsertId(); // Get the ID of the last inserted row
            $userName = $uName; // Get the Name of the user

            // Set session variables
            $_SESSION['user_id'] = $userID;
            $_SESSION['user_name'] = $userName;

            // Redirect to the dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            // Error handling: display an error message or redirect to the registration page with an error parameter
            header("Location: register.php?error=registration_failed");
            exit;
        }
    } catch (PDOException $e) {
        // Handle any errors
        error_log("Database error: " . $e->getMessage());
        header("Location: register.php?error=registration_failed");
        exit;
    }
} else {
    header("Location: register.php");
    exit;
}

// Disconnect from the database
db_disconnect($db);
?>
