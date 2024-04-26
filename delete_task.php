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

        // Check if form is submitted (confirmation)
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delete'])) {
            // Query to delete task
            $delete_query = "DELETE FROM tasks WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("i", $task_id);

            if ($delete_stmt->execute()) {
                // Task deleted successfully
                header("Location: task_list.php"); // Redirect to task list page after task deletion
                exit();
            } else {
                // Error handling
                echo "Error: " . $delete_stmt->error;
            }

            $delete_stmt->close();
        }

        // Display confirmation dialog
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Task</title>
</head>
<body>
    <h2>Delete Task</h2>
    <p>Are you sure you want to delete the task "<?php echo $task['title']; ?>"?</p>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?task_id=$task_id"; ?>" method="POST">
        <button type="submit" name="confirm_delete">Yes, Delete</button>
        <a href="task_details.php?task_id=<?php echo $task_id; ?>">Cancel</a>
    </form>
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
