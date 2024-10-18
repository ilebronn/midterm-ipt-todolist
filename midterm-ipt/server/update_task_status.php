<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine if the checkbox was checked
    $checked = isset($_POST['complete']) ? 1 : 0;
    $taskId = $_POST['taskId']; // Task ID from the POST request

    // Ensure you have a valid task ID
    if (empty($taskId) || !isset($_SESSION['user_id'])) {
        http_response_code(400);
        echo "Bad request: Missing task ID or user session.";
        exit;
    }

    require_once('../database/db_credentials.php');
    require_once('../database/database.php');
    $db = db_connect();

    $userId = $_SESSION['user_id'];
    
    // Prepare the SQL statement
    $sql = "UPDATE Tasks SET Complete = :complete WHERE Id = :taskId AND UserId = :userId";
    $stmt = $db->prepare($sql);
    
    // Bind the parameters
    $stmt->bindParam(':complete', $checked, PDO::PARAM_INT);
    $stmt->bindParam(':taskId', $taskId, PDO::PARAM_INT);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

    // Execute the statement
    if ($stmt->execute()) {
        // Task updated successfully
        header("Location: dashboard.php");
        exit;
    } else {
        // Error updating task
        echo "Error updating task: " . implode(", ", $stmt->errorInfo());
    }

    db_disconnect($db);
} else {
    // Invalid request method
    http_response_code(400);
    echo "Bad request";
}
?>
