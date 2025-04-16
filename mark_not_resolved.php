<?php
require_once 'db_connect.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header('Location: login.php');
    exit;
}

// Ensure only admins can mark as not resolved
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== 'yes') {
    // If not an admin, redirect to the issues list
    header('Location: issues_list.php');
    exit;
}

// Check if the issue_id is provided via POST
if (isset($_POST['issue_id'])) {
    $issue_id = $_POST['issue_id'];

    // Validate that the issue_id is numeric and positive
    if (is_numeric($issue_id) && $issue_id > 0) {
        // Update the status to 'Not Resolved' and reset the close_date
        $stmt = $pdo->prepare("UPDATE iss_issues SET status = 'Not Resolved', close_date = NULL WHERE id = ?");
        if ($stmt->execute([$issue_id])) {
            // Successfully updated, redirect to the issues list
            header('Location: issues_list.php');
            exit;
        } else {
            // Database error
            echo "There was an error updating the issue status.";
        }
    } else {
        // Invalid issue_id
        echo "Invalid issue ID.";
    }
} else {
    // No issue_id provided
    echo "Issue ID is required.";
}
?>
