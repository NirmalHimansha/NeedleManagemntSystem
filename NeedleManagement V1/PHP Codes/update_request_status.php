<?php
// =================================================================
// FILE: update_request_status.php
// NEW FILE - Handles the approve/reject logic.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Protection ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$allowed_roles = ['Super Admin', 'Manager'];
if (!in_array($_SESSION['role_name'], $allowed_roles)) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    $approver_id = $_SESSION['user_id'];

    // Validate action
    if ($action !== 'Approved' && $action !== 'Rejected') {
        header("Location: approve_requests.php?status=error&message=" . urlencode("Invalid action."));
        exit();
    }

    // Update the request status in the database
    $sql = "UPDATE needle_requests 
            SET request_status = ?, manager_approver_id_fk = ?, approved_at = NOW() 
            WHERE request_id = ? AND request_status = 'Pending Approval'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $action, $approver_id, $request_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            header("Location: approve_requests.php?status=success&message=" . urlencode("Request has been " . strtolower($action) . "."));
        } else {
            header("Location: approve_requests.php?status=error&message=" . urlencode("Request could not be updated. It may have already been actioned."));
        }
    } else {
        header("Location: approve_requests.php?status=error&message=" . urlencode("Database error: " . $stmt->error));
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: approve_requests.php");
    exit();
}
?>
