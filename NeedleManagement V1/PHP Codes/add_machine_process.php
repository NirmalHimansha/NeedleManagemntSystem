<?php
// =================================================================
// FILE: add_machine_process.php
// UPDATED - Corrected the column name from machine_id to model_id.
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
    $model_name = trim($_POST['model_name']);
    $serial_number = trim($_POST['serial_number']);
    $model_type = trim($_POST['model_type']);

    // --- Validation ---
    if (empty($model_name) || empty($serial_number) || empty($model_type)) {
        header("Location: manage_machines.php?status=error&message=" . urlencode("All fields are required."));
        exit();
    }

    // Check if serial number already exists (should be unique)
    // CORRECTED a typo here: was machine_id, now model_id
    $stmt_check = $conn->prepare("SELECT model_id FROM machines WHERE serial_number = ?");
    $stmt_check->bind_param("s", $serial_number);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $stmt_check->close();
        header("Location: manage_machines.php?status=error&message=" . urlencode("A machine with this serial number already exists."));
        exit();
    }
    $stmt_check->close();

    // --- Insert into Database ---
    $sql = "INSERT INTO machines (model_name, serial_number, model_type) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $model_name, $serial_number, $model_type);

    if ($stmt->execute()) {
        header("Location: manage_machines.php?status=success&message=" . urlencode("Machine added successfully."));
    } else {
        header("Location: manage_machines.php?status=error&message=" . urlencode("Failed to add machine: " . $stmt->error));
    }

    $stmt->close();
    $conn->close();

} else {
    // If not a POST request, redirect to the form page
    header("Location: manage_machines.php");
    exit();
}
?>