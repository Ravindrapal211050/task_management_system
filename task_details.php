<?php
session_start();
require_once 'db_connection.php'; // Include the file with database connection details

// Check if task ID is provided in the URL
if (isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];

    // Query to fetch task details
    $task_query = "SELECT * FROM tasks WHERE id = ? AND user_id = ?";
    $task_stmt = $conn->prepare($task_query);
    $task_stmt->bind_param("ii", $task_id, $_SESSION['user_id']);
    $task_stmt->execute();
    $task_result = $task_stmt->get_result();

    // Check if task exists
    if ($task_result->num_rows == 1) {
        $task = $task_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Details</title>
</head>
<body>
    <h2>Task Details</h2>
    <div>
        <h3><?php echo $task['title']; ?></h3>
        <p>Description: <?php echo $task['description']; ?></p>
        <p>Due Date: <?php echo $task['due_date']; ?></p>
        <p>Status: <?php echo ucfirst($task['status']); ?></p>
        <p>Priority: <?php echo ucfirst($task['priority']); ?></p>
    </div>

    <!-- Display task details with status update option -->
<div>
    <h2>Task Details</h2>
    <h3><?php echo $task['title']; ?></h3>
    <p>Description: <?php echo $task['description']; ?></p>
    <p>Due Date: <?php echo $task['due_date']; ?></p>
    <p>Status: <?php echo ucfirst($task['status']); ?></p>
    <p>Priority: <?php echo ucfirst($task['priority']); ?></p>

    <!-- Status update links -->
    <p>Status: 
        <?php
        if ($task['status'] == 'pending') {
            echo "<a href='task_details.php?task_id=" . $task_id . "&status=completed'>Mark as Completed</a>";
        } else {
            echo "<a href='task_details.php?task_id=" . $task_id . "&status=pending'>Mark as Pending</a>";
        }
        ?>
    </p>
</div>

</body>
</html>
<?php
    } else {
        // Task not found
        echo "Task not found.";
    }

    $task_stmt->close();
    $conn->close();
} else {
    // Task ID not provided in the URL
    echo "Task ID not provided.";
}
?>
