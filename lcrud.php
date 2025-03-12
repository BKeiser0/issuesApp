<?php
require_once 'config.php';

// Function to create a new comment
function createComment($per_id, $iss_id, $short_comment, $long_comment, $posted_date) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO iss_comments (per_id, iss_id, short_comment, long_comment, posted_date) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$per_id, $iss_id, $short_comment, $long_comment, $posted_date]);
}

// Function to read all comments
function getComments() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM iss_comments");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to update a comment
function updateComment($id, $short_comment, $long_comment) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE iss_comments SET short_comment = ?, long_comment = ? WHERE id = ?");
    return $stmt->execute([$short_comment, $long_comment, $id]);
}

// Function to delete a comment
function deleteComment($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM iss_comments WHERE id = ?");
    return $stmt->execute([$id]);
}
?>
