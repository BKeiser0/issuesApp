<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_issue'])) {
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $priority = $_POST['priority'];
    $org = $_POST['org'];
    $project = $_POST['project'];

    // Set the open date to the current date and time
    $open_date = date('Y-m-d H:i:s'); // Current date and time in 'Y-m-d H:i:s' format
    $close_date = null; // Close date is not provided during creation

    // Insert new issue into the database
    $stmt = $pdo->prepare("INSERT INTO iss_issues (short_description, long_description, priority, org, project, open_date, close_date) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$short_description, $long_description, $priority, $org, $project, $open_date, $close_date]);

    header('Location: issues_list.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Issue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h1 class="text-center mb-4">Add New Issue</h1>
    <form method="POST" action="add_issue.php">
        <div class="mb-3">
            <label for="short_description" class="form-label">Short Description</label>
            <input type="text" class="form-control" name="short_description" required>
        </div>
        <div class="mb-3">
            <label for="long_description" class="form-label">Long Description</label>
            <textarea class="form-control" name="long_description" rows="3" required></textarea>
        </div>
        <div class="mb-3">
        <label for="project" class="form-label">Issue Priority</label>
        <select class="form-control" name="priority" required>
          <option value="Low">Low</option>
          <option value="Medium">Medium</option>
          <option value="High">High</option>
        </select>

        </div>
        <div class="mb-3">
            <label for="org" class="form-label">Organization</label>
            <input type="text" class="form-control" name="org" required>
        </div>
        <div class="mb-3">
            <label for="project" class="form-label">Project Name</label>
            <input type="text" class="form-control" name="project" required>
        </div>
        <div class="mb-3">
            <label for="open_date" class="form-label">Open Date</label>
            <input type="text" class="form-control" value="<?php echo date('Y-m-d H:i:s'); ?>" readonly>
            <small class="form-text text-muted">This will be set to the current date and time automatically.</small>
        </div>
        <!-- Removed close date input field -->

        <button type="submit" name="add_issue" class="btn btn-primary">Add Issue</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
