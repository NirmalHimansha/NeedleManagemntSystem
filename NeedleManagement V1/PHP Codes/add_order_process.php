<?php
// =================================================================
// FILE: add_order_process.php
// NEW FILE - Handles the purchase order logic.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Protection ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$allowed_roles = ['Super Admin', 'Purchasing'];
if (!in_array($_SESSION['role_name'], $allowed_roles)) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $needle_type_id = $_POST['needle_type_id'];
    $quantity_ordered = $_POST['quantity_ordered'];
    $supplier_name = !empty(trim($_POST['supplier_name'])) ? trim($_POST['supplier_name']) : NULL;
    $order_notes = !empty(trim($_POST['order_notes'])) ? trim($_POST['order_notes']) : NULL;
    $placing_user_id = $_SESSION['user_id'];

    // --- Validation ---
    if (empty($needle_type_id) || empty($quantity_ordered) || !is_numeric($quantity_ordered) || $quantity_ordered <= 0) {
        header("Location: place_orders.php?status=error&message=" . urlencode("Please select a needle and enter a valid quantity."));
        exit();
    }

    // --- Insert into Database ---
    $sql = "INSERT INTO purchase_orders (needle_type_id_fk, quantity_ordered, supplier_name, order_notes, placing_user_id_fk) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssi", $needle_type_id, $quantity_ordered, $supplier_name, $order_notes, $placing_user_id);

    if ($stmt->execute()) {
        header("Location: place_orders.php?status=success&message=" . urlencode("Purchase order placed successfully."));
    } else {
        header("Location: place_orders.php?status=error&message=" . urlencode("Database error: " . $stmt->error));
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: place_orders.php");
    exit();
}
?>