<?php
require_once 'db_connect.php';

// Fetch the issue details
if (isset($_GET['id'])) {
    $issue_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM iss_issues WHERE id = ?");
    $stmt->execute([$issue_id]);
    $issue = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_issue'])) {
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $priority = $_POST['priority'];
    $org = $_POST['org'];
    $project = $_POST['project'];

    // Update the issue
    $stmt = $pdo->prepare("UPDATE iss_issues SET short_description = ?, long_description = ?, priority = ?, org = ?, project = ? WHERE id = ?");
    $stmt->execute([$short_description, $long_description, $priority, $org, $project, $issue_id]);

    header('Location: issues_list.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Issue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h1 class="text-center mb-4">Edit Issue</h1>
    <form method="POST" action="edit_issue.php?id=<?php echo $issue['id']; ?>">
        <div class="mb-3">
            <label for="short_description" class="form-label">Short Description</label>
            <input type="text" class="form-control" name="short_description" value="<?php echo htmlspecialchars($issue['short_description']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="long_description" class="form-label">Long Description</label>
            <textarea class="form-control" name="long_description" rows="3" required><?php echo htmlspecialchars($issue['long_description']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="priority" class="form-label">Priority</label>
            <select class="form-control" name="priority" required>
                <option value="Low" <?php echo ($issue['priority'] == 'Low') ? 'selected' : ''; ?>>Low</option>
                <option value="Medium" <?php echo ($issue['priority'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                <option value="High" <?php echo ($issue['priority'] == 'High') ? 'selected' : ''; ?>>High</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="org" class="form-label">Organization</label>
            <input type="text" class="form-control" name="org" value="<?php echo htmlspecialchars($issue['org']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="project" class="form-label">Project Name</label>
            <input type="text" class="form-control" name="project" value="<?php echo htmlspecialchars($issue['project']); ?>" required>
        </div>
        <button type="submit" name="update_issue" class="btn btn-primary">Update Issue</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
