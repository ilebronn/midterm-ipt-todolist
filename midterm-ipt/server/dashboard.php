<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// This function creates rows in table format using the data queried from the current user.
function displayTasks() {
    $currentUserId = $_SESSION['user_id']; 

    require_once('../database/db_credentials.php');
    require_once('../database/database.php');
    $db = db_connect(); // Connect to the database using PDO

    // Query to select all tasks for the current user
    $sql = "SELECT * FROM Tasks WHERE UserId = :userId"; // Use a named placeholder

    // Prepare the SQL statement
    $stmt = $db->prepare($sql);
    
    // Bind the user ID parameter
    $stmt->bindParam(':userId', $currentUserId, PDO::PARAM_INT);
    
    // Execute the query
    $stmt->execute();

    // Check if the query was successful
    if ($stmt) {
        echo "<table>";
        echo "<tr align='left'><th>Complete</th><th>Name</th><th>Tag (optional)</th><th>Details</th></tr>";

        // Fetch the tasks from the result set. Function generates rows based on the query
        while ($task = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>
                <form method='POST' action='update_task_status.php'>
                    <input type='hidden' name='taskId' value='{$task['Id']}'>
                    <input type='checkbox' name='complete' value='1' " . ($task['Complete'] ? 'checked' : '') . " onchange='this.form.submit()'>
                </form>
            </td>";
            echo "<td class='taskCell'>" . htmlspecialchars($task['Name']) . "</td>";
            echo "<td class='tagCell'>" . htmlspecialchars($task['Tag'] ?? 'N/A') . "</td>";
            echo "<td class='detailCell'><button id='expandButton' class='detailButton' data-task-id='{$task['Id']}'>Expand</button></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        // Error handling: display an error message or redirect
        echo "Failed to retrieve tasks.";
    }

    // Close the database connection
    db_disconnect($db); 
}

// Function to retrieve all tasks for the current user
function getTasks() {
    $currentUserId = $_SESSION['user_id']; 

    require_once('../database/db_credentials.php');
    require_once('../database/database.php');
    $db = db_connect(); // Connect to the database using PDO

    $sql = "SELECT * FROM Tasks WHERE UserId = :userId"; 
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':userId', $currentUserId, PDO::PARAM_INT);
    $stmt->execute();

    $tasks = [];
    while ($task = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tasks[$task['Id']] = $task; // Use task ID as key
    }
    db_disconnect($db);
    return $tasks; // Return the array of tasks
}

// Function displays the username of the current session user
function displayUsername() {
    // Check if session variable is set
    if (!isset($_SESSION['user_name'])) {
        echo "Session variable not set.";
        return;
    }
    $currentUserName = $_SESSION['user_name'];
    echo "<p class='welcomeMessage'>Welcome back, " . htmlspecialchars($currentUserName) . "</p>"; // Escape output for safety
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Will Hergott, Andrew Kim, and Paulo Gomes">
    <link rel="stylesheet" type="text/css" href="../styles/style.css">
    <script src="../js/dashboard.js" defer></script>
    <title>Dashboard</title>
</head>
<body>
    <?php
    include "header.php";
    displayUsername();
    include "search_bar.php";
    ?>

    <div class="taskcontainer">
        <h1>Dashboard</h1>
        <button class="addTask" onclick="openPopup()">Add New Task</button>
        <hr>
        <!-- Calling displayTasks() to generate table -->
        <?php displayTasks(); ?>
        
        <!-- This is the form for adding a new task. -->
        <form id="taskForm" action="create_task.php" method="POST" onsubmit="return validate();">
            <div id="newTask" class="newTask" onmousedown="startDrag(event)">
                <h2>Add A New Task</h2>
                <div class="textfield2">
                    <label for="taskName">Task Name</label><br>
                    <input type="text" name="taskName" id="taskName" size="30" class="taskfield"
                        placeholder="E.g. Book an appointment at 5PM for...">
                </div>
                <div class="textfield2">
                    <label for="tag">Tag</label><br>
                    <input type="text" name="tag" id="tag" size="30" class="taskfield"
                        placeholder="E.g School, Work...">
                </div>
                <div class="textfield2">
                    <label for="description">Description</label><br>
                    <textarea name="description" id="description" class="taskfield" cols="33" rows="8" maxlength="500" placeholder="Enter a description here... (Maximum 500 characters)"></textarea>
                </div>
                <button type="submit" name="submit">Add</button>
                <button type="button" onclick="closePopup()">Close</button>
            </div>
        </form>
    </div>

    <!-- This is the form for expanding details about a task. -->
    <div class="taskcontainerExpand">
        <script> 
            var tasks = <?php echo json_encode(getTasks()); ?>; // Ensure getTasks() is called directly here
            document.addEventListener('DOMContentLoaded', function() {
                sendTaskID(tasks);
            });
        </script>
        <form id="taskExpandForm" action="" method="POST">
            <!-- Update this part in your dashboard.php -->
            <div id="expandTask" class="expandTask" style="display:none;" onmousedown="startDragExpand(event)"> <!-- Added onmousedown --> <!-- Removed draggable="true" -->
                <h2>Task Details</h2>
                <div class="textfield2">
                    <label for="taskName">Task Name</label><br>
                    <input type="text" name="taskName2" id="expandName" size="30" class="taskfield">
                </div>
                <div class="textfield2">
                    <label for="tag">Tag</label><br>
                    <input type="text" name="tag2" id="expandTag" size="30" maxlength="50" class="taskfield" placeholder="E.g School, Work...">
                </div>
                <div class="textfield2">
                    <label for="description">Description</label><br>
                    <textarea name="description2" id="expandDesc" class="taskfield" cols="33" rows="8" maxlength="500" placeholder="Enter a description here... (Maximum 500 characters)"></textarea>
                </div>  
                <input type="hidden" name="taskId" id="updateTaskId" value="">
                <button type="submit">Save Edits</button>
            </form>
            <form id="ExpandTaskDelete" action="" method="POST">
                <input type="hidden" name="taskId" id="deleteTaskId" value="">
                <button type="submit">Delete</button>
            </form>
            <button type="button" onclick="closePopupExpand()">Close</button>
        </div> 
    </div>

    <?php include('footer.php'); ?>
    
    <script>
        // Dragging functionality for expanded task
        function startDragExpand(event) {
            const taskExpandElement = document.getElementById('expandTask');
            let offsetX = event.clientX - taskExpandElement.getBoundingClientRect().left;
            let offsetY = event.clientY - taskExpandElement.getBoundingClientRect().top;

            function dragMove(e) {
                taskExpandElement.style.position = 'absolute';
                taskExpandElement.style.left = `${e.clientX - offsetX}px`;
                taskExpandElement.style.top = `${e.clientY - offsetY}px`;
            }

            function dragEnd() {
                document.removeEventListener('mousemove', dragMove);
                document.removeEventListener('mouseup', dragEnd);
            }

            document.addEventListener('mousemove', dragMove);
            document.addEventListener('mouseup', dragEnd);
        }
    </script>
</body>
</html>

