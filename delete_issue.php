<?php
require_once 'db_connect.php';

if (isset($_GET['id'])) {
    $issue_id = $_GET['id'];

    // Delete the issue from the database
    $stmt = $pdo->prepare("DELETE FROM iss_issues WHERE id = ?");
    $stmt->execute([$issue_id]);

    header('Location: issues_list.php');
    exit();
}
?>
