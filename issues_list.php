<?php
require_once 'db_connect.php';

// Start the session
session_start();

// Check if the user is logged in and store the user ID
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    // Redirect to login page if not logged in (you can customize this as needed)
    header('Location: login.php');
    exit;
}

// Fetch issues from the database, ordered by priority (High -> Medium -> Low)
$stmt = $pdo->prepare("SELECT * FROM iss_issues 
                       ORDER BY 
                           CASE 
                               WHEN priority = 'High' THEN 1
                               WHEN priority = 'Medium' THEN 2
                               WHEN priority = 'Low' THEN 3
                           END ASC, open_date DESC");
$stmt->execute();
$issues = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $issue_id = $_POST['issue_id'];
    $short_comment = $_POST['short_comment'];
    $long_comment = $_POST['long_comment'];

    // Insert comment into the database
    $stmt = $pdo->prepare("INSERT INTO iss_comments (per_id, iss_id, short_comment, long_comment, posted_date) 
                           VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $issue_id, $short_comment, $long_comment]);
}

// Fetch all comments with the username from the iss_persons table
$comments_stmt = $pdo->prepare("SELECT c.*, p.fname, p.lname FROM iss_comments c 
                                LEFT JOIN iss_persons p ON c.per_id = p.id
                                ORDER BY c.posted_date DESC");
$comments_stmt->execute();
$comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issues List with Comments</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar (optional) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Issues Tracker</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
            <!-- Add "People Management" button only for admins -->
            <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 'yes'): ?>
                <li class="nav-item">
                    <a class="nav-link btn btn-success btn-lg text-white ms-3" href="people.php">People Management</a>
                </li>
            <?php endif; ?>

            <!-- Other navbar items -->
            <li class="nav-item">
                <a class="nav-link btn btn-success btn-lg text-white" href="add_issue.php">Add New Issue</a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-success btn-lg text-white ms-3" href="login.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>



<div class="container my-5">
    <!-- Heading -->
    <h1 class="text-center mb-4">Issues List</h1>

    <!-- Issues Table -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5>Issue Details</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">Issue ID</th>
                        <th scope="col">Project Name</th>
                        <th scope="col">Issue Title</th>
                        <th scope="col">Description</th>
                        <th scope="col">Status</th> <!-- New Status Column -->
                        <th scope="col">Priority</th>
                        <th scope="col">Created At</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($issues as $issue): ?>
                    <tr>
                        <td><?php echo $issue['id']; ?></td>
                        <td><?php echo htmlspecialchars($issue['project']); ?></td>
                        <td><?php echo htmlspecialchars($issue['short_description']); ?></td>
                        <td><?php echo htmlspecialchars($issue['long_description']); ?></td>
                        <td>
            <?php echo htmlspecialchars($issue['status']); ?> <!-- Display Status -->
        </td>
                        <td><?php echo htmlspecialchars($issue['priority']); ?></td>
                        <td><?php echo $issue['open_date']; ?></td>
                        <td>
    <!-- Check if the current user is the creator or an admin -->
    <?php if ($issue['created_by'] == $user_id || (isset($_SESSION['admin']) && $_SESSION['admin'] == 'yes')): ?>
        <a href="edit_issue.php?id=<?php echo $issue['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
        <a href="delete_issue.php?id=<?php echo $issue['id']; ?>" onclick="return confirm('Are you sure?');" class="btn btn-danger btn-sm">Delete</a>
    <?php else: ?>
        <!-- Deny access to editing or deleting -->
        <!-- <span class="text-muted">No actions available</span> -->
    <?php endif; ?>

    <!-- Comments Button -->
    <button class="btn btn-primary btn-sm" onclick="showComments(<?php echo $issue['id']; ?>)">Comments</button>

    <!-- Mark as Resolved Button (only shown if the issue is not resolved) -->
    <?php if ($issue['status'] == 'Not Resolved'): ?>
        <form method="POST" action="mark_resolved.php" style="display:inline;" id="markResolvedForm_<?php echo $issue['id']; ?>">
            <input type="hidden" name="issue_id" value="<?php echo $issue['id']; ?>">
            <button type="button" class="btn btn-success btn-sm" onclick="confirmResolution(<?php echo $issue['id']; ?>)">
                Mark as Resolved
            </button>
        </form>
    <?php else: ?>
        <!-- If the issue is resolved, show 'Mark as Not Resolved' button for admins -->
        <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 'yes'): ?>
            <form method="POST" action="mark_not_resolved.php" style="display:inline;" id="markNotResolvedForm_<?php echo $issue['id']; ?>">
                <input type="hidden" name="issue_id" value="<?php echo $issue['id']; ?>">
                <button type="button" class="btn btn-warning btn-sm" onclick="confirmNotResolved(<?php echo $issue['id']; ?>)">
                    Mark as Not Resolved
                </button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</td>

                    </tr>

                    <!-- Comments Section for this Issue -->
                    <tr id="comments-row-<?php echo $issue['id']; ?>" style="display:none;">
                        <td colspan="7">
                            <div class="comment-section mt-3">
                                <!-- Add Comment Form -->
                                <form method="POST" action="" class="mb-3">
                                    <input type="hidden" name="issue_id" value="<?php echo $issue['id']; ?>">
                                    <div class="mb-3">
                                        <label for="short_comment" class="form-label">Short Comment</label>
                                        <input type="text" name="short_comment" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="long_comment" class="form-label">Long Comment</label>
                                        <textarea name="long_comment" class="form-control" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" name="comment" class="btn btn-primary btn-sm">Add Comment</button>
                                </form>

                                <!-- Display Comments for this Issue -->
                                <ul class="list-group mt-3">
                                    <?php foreach ($comments as $comment): ?>
                                        <?php if ($comment['iss_id'] == $issue['id']): ?>
                                            <li class="list-group-item">
                                                <strong><?php echo htmlspecialchars($comment['fname']) . ' ' . htmlspecialchars($comment['lname']); ?>:</strong> <?php echo htmlspecialchars($comment['short_comment']); ?>
                                                <p><?php echo htmlspecialchars($comment['long_comment']); ?></p>
                                                <p><em>Posted on: <?php echo $comment['posted_date']; ?></em></p>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<!-- JavaScript to toggle comment section visibility -->
<script>
    function showComments(issueId) {
        var commentSection = document.getElementById('comments-row-' + issueId);
        if (commentSection.style.display === "none") {
            commentSection.style.display = "table-row"; // Display the comment section
        } else {
            commentSection.style.display = "none"; // Hide the comment section
        }
    }

    function confirmResolution(issueId) {
        // Show confirmation dialog
        var confirmed = confirm("Are you sure you want to mark this issue as resolved?");
        
        if (confirmed) {
            // If confirmed, submit the form
            document.getElementById('markResolvedForm_' + issueId).submit();
        }
    }


    // Confirm dialog for Mark as Resolved
    function confirmResolution(issueId) {
        var confirmed = confirm("Are you sure you want to mark this issue as resolved?");
        
        if (confirmed) {
            document.getElementById('markResolvedForm_' + issueId).submit();
        }
    }


     // Confirm dialog for Mark as Not Resolved (for admins)
     function confirmNotResolved(issueId) {
        var confirmed = confirm("Are you sure you want to mark this issue as not resolved?");
        
        if (confirmed) {
            document.getElementById('markNotResolvedForm_' + issueId).submit();
        }
    }
    
</script>

</body>
</html>