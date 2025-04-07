<?php
require_once 'config.php';
session_start(); // Start the session to access session variables

// Function to create a new comment
function createComment($iss_id, $short_comment, $long_comment, $posted_date) {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        return false; // User is not logged in
    }
    
    global $pdo;
    $per_id = $_SESSION['user_id']; // Get the user ID from the session

    // Insert the new comment into the database
    $stmt = $pdo->prepare("INSERT INTO iss_comments (per_id, iss_id, short_comment, long_comment, posted_date) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$per_id, $iss_id, $short_comment, $long_comment, $posted_date]);
}

// Function to read all comments
function getComments($iss_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM iss_comments WHERE iss_id = ?");
    $stmt->execute([$iss_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to update a comment
function updateComment($id, $short_comment, $long_comment) {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        return false; // User is not logged in
    }

    global $pdo;
    $user_id = $_SESSION['user_id'];

    // Fetch the comment to check if the user is the owner or admin
    $stmt = $pdo->prepare("SELECT * FROM iss_comments WHERE id = ?");
    $stmt->execute([$id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($comment && ($comment['per_id'] == $user_id || isset($_SESSION['admin']) && $_SESSION['admin'] == 'yes')) {
        // User is the owner or an admin, so they can update the comment
        $stmt = $pdo->prepare("UPDATE iss_comments SET short_comment = ?, long_comment = ? WHERE id = ?");
        return $stmt->execute([$short_comment, $long_comment, $id]);
    }

    return false; // User is not authorized to update this comment
}

// Function to delete a comment
function deleteComment($id) {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        return false; // User is not logged in
    }

    global $pdo;
    $user_id = $_SESSION['user_id'];

    // Fetch the comment to check if the user is the owner or admin
    $stmt = $pdo->prepare("SELECT * FROM iss_comments WHERE id = ?");
    $stmt->execute([$id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($comment && ($comment['per_id'] == $user_id || isset($_SESSION['admin']) && $_SESSION['admin'] == 'yes')) {
        // User is the owner or an admin, so they can delete the comment
        $stmt = $pdo->prepare("DELETE FROM iss_comments WHERE id = ?");
        return $stmt->execute([$id]);
    }

    return false; // User is not authorized to delete this comment
}
?>
