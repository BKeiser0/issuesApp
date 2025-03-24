<?php
require_once 'db_connect.php';

// Check if issue_id and status are set in the URL
if (isset($_GET['issue_id']) && isset($_GET['status'])) {
    $issue_id = $_GET['issue_id'];
    $status = $_GET['status'];

    // Ensure the status is either 'Resolved' or 'Not Resolved'
    if ($status == 'Resolved' || $status == 'Not Resolved') {
        // Update the status in the database
        $stmt = $pdo->prepare("UPDATE iss_issues SET status = ? WHERE id = ?");
        $stmt->execute([$status, $issue_id]);
    }
}

// Redirect back to the issues list page after updating the status
header('Location: issues_list.php');
exit;
?>
