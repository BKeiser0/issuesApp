<?php
require_once 'db_connect.php'; // Ensure this file contains your database connection

// Fetch issues from the database
$stmt = $pdo->prepare("SELECT * FROM iss_issues ORDER BY project ASC");
$stmt->execute();
$issues = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Issues List</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Issues List</h1>
    
    <table border="1">
        <thead>
            <tr>
                <th>Issue ID</th>
                <th>Project Name</th>
                <th>Issue Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach ($issues as $issue): ?>
    <tr>
        <td><?php echo $issue['id']; ?></td>
        <td><?php echo htmlspecialchars($issue['project']); ?></td>
        <td><?php echo htmlspecialchars($issue['short_description']); ?></td>
        <td><?php echo htmlspecialchars($issue['long_description']); ?></td>
        <td><?php echo htmlspecialchars($issue['priority']); ?></td>
        <td><?php echo $issue['open_date']; ?></td>
        <td>
            <a href="edit_issue.php?id=<?php echo $issue['id']; ?>">Edit</a> | 
            <a href="delete_issue.php?id=<?php echo $issue['id']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</tbody>

    </table>
    
    <br>
    <a href="add_issue.php">Add New Issue</a>
</body>
</html>
