<?php
// =================================================================
// FILE: update_fulfillment_status.php
// NEW FILE - Handles the fulfillment logic.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Protection ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$allowed_roles = ['Super Admin', 'Stores'];
if (!in_array($_SESSION['role_name'], $allowed_roles)) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    $issuer_id = $_SESSION['user_id'];

    // Validate action
    if ($action !== 'Issued') {
        header("Location: fulfill_requests.php?status=error&message=" . urlencode("Invalid action."));
        exit();
    }

    // Update the request status in the database
    $sql = "UPDATE needle_requests 
            SET request_status = ?, stores_issuer_id_fk = ?, issued_at = NOW() 
            WHERE request_id = ? AND request_status = 'Approved'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $action, $issuer_id, $request_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            header("Location: fulfill_requests.php?status=success&message=" . urlencode("Request marked as issued."));
        } else {
            header("Location: fulfill_requests.php?status=error&message=" . urlencode("Request could not be updated. It may have already been actioned."));
        }
    } else {
        header("Location: fulfill_requests.php?status=error&message=" . urlencode("Database error: " . $stmt->error));
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: fulfill_requests.php");
    exit();
}
?>