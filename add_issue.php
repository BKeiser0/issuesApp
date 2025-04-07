<?php
require_once 'db_connect.php';
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit(); // Make sure no further code is executed after redirection
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_issue'])) {
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $priority = $_POST['priority'];
    $org = $_POST['org'];
    $project = $_POST['project'];

    // Get the logged-in user's ID from the session
    $created_by = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Check if the user is logged in (optional, you can customize this logic)
    if ($created_by === null) {
        die("You must be logged in to add an issue.");
    }

    // Set the open date to the current date and time
    $open_date = date('Y-m-d H:i:s'); // Current date and time in 'Y-m-d H:i:s' format
    $close_date = null; // Close date is not provided during creation

    // Handle PDF upload if it exists
    $pdf_attachment = null; // Default to null if no file is uploaded
    if (isset($_FILES['pdf_attachment']) && $_FILES['pdf_attachment']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['pdf_attachment']['tmp_name'];
        $fileName = $_FILES['pdf_attachment']['name'];
        $fileSize = $_FILES['pdf_attachment']['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file extension
        if ($fileExtension !== 'pdf') {
            die("Only PDF files are allowed.");
        }

        // Validate file size (2MB limit)
        if ($fileSize > 2 * 1024 * 1024) {
            die("File size exceeds 2 MB limit.");
        }

        // Generate a unique file name
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadDir = './uploads/'; // Specify your uploads directory here
        $destPath = $uploadDir . $newFileName;

        // Create the uploads directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move the uploaded file to the destination directory
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $pdf_attachment = $destPath; // Store the file path
        } else {
            die("Error moving the uploaded file.");
        }
    }

    // Insert new issue into the database, including the PDF path and created_by field
    $stmt = $pdo->prepare("INSERT INTO iss_issues (short_description, long_description, priority, org, project, open_date, close_date, pdf_attachment, created_by) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$short_description, $long_description, $priority, $org, $project, $open_date, $close_date, $pdf_attachment, $created_by]);

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
    <form method="POST" action="add_issue.php" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="short_description" class="form-label">Short Description</label>
        <input type="text" class="form-control" name="short_description" required>
    </div>
    <div class="mb-3">
        <label for="long_description" class="form-label">Long Description</label>
        <textarea class="form-control" name="long_description" rows="3" required></textarea>
    </div>
    <div class="mb-3">
        <label for="priority" class="form-label">Issue Priority</label>
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
    
    <!-- PDF File Upload Field -->
    <div class="mb-3">
        <label for="pdf_attachment" class="form-label">Attach PDF (Max 2 MB)</label>
        <input type="file" class="form-control" name="pdf_attachment" accept="application/pdf">
    </div>

    <button type="submit" name="add_issue" class="btn btn-primary">Add Issue</button>
</form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
