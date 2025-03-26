<?php
require_once 'db_connect.php';

// Start the session
session_start();

// Check if the user is logged in and if they are an admin
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$is_admin = isset($_SESSION['admin']) && $_SESSION['admin'] == 'yes';

if (!$user_id || !$is_admin) {
    // Redirect to the main page or login page if the user is not an admin
    header('Location: issues_list.php');
    exit;
}

// Fetch all users from the database
$stmt = $pdo->prepare("SELECT * FROM iss_persons");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle actions like assign admin or delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['assign_admin'])) {
        // Assign admin rights by updating the 'admin' column to 'yes'
        $user_to_assign = $_POST['user_id'];
        $stmt = $pdo->prepare("UPDATE iss_persons SET admin = 'yes' WHERE id = ?");
        $stmt->execute([$user_to_assign]);
    }

    if (isset($_POST['delete_user'])) {
        // Delete user
        $user_to_delete = $_POST['user_id'];
        $stmt = $pdo->prepare("DELETE FROM iss_persons WHERE id = ?");
        $stmt->execute([$user_to_delete]);
    }

    // Refresh page after action
    header("Location: people.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - People Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar (same as before) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Issues Tracker</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link" href="issues_list.php">Back to Issues</a>
            </li>
            <li class="nav-item">
                <a class="nav-link btn btn-danger" href="login.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container my-5">
    <h1 class="text-center mb-4">Manage Users</h1>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5>User Details</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo $user['admin'] == 'yes' ? 'Admin' : 'User'; ?></td>
                            <td>
                                <!-- Assign admin action -->
                                <?php if ($user['admin'] == 'no'): ?>
                                    <form method="POST" style="display:inline;" id="assignAdminForm_<?php echo $user['id']; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="assign_admin" value="1"> <!-- Hidden field to indicate admin assignment -->
                                        <button type="button" class="btn btn-success btn-sm" onclick="confirmMakeAdmin(<?php echo $user['id']; ?>)">Make Admin</button>
                                    </form>
                                <?php else: ?>
                                    <!-- Already an admin, so no action here -->
                                    <button class="btn btn-secondary btn-sm" disabled>Admin</button>
                                <?php endif; ?>

                                <!-- Delete user action -->
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                                </form>
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

<!-- JavaScript to add confirmation dialog for making a user an admin -->
<script>
    function confirmMakeAdmin(userId) {
        var confirmed = confirm("Are you sure you want to make this user an admin?");
        
        if (confirmed) {
            // Submit the form if confirmed
            var form = document.getElementById('assignAdminForm_' + userId);
            form.submit();  // This will submit the form and update the user's admin status
        }
    }
</script>

</body>
</html>
