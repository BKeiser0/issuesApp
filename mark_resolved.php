<?php
require_once 'db_connect.php';

// Check if issue_id and status are set in the URL
if (isset($_POST['issue_id'])) {
    $issue_id = $_POST['issue_id'];

    // Update the status to 'Resolved' in the database
    $stmt = $pdo->prepare("UPDATE iss_issues SET status = 'Resolved' WHERE id = ?");
    $stmt->execute([$issue_id]);
}

// Redirect back to the issues list page after updating the status
header('Location: issues_list.php');
exit;
?>
