<?php
session_start();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    returnToDashboard();
}

function returnToDashboard() {
    header("Location: dashboard.php");
    exit; // Exit after redirection
}

function displaySearchResults() {
    $currentUserId = $_SESSION['user_id']; 
    require_once('../database/db_credentials.php');
    require_once('../database/database.php');
    $db = db_connect();

    // Set the search query based on GET or session
    $searchQuery = isset($_GET['searchQuery']) ? $_GET['searchQuery'] : ($_SESSION['last_search'] ?? '');

    // Store the last search in the session
    $_SESSION['last_search'] = $searchQuery;

    // Prepare the SQL statement
    $sql = "SELECT * FROM Tasks WHERE UserId = :userId AND Tag LIKE :searchQuery";
    $stmt = $db->prepare($sql);
    $likeSearchQuery = "%" . $searchQuery . "%"; // Use wildcard for LIKE
    $stmt->bindParam(':userId', $currentUserId, PDO::PARAM_INT);
    $stmt->bindParam(':searchQuery', $likeSearchQuery, PDO::PARAM_STR);
    $stmt->execute();

    // Check if there are results
    if ($stmt->rowCount() > 0) {
        echo "<table>";
        echo "<tr align='left'><th>Complete</th><th>Name</th><th>Tag</th></tr>";

        // Fetch the tasks and display them
        while ($task = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr align='left'>";
            echo "<td>
                    <form class='searchCheckBoxContainer' method='POST' action='update_search_task.php'>
                        <input type='hidden' name='taskId' value='{$task['Id']}'>
                        <input type='checkbox' name='complete' value='1' " . ($task['Complete'] ? 'checked' : '') . " onchange='this.form.submit()'>
                    </form>
                </td>";

            echo "<td class='taskCell'>" . htmlspecialchars($task['Name']) . "</td>";
            echo "<td class='tagCell'>" . htmlspecialchars($task['Tag'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No results found.</p>";
    }

    db_disconnect($db);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../styles/style.css">
    <title>Search Results</title>
</head>
<body>
    <h1 class="searchResultsHeader">Search Results</h1>
    <div class="searchResultsContainer">
        <?php displaySearchResults(); ?>
    </div>
    <form method="post">
        <input type="hidden" name="returnToDashboard" value="1">
        <button type="submit">Back to Home</button>
    </form>
</body>
</html>
