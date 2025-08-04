<?php
// =================================================================
// FILE: add_needle_process.php
// NEW FILE - Create this file.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Protection ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$allowed_roles = ['Super Admin', 'Admin'];
if (!in_array($_SESSION['role_name'], $allowed_roles)) {
    header("Location: dashboard.php");
    exit();
}

// Check if the form was submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- Get Form Data ---
    $needle_sku = trim($_POST['needle_sku']);
    $needle_size = trim($_POST['needle_size']);
    $manufacturer = !empty(trim($_POST['manufacturer'])) ? trim($_POST['manufacturer']) : NULL;

    // --- Validation ---
    if (empty($needle_sku) || empty($needle_size)) {
        header("Location: manage_needles.php?status=error&message=" . urlencode("SKU and Size are required."));
        exit();
    }

    // Check if needle SKU already exists (should be unique)
    $stmt_check = $conn->prepare("SELECT needle_type_id FROM needle_types WHERE needle_sku = ?");
    $stmt_check->bind_param("s", $needle_sku);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $stmt_check->close();
        header("Location: manage_needles.php?status=error&message=" . urlencode("A needle with this SKU already exists."));
        exit();
    }
    $stmt_check->close();

    // --- Insert into Database ---
    $sql = "INSERT INTO needle_types (needle_sku, needle_size, manufacturer) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $needle_sku, $needle_size, $manufacturer);

    if ($stmt->execute()) {
        header("Location: manage_needles.php?status=success&message=" . urlencode("Needle type added successfully."));
    } else {
        header("Location: manage_needles.php?status=error&message=" . urlencode("Failed to add needle type: " . $stmt->error));
    }

    $stmt->close();
    $conn->close();

} else {
    // If not a POST request, redirect to the form page
    header("Location: manage_needles.php");
    exit();
}
?>
