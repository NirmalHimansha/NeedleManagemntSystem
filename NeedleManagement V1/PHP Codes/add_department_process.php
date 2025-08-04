<?php
// =================================================================
// FILE: add_department_process.php
// NEW FILE - Create this file.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Protection ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
if ($_SESSION['role_name'] !== 'Super Admin') {
    header("Location: dashboard.php");
    exit();
}

// Check if the form was submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- Get Form Data ---
    $dept_name = trim($_POST['dept_name']);

    // --- Validation ---
    if (empty($dept_name)) {
        header("Location: manage_departments.php?status=error&message=" . urlencode("Department name cannot be empty."));
        exit();
    }

    // Check if department name already exists
    $stmt_check = $conn->prepare("SELECT dept_id FROM departments WHERE dept_name = ?");
    $stmt_check->bind_param("s", $dept_name);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $stmt_check->close();
        header("Location: manage_departments.php?status=error&message=" . urlencode("Department name already exists."));
        exit();
    }
    $stmt_check->close();

    // --- Insert into Database ---
    $sql = "INSERT INTO departments (dept_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $dept_name);

    if ($stmt->execute()) {
        header("Location: manage_departments.php?status=success&message=" . urlencode("Department added successfully."));
    } else {
        header("Location: manage_departments.php?status=error&message=" . urlencode("Failed to add department: " . $stmt->error));
    }

    $stmt->close();
    $conn->close();

} else {
    // If not a POST request, redirect to the form page
    header("Location: manage_departments.php");
    exit();
}
?>