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

// Fetch the logged-in user's first and last name
$query = "SELECT fname, lname FROM iss_persons WHERE id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_name = $user ? $user['fname'] . ' ' . $user['lname'] : 'User'; // Default to 'User' if not found

// Get the search term from the URL if it exists
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '';

// Modify the query to include the search term if it's provided
$query = "SELECT i.*, p.fname, p.lname 
          FROM iss_issues i
          LEFT JOIN iss_persons p ON i.created_by = p.id
          WHERE i.project LIKE :search 
             OR i.short_description LIKE :search
             OR i.long_description LIKE :search
             OR i.status LIKE :search
          ORDER BY 
              CASE 
                  WHEN priority = 'High' THEN 1
                  WHEN priority = 'Medium' THEN 2
                  WHEN priority = 'Low' THEN 3
              END ASC, open_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute(['search' => $search]);
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

    // Redirect to the same page to avoid resubmitting the form on refresh
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;  // Make sure to exit after the redirect
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
<nav class="navbar navbar-expand-lg navbar-dark bg-dark ps-3">
    <a class="navbar-brand" href="#">Issues Tracker</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
            <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 'yes'): ?>
                <li class="nav-item ms-3">
                    <a class="nav-link btn btn-success btn-lg text-white" href="people.php">People Management</a>
                </li>
            <?php endif; ?>
            <li class="nav-item ms-3">
                <a class="nav-link btn btn-success btn-lg text-white" href="add_issue.php">Add New Issue</a>
            </li>
            <!-- Display the user's name in the navbar -->
            <li class="nav-item ms-3 text-white">
                <span class="nav-link"><?php echo htmlspecialchars($user_name); ?></span>
            </li>
            <li class="nav-item ms-3 me-3"> <!-- Add margin-right to only the Logout button -->
                <a class="nav-link btn btn-success btn-lg text-white" href="login.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container my-5">
    <h1 class="text-center mb-4">Issues List</h1>

    <!-- Search Form -->
    <form method="GET" action="issues_list.php" class="mb-4">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Search issues..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5>Issue Details</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">Issue ID</th>
                        <th scope="col">Posted By</th> <!-- New column for the person who posted the issue -->
                        <th scope="col">Project Name</th>
                        <th scope="col">Issue Title</th>
                        <th scope="col">Description</th>
                        <th scope="col">Status</th>
                        <th scope="col">Priority</th>
                        <th scope="col">Created At</th>
                        <th scope="col">Actions</th>
                        <th scope="col">PDF Attachment</th> <!-- New Column for PDF Link -->
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($issues as $issue): ?>
                    <tr>
                        <td><?php echo $issue['id']; ?></td>
                        <td><?php echo htmlspecialchars($issue['fname']) . ' ' . htmlspecialchars($issue['lname']); ?></td> <!-- Display name of the user who posted the issue -->
                        <td><?php echo htmlspecialchars($issue['project']); ?></td>
                        <td><?php echo htmlspecialchars($issue['short_description']); ?></td>
                        <td><?php echo htmlspecialchars($issue['long_description']); ?></td>
                        <td><?php echo htmlspecialchars($issue['status']); ?></td>
                        <td><?php echo htmlspecialchars($issue['priority']); ?></td>
                        <td><?php echo $issue['open_date']; ?></td>
                        <td>
                            <?php if ($issue['created_by'] == $user_id || (isset($_SESSION['admin']) && $_SESSION['admin'] == 'yes')): ?>
                                <a href="edit_issue.php?id=<?php echo $issue['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete_issue.php?id=<?php echo $issue['id']; ?>" onclick="return confirm('Are you sure?');" class="btn btn-danger btn-sm">Delete</a>
                            <?php endif; ?>

                            <button class="btn btn-primary btn-sm" onclick="showComments(<?php echo $issue['id']; ?>)">Comments</button>

                            <?php if ($issue['status'] == 'Not Resolved'): ?>
                                <form method="POST" action="mark_resolved.php" style="display:inline;" id="markResolvedForm_<?php echo $issue['id']; ?>">
                                    <input type="hidden" name="issue_id" value="<?php echo $issue['id']; ?>">
                                    <button type="button" class="btn btn-success btn-sm" onclick="confirmResolution(<?php echo $issue['id']; ?>)">Mark as Resolved</button>
                                </form>
                            <?php else: ?>
                                <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 'yes'): ?>
                                    <form method="POST" action="mark_not_resolved.php" style="display:inline;" id="markNotResolvedForm_<?php echo $issue['id']; ?>">
                                        <input type="hidden" name="issue_id" value="<?php echo $issue['id']; ?>">
                                        <button type="button" class="btn btn-warning btn-sm" onclick="confirmNotResolved(<?php echo $issue['id']; ?>)">Mark as Not Resolved</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>

                        <!-- Display the PDF Link if it exists -->
                        <td>
                            <?php if (!empty($issue['pdf_attachment'])): ?>
                                <a href="<?php echo htmlspecialchars($issue['pdf_attachment']); ?>" target="_blank" class="btn btn-info btn-sm">View PDF</a>
                            <?php else: ?>
                                <span>No PDF</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr id="comments-row-<?php echo $issue['id']; ?>" style="display:none;">
                        <td colspan="7">
                            <div class="comment-section mt-3">
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

                                <ul class="list-group mt-3">
                                    <?php foreach ($comments as $comment): ?>
                                        <?php if ($comment['iss_id'] == $issue['id']): ?>
                                            <li class="list-group-item">
                                                <strong><?php echo htmlspecialchars($comment['fname']) . ' ' . htmlspecialchars($comment['lname']); ?>:</strong> 
                                                <?php echo htmlspecialchars($comment['short_comment']); ?>
                                                <p><?php echo htmlspecialchars($comment['long_comment']); ?></p>
                                                <p><em>Posted on: <?php echo $comment['posted_date']; ?></em></p>

                                                <?php if ($comment['per_id'] == $user_id || isset($_SESSION['admin']) && $_SESSION['admin'] == 'yes'): ?>
                                                    <a href="edit_comment.php?id=<?php echo $comment['id']; ?>" class="btn btn-warning btn-sm">Edit Comment</a>
                                                    <a href="delete_comment.php?id=<?php echo $comment['id']; ?>" onclick="return confirm('Are you sure you want to delete this comment?');" class="btn btn-danger btn-sm">Delete Comment</a>
                                                <?php endif; ?>
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

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<script>
    function showComments(issueId) {
        var commentSection = document.getElementById('comments-row-' + issueId);
        if (commentSection.style.display === "none") {
            commentSection.style.display = "table-row";
        } else {
            commentSection.style.display = "none";
        }
    }

    function confirmResolution(issueId) {
        var confirmed = confirm("Are you sure you want to mark this issue as resolved?");
        if (confirmed) {
            document.getElementById('markResolvedForm_' + issueId).submit();
        }
    }

    function confirmNotResolved(issueId) {
        var confirmed = confirm("Are you sure you want to mark this issue as not resolved?");
        if (confirmed) {
            document.getElementById('markNotResolvedForm_' + issueId).submit();
        }
    }
</script>

</body>
</html>
