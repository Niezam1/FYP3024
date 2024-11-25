<?php
include 'config.php';

// Start session
session_start();
$user_id = $_SESSION['user_id'];

if (isset($_POST['submit'])) {

    // Retrieve POST data
    $table_selected = $_POST['availability'];
    $table_time = $_POST['hidden_time'];
    $table_day = $_POST['hidden_day'];
    $table_week = $_POST['hidden_week'];
    $cell_id = $_POST['hidden_cellId'];

    // First, delete any existing entry for the same cell, week, and user
    $deleteQuery = $conn->prepare("DELETE FROM `timetable` WHERE `CellNo` = ? AND `Day` = ? AND `Time` = ? AND `Week` = ? AND `User_id` = ?");
    
    // Check if prepare failed, then display error messages
    if ($deleteQuery === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $deleteQuery->bind_param('isisi', $cell_id, $table_day, $table_time, $table_week, $user_id);

    // Execute the delete query
    if (!$deleteQuery->execute()) {
        die('Delete failed: ' . htmlspecialchars($deleteQuery->error));
    }

    // Close delete statement
    $deleteQuery->close();

    // Prepare the SQL statement to insert the new data
    $stmt = $conn->prepare("INSERT INTO `timetable` (`CellNo`, `Day`, `Time`, `Week`, `availability`, `User_id`) VALUES (?, ?, ?, ?, ?, ?)");

    // Check if prepare failed, then display error messages
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param('isiisi', $cell_id, $table_day, $table_time, $table_week, $table_selected, $user_id);

    // Execute the statement
    if ($stmt->execute()) {
        // If the insert is successful, redirect to dean index page and show success message
        echo '
        <script>
            alert("Record inserted successfully!"); 
            window.location.href = "index.php?week=' . $table_week . '";
        </script>';
    } else {
        die('Insert query failed: ' . htmlspecialchars($stmt->error));
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
