<?php
// =================================================================
// FILE: add_stock_process.php
// NEW FILE - Handles the inventory update logic.
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
    $quantity_to_add = $_POST['quantity_to_add'];

    // --- Validation ---
    if (empty($needle_type_id) || empty($quantity_to_add) || !is_numeric($quantity_to_add) || $quantity_to_add <= 0) {
        header("Location: manage_inventory.php?status=error&message=" . urlencode("Please select a needle and enter a valid quantity."));
        exit();
    }

    // --- Check if inventory record exists ---
    $stmt_check = $conn->prepare("SELECT inventory_id FROM needle_inventory WHERE needle_type_id_fk = ?");
    $stmt_check->bind_param("i", $needle_type_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // If it exists, UPDATE the quantity
        $stmt_check->close();
        $sql = "UPDATE needle_inventory SET quantity = quantity + ? WHERE needle_type_id_fk = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $quantity_to_add, $needle_type_id);
    } else {
        // If it doesn't exist, INSERT a new record
        $stmt_check->close();
        $sql = "INSERT INTO needle_inventory (needle_type_id_fk, quantity) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $needle_type_id, $quantity_to_add);
    }

    if ($stmt->execute()) {
        header("Location: manage_inventory.php?status=success&message=" . urlencode("Stock updated successfully."));
    } else {
        header("Location: manage_inventory.php?status=error&message=" . urlencode("Database error: " . $stmt->error));
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: manage_inventory.php");
    exit();
}
?>