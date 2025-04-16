<?php
require_once 'database/database.php';

// Ensure the request is a POST (avoid GET method for actions that modify data)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if issue_id and status are set in the POST request
    if (isset($_POST['issue_id']) && isset($_POST['status'])) {
        $issue_id = $_POST['issue_id'];
        $status = $_POST['status'];

        // Validate issue_id is a valid integer
        if (filter_var($issue_id, FILTER_VALIDATE_INT)) {
            // Ensure the status is either 'Resolved' or 'Not Resolved'
            if ($status === 'Resolved' || $status === 'Not Resolved') {
                // Update the status in the database
                $stmt = $pdo->prepare("UPDATE iss_issues SET status = ? WHERE id = ?");
                $result = $stmt->execute([$status, $issue_id]);

                // Check if the update was successful
                if ($result) {
                    // Redirect back to the issues list page after updating the status
                    header('Location: issues_list.php');
                    exit;
                } else {
                    // Error handling if the update fails
                    echo "An error occurred while updating the status. Please try again.";
                }
            } else {
                // Invalid status value
                echo "Invalid status value.";
            }
        } else {
            // Invalid issue_id format
            echo "Invalid issue ID.";
        }
    } else {
        // Missing issue_id or status in POST request
        echo "Required parameters are missing.";
    }
} else {
    // If not a POST request, show an error
    echo "Invalid request method.";
}
?>
