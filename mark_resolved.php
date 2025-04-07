<?php
require_once 'db_connect.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header('Location: login.php');
    exit;
}

// Check if issue_id is provided via POST
if (isset($_POST['issue_id'])) {
    $issue_id = $_POST['issue_id'];

    // Validate that issue_id is a positive integer
    if (is_numeric($issue_id) && $issue_id > 0) {
        // Prepare and execute the query to update the issue status to 'Resolved'
        $stmt = $pdo->prepare("UPDATE iss_issues SET status = 'Resolved' WHERE id = ?");
        $stmt->execute([$issue_id]);

        // Check if the status update was successful
        if ($stmt->rowCount() > 0) {
            // Successfully updated, redirect to the issues list page
            header('Location: issues_list.php');
            exit;
        } else {
            // If no rows were updated, handle the error
            echo "Error: Could not update the issue status.";
        }
    } else {
        // Invalid issue_id
        echo "Invalid issue ID.";
    }
} else {
    // If no issue_id is provided
    echo "Issue ID is required.";
}

?>
