<?php
require_once 'lcrud.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        createComment($_POST['per_id'], $_POST['iss_id'], $_POST['short_comment'], $_POST['long_comment'], $_POST['posted_date']);
    } elseif (isset($_POST['update'])) {
        updateComment($_POST['id'], $_POST['short_comment'], $_POST['long_comment']);
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    deleteComment($_GET['delete']);
}

$comments = getComments();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCRUD Application</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Manage Comments</h1>

    <!-- Create New Comment -->
    <h2>Create New Comment</h2>
    <form method="POST" action="">
        <label for="per_id">Person ID:</label>
        <input type="number" name="per_id" required>
        <br>
        <label for="iss_id">Issue ID:</label>
        <input type="number" name="iss_id" required>
        <br>
        <label for="short_comment">Short Comment:</label>
        <input type="text" name="short_comment" required>
        <br>
        <label for="long_comment">Long Comment:</label>
        <textarea name="long_comment" required></textarea>
        <br>
        <label for="posted_date">Posted Date:</label>
        <input type="date" name="posted_date" required>
        <br>
        <button type="submit" name="create">Create</button>
    </form>

    <!-- List of Comments -->
    <h2>Existing Comments</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Person ID</th>
                <th>Issue ID</th>
                <th>Short Comment</th>
                <th>Long Comment</th>
                <th>Posted Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comments as $comment): ?>
            <tr>
                <td><?php echo $comment['id']; ?></td>
                <td><?php echo $comment['per_id']; ?></td>
                <td><?php echo $comment['iss_id']; ?></td>
                <td><?php echo $comment['short_comment']; ?></td>
                <td><?php echo $comment['long_comment']; ?></td>
                <td><?php echo $comment['posted_date']; ?></td>
                <td>
                    <a href="index.php?edit=<?php echo $comment['id']; ?>">Edit</a> | 
                    <a href="index.php?delete=<?php echo $comment['id']; ?>">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Edit Comment -->
    <?php if (isset($_GET['edit'])): 
        $comment_id = $_GET['edit'];
        $stmt = $pdo->prepare("SELECT * FROM iss_comments WHERE id = ?");
        $stmt->execute([$comment_id]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>
    <h2>Edit Comment</h2>
    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo $comment['id']; ?>">
        <label for="short_comment">Short Comment:</label>
        <input type="text" name="short_comment" value="<?php echo $comment['short_comment']; ?>" required>
        <br>
        <label for="long_comment">Long Comment:</label>
        <textarea name="long_comment" required><?php echo $comment['long_comment']; ?></textarea>
        <br>
        <button type="submit" name="update">Update</button>
    </form>
    <?php endif; ?>
</body>
</html>
