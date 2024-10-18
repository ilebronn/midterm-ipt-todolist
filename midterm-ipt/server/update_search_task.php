<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checked = isset($_POST['complete']) ? 1 : 0; // Set checked based on the checkbox

    $taskId = $_POST['taskId']; 

    // Include database connection details
    require_once('../database/db_credentials.php');
    require_once('../database/database.php'); // This already contains the db_connect() function

    // Connect to the database
    $db = db_connect();

    $userId = $_SESSION['user_id'];
    
    // Update the task status in the database using prepared statements
    $sql = "UPDATE Tasks SET Complete = :complete WHERE Id = :taskId AND UserId = :userId";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':complete', $checked, PDO::PARAM_INT);
    $stmt->bindParam(':taskId', $taskId, PDO::PARAM_INT);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Task updated successfully
        header("Location: search.php");
        exit(); // Stop further execution after header redirect
    } else {
        // Error updating task
        echo "Error updating task.";
    }

    db_disconnect($db);
} else {
    // Invalid request method or missing parameters
    http_response_code(400);
    echo "Bad request";
}
?>
