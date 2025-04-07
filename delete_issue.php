<?php
require_once 'db_connect.php';

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: login.php');
    exit();
}

// Get the issue ID from the URL if it exists
if (isset($_GET['id'])) {
    $issue_id = $_GET['id'];

    // Fetch the issue's details to check if the logged-in user created it
    $stmt = $pdo->prepare("SELECT created_by FROM iss_issues WHERE id = ?");
    $stmt->execute([$issue_id]);
    $issue = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the issue exists and the user is allowed to delete it
    if ($issue) {
        // Check if the logged-in user is the one who created the issue or is an admin
        if ($_SESSION['user_id'] == $issue['created_by'] || isset($_SESSION['admin']) && $_SESSION['admin'] == 'yes') {
            // Delete the issue from the database
            $stmt = $pdo->prepare("DELETE FROM iss_issues WHERE id = ?");
            $stmt->execute([$issue_id]);

            // Redirect to the issues list after deletion
            header('Location: issues_list.php');
            exit();
        } else {
            // If the user is not allowed to delete the issue
            echo "You do not have permission to delete this issue.";
            exit();
        }
    } else {
        // If the issue does not exist
        echo "Issue not found.";
        exit();
    }
}
?>
