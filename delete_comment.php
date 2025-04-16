<?php
require_once 'database/database.php';

// Start the session
session_start();

// Check if the user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit;
}

// Check if the comment ID is provided
if (isset($_GET['id'])) {
    $comment_id = $_GET['id'];

    // Fetch the comment from the database
    $stmt = $pdo->prepare("SELECT * FROM iss_comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ensure the user is the one who made the comment or an admin
    if ($comment && ($comment['per_id'] == $user_id || isset($_SESSION['admin']) && $_SESSION['admin'] == 'yes')) {
        // Delete the comment from the database
        $stmt = $pdo->prepare("DELETE FROM iss_comments WHERE id = ?");
        $stmt->execute([$comment_id]);

        // Redirect back to the issues list after deletion
        header('Location: issues_list.php');
        exit;
    } else {
        // If not authorized, redirect back to issues list
        header('Location: issues_list.php');
        exit;
    }
} else {
    // If no comment ID, redirect to issues list
    header('Location: issues_list.php');
    exit;
}
