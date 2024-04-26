<?php
session_start();
require_once 'db_connection.php';


$limit = 5; // Number of tasks per page
$page = isset($_GET['page']) ? $_GET['page'] : 1; 
$offset = ($page - 1) * $limit;

// Query to fetch total number of tasks
$total_tasks_query = "SELECT COUNT(*) AS total_tasks FROM tasks WHERE user_id = ?";
$total_tasks_stmt = $conn->prepare($total_tasks_query);
$total_tasks_stmt->bind_param("i", $_SESSION['user_id']);
$total_tasks_stmt->execute();
$total_tasks_result = $total_tasks_stmt->get_result();
$total_tasks_row = $total_tasks_result->fetch_assoc();
$total_tasks = $total_tasks_row['total_tasks'];

// Calculate total pages
$total_pages = ceil($total_tasks / $limit);

// Query to fetch tasks for current page
$tasks_query = "SELECT * FROM tasks WHERE user_id = ? LIMIT ?, ?";
$tasks_stmt = $conn->prepare($tasks_query);
$tasks_stmt->bind_param("iii", $_SESSION['user_id'], $offset, $limit);
$tasks_stmt->execute();
$tasks_result = $tasks_stmt->get_result();

// Fetch tasks and display them
while ($row = $tasks_result->fetch_assoc()) {
    // Output task data
    echo "<div>";
    echo "<h3>" . $row['title'] . "</h3>";
    echo "<p>" . $row['description'] . "</p>";
    echo "<p>Due Date: " . $row['due_date'] . "</p>";
    echo "<p>Priority: " . $row['priority'] . "</p>";
    echo "</div>";
}

// Pagination links
echo "<div class='pagination'>";
for ($i = 1; $i <= $total_pages; $i++) {
    echo "<a href='task_list.php?page=$i'>$i</a>";
}
echo "</div>";

$total_tasks_stmt->close();
$tasks_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task List</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.pagination a').click(function(e) {
                e.preventDefault();
                var page = $(this).attr('href').split('=')[1];
                $.ajax({
                    url: 'task_list.php?page=' + page,
                    type: 'GET',
                    success: function(data) {
                        $('.task-list').html(data);
                    }
                });
            });
        });
    </script>
    <style>
        .task-list {
            margin-bottom: 20px;
        }

        .pagination {
            margin-top: 20px;
        }

        .pagination a {
            padding: 5px 10px;
            margin-right: 5px;
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
            border-radius: 3px;
        }

        .pagination a:hover {
            background-color: #ddd;
        }

        .pagination .active {
            background-color: #007bff;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="task-list">
        <!-- Task list content will be loaded here -->
    </div>
    <div class="pagination">
        <!-- Pagination links will be loaded here -->
    </div>

    <!-- Display task list with status update options -->
<div>
    <h2>Task List</h2>
    <?php
    // Fetch tasks and display them
    while ($row = $tasks_result->fetch_assoc()) {
        // Output task data
        echo "<div>";
        echo "<h3>" . $row['title'] . "</h3>";
        echo "<p>" . $row['description'] . "</p>";
        echo "<p>Due Date: " . $row['due_date'] . "</p>";
        echo "<p>Priority: " . $row['priority'] . "</p>";

        // Status update links
        echo "<p>Status: ";
        if ($row['status'] == 'pending') {
            echo "<a href='task_list.php?task_id=" . $row['id'] . "&status=completed'>Mark as Completed</a>";
        } else {
            echo "<a href='task_list.php?task_id=" . $row['id'] . "&status=pending'>Mark as Pending</a>";
        }
        echo "</p>";

        echo "</div>";
    }
    ?>
</div>

</body>
</html>
