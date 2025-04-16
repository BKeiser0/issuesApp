<?php 
require_once 'db_connect.php';

// Start the session
session_start();

// Check if the user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: login.php');
    exit;
}

// Fetch logged-in user's first and last name
$query = "SELECT fname, lname FROM iss_persons WHERE id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_name = $user ? $user['fname'] . ' ' . $user['lname'] : 'User';

// Get the search term from the URL if it exists
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : null;

// Get the sort selection; default is 'not_resolved'
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'not_resolved';

// Build the WHERE clause for searching across multiple columns
$whereClause = "(
    :search IS NULL
    OR CAST(i.id AS CHAR) LIKE :search
    OR i.project LIKE :search 
    OR i.short_description LIKE :search 
    OR i.long_description LIKE :search 
    OR i.status LIKE :search
    OR i.priority LIKE :search
    OR i.open_date LIKE :search
    OR i.pdf_attachment LIKE :search
    OR p.fname LIKE :search
    OR p.lname LIKE :search
)";

// If the user chose "Not Resolved," filter by i.status = 'Not Resolved'
if ($sort === 'not_resolved') {
    $whereClause .= " AND i.status = 'Not Resolved'";
}

// Full query with optional status filter
$query = "SELECT i.*, p.fname, p.lname 
          FROM iss_issues i
          LEFT JOIN iss_persons p ON i.created_by = p.id
          WHERE $whereClause
          ORDER BY 
              CASE 
                  WHEN i.priority = 'High' THEN 1
                  WHEN i.priority = 'Medium' THEN 2
                  WHEN i.priority = 'Low' THEN 3
              END ASC, i.open_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute(['search' => $search]);
$issues = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $issue_id = $_POST['issue_id'];
    $short_comment = $_POST['short_comment'];
    $long_comment = $_POST['long_comment'];

    $stmt = $pdo->prepare("INSERT INTO iss_comments (per_id, iss_id, short_comment, long_comment, posted_date) 
                           VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $issue_id, $short_comment, $long_comment]);

    // Avoid form re-submission on refresh
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// Fetch comments
$comments_stmt = $pdo->prepare("SELECT c.*, p.fname, p.lname 
                                FROM iss_comments c
                                LEFT JOIN iss_persons p ON c.per_id = p.id
                                ORDER BY c.posted_date DESC");
$comments_stmt->execute();
$comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Issues Tracker</title>

  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <!-- Font Awesome for icons -->
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
    rel="stylesheet"
  />
  <style>
    body {
      font-family: 'Arial', sans-serif;
    }
    .navbar {
      margin-bottom: 20px;
    }
    .card-header {
      background-color: #0056b3;
      color: #fff;
    }
    .table th {
      background-color: #f8f9fa;
    }
    /* Make sure table columns remain responsive */
    th, td {
      vertical-align: middle;
      text-align: left;
    }
    .btn-group > .btn:not(:last-child) {
      margin-right: 5px;
    }
    .btn, .btn-group .btn {
      border-radius: 4px !important;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark ps-3">
  <a class="navbar-brand" href="#">Issues Tracker</a>
  <button
    class="navbar-toggler"
    type="button"
    data-bs-toggle="collapse"
    data-bs-target="#navbarNav"
    aria-controls="navbarNav"
    aria-expanded="false"
    aria-label="Toggle navigation"
  >
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav ms-auto">
      <!-- Logged-in user name -->
      <li class="nav-item ms-3 text-white">
        <span class="nav-link"><?php echo htmlspecialchars($user_name); ?></span>
      </li>
      <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] === 'yes'): ?>
        <li class="nav-item ms-3">
          <a class="nav-link btn btn-success btn-lg text-white" href="people.php">
            People Management
          </a>
        </li>
      <?php endif; ?>
      <li class="nav-item ms-3">
        <a class="nav-link btn btn-success btn-lg text-white" href="add_issue.php">
          Add New Issue
        </a>
      </li>
      <li class="nav-item ms-3 me-3">
        <a class="nav-link btn btn-success btn-lg text-white" href="logout.php">
          Logout
        </a>
      </li>
    </ul>
  </div>
</nav>

<div class="container">
  <h1 class="text-center mb-4">Issues List</h1>

  <!-- Search Form -->
  <form method="GET" action="issues_list.php" class="mb-4">
    <div class="row g-2">
      <!-- 3/4 Column: Search bar + Button -->
      <div class="col-9">
        <div class="input-group">
          <input
            type="text"
            class="form-control"
            name="search"
            placeholder="Search issues..."
            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
          />
          <button class="btn btn-primary" type="submit">Search</button>
        </div>
      </div>
      <!-- 1/4 Column: Sort dropdown (auto-submit on change) -->
      <div class="col-3">
        <div class="input-group">
          <label class="input-group-text" for="sort">Sort By</label>
          <select class="form-select" id="sort" name="sort" onchange="this.form.submit()">
            <option value="not_resolved" <?php echo (!isset($_GET['sort']) || $_GET['sort'] === 'not_resolved') ? 'selected' : ''; ?>>Not Resolved</option>
            <option value="all_issues" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'all_issues') ? 'selected' : ''; ?>>All Issues</option>
          </select>
        </div>
      </div>
    </div>
  </form>

  <div class="card">
    <div class="card-header">
      <h5>Issues</h5>
    </div>
    <div class="card-body">
      <table class="table table-bordered align-middle">
        <thead>
          <tr>
            <th>Issue</th>
            <th>Posted By</th>
            <th>Project</th>
            <th>Title</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($issues as $issue): ?>
            <tr>
              <td><?php echo $issue['id']; ?></td>
              <td><?php echo htmlspecialchars($issue['fname']) . ' ' . htmlspecialchars($issue['lname']); ?></td>
              <td><?php echo htmlspecialchars($issue['project']); ?></td>
              <td><?php echo htmlspecialchars($issue['short_description']); ?></td>
              <td><?php echo htmlspecialchars($issue['status']); ?></td>
              <td><?php echo htmlspecialchars($issue['priority']); ?></td>
              <td>
                <!-- Button Group for uniform look -->
                <div class="btn-group" role="group" aria-label="Actions">
                  <?php if ($issue['created_by'] == $user_id || (isset($_SESSION['admin']) && $_SESSION['admin'] === 'yes')): ?>
                    <a
                      href="edit_issue.php?id=<?php echo $issue['id']; ?>"
                      class="btn btn-warning btn-sm"
                    >
                      <i class="fas fa-edit"></i> Edit
                    </a>
                    <a
                      href="delete_issue.php?id=<?php echo $issue['id']; ?>"
                      onclick="return confirm('Are you sure?');"
                      class="btn btn-danger btn-sm"
                    >
                      <i class="fas fa-trash"></i> Delete
                    </a>
                  <?php endif; ?>

                  <button
                    class="btn btn-primary btn-sm"
                    onclick="showComments(<?php echo $issue['id']; ?>)"
                  >
                    <i class="fas fa-comments"></i> Comments
                  </button>

                  <button
                    class="btn btn-info btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#issueDetailsModal<?php echo $issue['id']; ?>"
                  >
                    <i class="fas fa-info-circle"></i> View Details
                  </button>

                  <?php if ($issue['status'] === 'Not Resolved'): ?>
                    <?php if ($issue['created_by'] == $user_id || (isset($_SESSION['admin']) && $_SESSION['admin'] === 'yes')): ?>
                      <form
                        method="POST"
                        action="mark_resolved.php"
                        style="display:inline;"
                        id="markResolvedForm_<?php echo $issue['id']; ?>"
                      >
                        <input type="hidden" name="issue_id" value="<?php echo $issue['id']; ?>">
                        <button
                          type="button"
                          class="btn btn-success btn-sm"
                          onclick="confirmResolution(<?php echo $issue['id']; ?>)"
                        >
                          Resolved
                        </button>
                      </form>
                    <?php endif; ?>
                  <?php else: ?>
                    <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] === 'yes'): ?>
                      <form
                        method="POST"
                        action="mark_not_resolved.php"
                        style="display:inline;"
                        id="markNotResolvedForm_<?php echo $issue['id']; ?>"
                      >
                        <input type="hidden" name="issue_id" value="<?php echo $issue['id']; ?>">
                        <button
                          type="button"
                          class="btn btn-warning btn-sm"
                          onclick="confirmNotResolved(<?php echo $issue['id']; ?>)"
                        >
                          Un-Resolved
                        </button>
                      </form>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </td>
            </tr>

            <!-- Comments Row -->
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
                    <button type="submit" name="comment" class="btn btn-primary btn-sm">
                      Add Comment
                    </button>
                  </form>

                  <ul class="list-group">
                    <?php foreach ($comments as $comment): ?>
                      <?php if ($comment['iss_id'] == $issue['id']): ?>
                        <li class="list-group-item">
                          <strong>
                            <?php echo htmlspecialchars($comment['fname']) . ' ' . htmlspecialchars($comment['lname']); ?>:
                          </strong>
                          <p>
                            <strong>Short comment:</strong>
                            <?php echo htmlspecialchars($comment['short_comment']); ?>
                          </p>
                          <p>
                            <strong>Long comment:</strong>
                            <?php echo nl2br(htmlspecialchars($comment['long_comment'])); ?>
                          </p>
                          <p><em>Posted on: <?php echo $comment['posted_date']; ?></em></p>
                          <?php if ($comment['per_id'] == $user_id || (isset($_SESSION['admin']) && $_SESSION['admin'] === 'yes')): ?>
                            <a
                              href="edit_comment.php?id=<?php echo $comment['id']; ?>"
                              class="btn btn-warning btn-sm"
                            >
                              Edit Comment
                            </a>
                            <a
                              href="delete_comment.php?id=<?php echo $comment['id']; ?>"
                              onclick="return confirm('Are you sure you want to delete this comment?');"
                              class="btn btn-danger btn-sm"
                            >
                              Delete Comment
                            </a>
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

<!-- Issue Details Modals -->
<?php foreach ($issues as $issue): ?>
  <div
    class="modal fade"
    id="issueDetailsModal<?php echo $issue['id']; ?>"
    tabindex="-1"
    aria-labelledby="issueDetailsModalLabel<?php echo $issue['id']; ?>"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5
            class="modal-title"
            id="issueDetailsModalLabel<?php echo $issue['id']; ?>"
          >
            Issue Details
          </h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <h5>Created At: <?php echo $issue['open_date']; ?></h5>
          <!-- New code to display Close Date -->
          <?php if (!empty($issue['close_date'])): ?>
            <h5>Closed At: <?php echo $issue['close_date']; ?></h5>
          <?php else: ?>
            <h5>Closed At: <em>Not resolved yet</em></h5>
          <?php endif; ?>
          <p><strong>Description:</strong></p>
          <p><?php echo nl2br(htmlspecialchars($issue['long_description'])); ?></p>
          <?php if (!empty($issue['pdf_attachment'])): ?>
            <a
              href="<?php echo htmlspecialchars($issue['pdf_attachment']); ?>"
              target="_blank"
              class="btn btn-info btn-sm"
            >
              View PDF
            </a>
          <?php else: ?>
            <span>No PDF attached</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<!-- Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<script>
  // Show/hide comment rows
  function showComments(issueId) {
    var commentSection = document.getElementById('comments-row-' + issueId);
    commentSection.style.display =
      commentSection.style.display === 'none' ? 'table-row' : 'none';
  }

  // Confirm resolution
  function confirmResolution(issueId) {
    if (confirm('Are you sure you want to mark this issue as resolved?')) {
      document.getElementById('markResolvedForm_' + issueId).submit();
    }
  }

  // Confirm not resolved
  function confirmNotResolved(issueId) {
    if (confirm('Are you sure you want to mark this issue as not resolved?')) {
      document.getElementById('markNotResolvedForm_' + issueId).submit();
    }
  }
</script>
</body>
</html>
