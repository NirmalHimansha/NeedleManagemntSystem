<?php
// =================================================================
// FILE: add_purchase_request_process.php
// NEW FILE - Handles the purchase request logic.
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

    $needle_type_id = $_POST['needle_type_id'];
    $quantity_requested = $_POST['quantity_requested'];
    $request_notes = !empty(trim($_POST['request_notes'])) ? trim($_POST['request_notes']) : NULL;
    $requesting_user_id = $_SESSION['user_id'];

    // --- Validation ---
    if (empty($needle_type_id) || empty($quantity_requested) || !is_numeric($quantity_requested) || $quantity_requested <= 0) {
        header("Location: request_purchase.php?status=error&message=" . urlencode("Please select a needle and enter a valid quantity."));
        exit();
    }

    // --- Insert into Database ---
    $sql = "INSERT INTO purchase_requests (needle_type_id_fk, quantity_requested, request_notes, requesting_user_id_fk) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisi", $needle_type_id, $quantity_requested, $request_notes, $requesting_user_id);

    if ($stmt->execute()) {
        header("Location: request_purchase.php?status=success&message=" . urlencode("Purchase request submitted successfully."));
    } else {
        header("Location: request_purchase.php?status=error&message=" . urlencode("Database error: " . $stmt->error));
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: request_purchase.php");
    exit();
}
?>