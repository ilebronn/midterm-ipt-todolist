<?php
require_once('../database/db_credentials.php');
require_once('../database/database.php');

// Start the session if it hasn't been started already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the ID is set in the URL
if (!isset($_GET['id'])) {
    header("Location: dashboard.php"); // Go to dashboard.php
    exit; // Exit the script after going back to dashboard.php
}

$taskId = $_GET['id']; // Getting Task ID from the URL

// Connect to the database
$db = db_connect();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = $_POST['taskName2'];
    $tag = $_POST['tag2'];
    $description = $_POST['description2'];

    // Prepare the SQL statement
    $sql = "UPDATE Tasks SET Name = :name, Tag = :tag, Description = :description WHERE Id = :taskId AND UserId = :userId";
    $stmt = $db->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':tag', $tag, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':taskId', $taskId, PDO::PARAM_INT);
    $stmt->bindParam(':userId', $user_id, PDO::PARAM_INT);

    // Execute the statement
    if ($stmt->execute()) {
        // Task updated successfully
        header("Location: dashboard.php");
        exit;
    } else {
        // Error updating task
        echo "Error updating task: " . implode(", ", $stmt->errorInfo());
    }
} else {
    // Invalid request method
    header("Location: dashboard.php");
    exit;
}

db_disconnect($db);
?>
