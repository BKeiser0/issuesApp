<?php
require_once 'db_connect.php';

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

    // If the comment exists
    if ($comment) {
        // Ensure the user is the one who made the comment or an admin
        if ($comment['per_id'] != $user_id && (!isset($_SESSION['admin']) || $_SESSION['admin'] != 'yes')) {
            // If not the creator or admin, redirect back
            header('Location: issues_list.php');
            exit;
        }

        // Handle the form submission for editing the comment
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $short_comment = $_POST['short_comment'];
            $long_comment = $_POST['long_comment'];

            // Update the comment in the database
            $stmt = $pdo->prepare("UPDATE iss_comments SET short_comment = ?, long_comment = ? WHERE id = ?");
            $stmt->execute([$short_comment, $long_comment, $comment_id]);

            // Redirect back to the issues list after updating
            header('Location: issues_list.php');
            exit;
        }
    } else {
        // If no comment found, redirect to the issues list
        header('Location: issues_list.php');
        exit;
    }
} else {
    // If no comment ID, redirect to issues list page
    header('Location: issues_list.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Comment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-5">
    <h1>Edit Comment</h1>

    <form method="POST" action="">
        <div class="mb-3">
            <label for="short_comment" class="form-label">Short Comment</label>
            <input type="text" name="short_comment" class="form-control" value="<?php echo htmlspecialchars($comment['short_comment']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="long_comment" class="form-label">Long Comment</label>
            <textarea name="long_comment" class="form-control" rows="3" required><?php echo htmlspecialchars($comment['long_comment']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update Comment</button>
    </form>
</div>

</body>
</html>
