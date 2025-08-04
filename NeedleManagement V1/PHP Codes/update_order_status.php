<?php
// =================================================================
// FILE: update_order_status.php
// NEW FILE - Handles the order status update and inventory refill.
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

    $order_id = $_POST['order_id'];
    $action = $_POST['action'];
    $needle_type_id = $_POST['needle_type_id'];
    $quantity = $_POST['quantity'];

    // Validate action
    if ($action !== 'Delivered') {
        header("Location: place_orders.php?status=error&message=" . urlencode("Invalid action."));
        exit();
    }

    // Use a transaction to ensure both updates succeed or fail together
    $conn->begin_transaction();

    try {
        // 1. Update the purchase order status
        $sql_order = "UPDATE purchase_orders 
                      SET order_status = ?, received_at = NOW() 
                      WHERE order_id = ? AND order_status = 'Placed'";
        $stmt_order = $conn->prepare($sql_order);
        $stmt_order->bind_param("si", $action, $order_id);
        $stmt_order->execute();
        
        if ($stmt_order->affected_rows === 0) {
            throw new Exception("Order could not be updated. It may have already been actioned.");
        }
        $stmt_order->close();

        // 2. Update the inventory
        $sql_inventory = "INSERT INTO needle_inventory (needle_type_id_fk, quantity) VALUES (?, ?)
                          ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)";
        $stmt_inventory = $conn->prepare($sql_inventory);
        $stmt_inventory->bind_param("ii", $needle_type_id, $quantity);
        $stmt_inventory->execute();
        $stmt_inventory->close();

        // If both queries were successful, commit the transaction
        $conn->commit();
        header("Location: place_orders.php?status=success&message=" . urlencode("Order marked as delivered and stock has been updated."));

    } catch (Exception $e) {
        // If any query failed, roll back the transaction
        $conn->rollback();
        header("Location: place_orders.php?status=error&message=" . urlencode($e->getMessage()));
    }

    $conn->close();

} else {
    header("Location: place_orders.php");
    exit();
}
?>
