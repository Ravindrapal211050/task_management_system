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

        // Check if form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $due_date = $_POST['due_date'];
            $priority = $_POST['priority'];

            // Query to update task details
            $update_query = "UPDATE tasks SET title = ?, description = ?, due_date = ?, priority = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ssssi", $title, $description, $due_date, $priority, $task_id);

            if ($update_stmt->execute()) {
                // Task updated successfully
                header("Location: task_details.php?task_id=$task_id"); // Redirect to task details page after task update
                exit();
            } else {
                // Error handling
                echo "Error: " . $update_stmt->error;
            }

            $update_stmt->close();
        }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
</head>
<body>
    <h2>Edit Task</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?task_id=$task_id"; ?>" method="POST">
        <label>Title:</label><br>
        <input type="text" name="title" value="<?php echo $task['title']; ?>" required><br>
        <label>Description:</label><br>
        <textarea name="description" required><?php echo $task['description']; ?></textarea><br>
        <label>Due Date:</label><br>
        <input type="date" name="due_date" value="<?php echo $task['due_date']; ?>" required><br>
        <label>Priority:</label><br>
        <select name="priority" required>
            <option value="low" <?php if ($task['priority'] == 'low') echo 'selected'; ?>>Low</option>
            <option value="medium" <?php if ($task['priority'] == 'medium') echo 'selected'; ?>>Medium</option>
            <option value="high" <?php if ($task['priority'] == 'high') echo 'selected'; ?>>High</option>
        </select><br>
        <button type="submit">Update Task</button>
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
