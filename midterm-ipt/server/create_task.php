<?php
session_start();
require_once('../database/db_credentials.php');
require_once('../database/database.php');

$db = db_connect(); // Connect to the database using PDO

// Check if server request is POST and if the submit button is clicked on the Add Task form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $user_id = $_SESSION['user_id'];
    $name = $_POST['taskName'];
    $tag = $_POST['tag'];
    $description = $_POST['description'];

    try {
        $sql = "INSERT INTO Tasks (UserId, Name, Tag, Description, Complete) VALUES (:userId, :name, :tag, :description, 0)";
        $stmt = $db->prepare($sql);

        // Bind parameters to prevent SQL injection
        $stmt->bindParam(':userId', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':tag', $tag, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);

        // Execute the prepared statement
        $stmt->execute();

        // Check if a row was affected (inserted)
        if ($stmt->rowCount() === 1) {
            // Task inserted successfully
            header("Location: dashboard.php");
            exit();
        } else {
            // Error inserting task
            header("Location: dashboard.php");
            exit();
        }
    } catch (PDOException $e) {
        // Handle any errors
        // You might want to log the error message and redirect or display a friendly message
        error_log("Database error: " . $e->getMessage());
        header("Location: dashboard.php");
        exit();
    }
} else {
    // Refresh the dashboard page whether a task is inserted or not, to hide the popup window
    header("Location: dashboard.php");
    exit();
}

// Disconnect from the database
db_disconnect($db);
?>
