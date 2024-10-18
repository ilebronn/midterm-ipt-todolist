<?php
require_once ('../database/db_credentials.php');
require_once ('../database/database.php');

$db = db_connect();

// Check if the id is set in the URL
if (!isset($_GET['id'])) {
    header("Location: dashboard.php"); // Go to dashboard.php
    exit; // Exit the script after going back to dashboard.php
}

$taskId = $_GET['id']; // Getting Task ID from the URL (we get this from the expand button)

// Check if delete request was posted/requested by the form.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Prepare the SQL statement
    $sql = "DELETE FROM tasks WHERE Id = :taskId";
    $stmt = $db->prepare($sql);
    
    // Bind the taskId parameter
    $stmt->bindParam(':taskId', $taskId, PDO::PARAM_INT);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect to dashboard on successful deletion
        header("Location: dashboard.php");
        exit;
    } else {
        // Handle error: Optionally, you can log or display an error message here
        echo "Error deleting task.";
    }
}
?>
