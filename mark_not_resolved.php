<?php
require_once 'db_connect.php';
session_start();

// Ensure only admins can mark as not resolved
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== 'yes') {
    // If not an admin, redirect to the issues list
    header('Location: issues_list.php');
    exit;
}

// Check if the issue_id is provided via POST
if (isset($_POST['issue_id'])) {
    $issue_id = $_POST['issue_id'];

    // Update the status to 'Not Resolved' in the database
    $stmt = $pdo->prepare("UPDATE iss_issues SET status = 'Not Resolved' WHERE id = ?");
    $stmt->execute([$issue_id]);
}

// Redirect back to the issues list page after updating the status
header('Location: issues_list.php');
exit;
?>
