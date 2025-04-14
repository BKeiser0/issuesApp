<?php
require_once 'db_connect.php';

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: login.php');
    exit();
}

// Fetch the issue details
if (isset($_GET['id'])) {
    $issue_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM iss_issues WHERE id = ?");
    $stmt->execute([$issue_id]);
    $issue = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Check if the issue exists and if the logged-in user can edit it
if ($issue) {
    // Check if the logged-in user is the one who created the issue or if they are an admin
    if ($_SESSION['user_id'] != $issue['created_by'] && (!isset($_SESSION['admin']) || $_SESSION['admin'] != 'yes')) {
        // If the user is not the creator or an admin, deny access
        echo "You do not have permission to edit this issue.";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_issue'])) {
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $priority = $_POST['priority'];
    $org = $_POST['org'];
    $project = $_POST['project'];

    // Flag for removal request (allowed for admin or the issue creator)
    $remove_pdf = false;
    if (isset($_POST['remove_pdf']) && $_POST['remove_pdf'] == 'on') {
        $remove_pdf = true;
    }

    // Initialize variable for new PDF file path
    $pdf_file = "";

    // Check if a new PDF file has been uploaded
    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/"; // Make sure this directory exists and is writable
        $pdf_name = time() . "_" . basename($_FILES["pdf"]["name"]);
        $target_file = $target_dir . $pdf_name;
        if (move_uploaded_file($_FILES["pdf"]["tmp_name"], $target_file)) {
            $pdf_file = $target_file;
        }
    }

    // If a new PDF is uploaded, update with it (new file takes priority)
    if ($pdf_file !== "") {
        $stmt = $pdo->prepare("UPDATE iss_issues SET short_description = ?, long_description = ?, priority = ?, org = ?, project = ?, pdf_attachment = ? WHERE id = ?");
        $stmt->execute([$short_description, $long_description, $priority, $org, $project, $pdf_file, $issue_id]);
    } 
    // Else, if removal is requested by the issue creator or an admin, remove the PDF attachment
    else if ($remove_pdf && ($_SESSION['user_id'] == $issue['created_by'] || (isset($_SESSION['admin']) && $_SESSION['admin'] === 'yes'))) {
        // Optionally delete the physical file if it exists
        if (!empty($issue['pdf_attachment']) && file_exists($issue['pdf_attachment'])) {
            unlink($issue['pdf_attachment']);
        }
        $stmt = $pdo->prepare("UPDATE iss_issues SET short_description = ?, long_description = ?, priority = ?, org = ?, project = ?, pdf_attachment = '' WHERE id = ?");
        $stmt->execute([$short_description, $long_description, $priority, $org, $project, $issue_id]);
    } 
    // Otherwise, update without changing the pdf_attachment field
    else {
        $stmt = $pdo->prepare("UPDATE iss_issues SET short_description = ?, long_description = ?, priority = ?, org = ?, project = ? WHERE id = ?");
        $stmt->execute([$short_description, $long_description, $priority, $org, $project, $issue_id]);
    }

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
    <!-- Added enctype attribute for file uploads -->
    <form method="POST" action="edit_issue.php?id=<?php echo $issue['id']; ?>" enctype="multipart/form-data">
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
        <!-- New PDF Upload Field -->
        <div class="mb-3">
            <label for="pdf" class="form-label">Upload PDF (optional)</label>
            <input type="file" class="form-control" name="pdf" accept="application/pdf">
        </div>
        <!-- Checkbox to remove existing PDF; shown if a PDF exists and if the user is either admin or the issue creator -->
        <?php if ((isset($_SESSION['admin']) && $_SESSION['admin'] === 'yes' || $_SESSION['user_id'] == $issue['created_by']) && !empty($issue['pdf_attachment'])): ?>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remove_pdf" id="remove_pdf">
                <label class="form-check-label" for="remove_pdf">Remove existing PDF</label>
            </div>
        <?php endif; ?>
        <button type="submit" name="update_issue" class="btn btn-primary">Update Issue</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
