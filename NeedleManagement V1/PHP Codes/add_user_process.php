<?php
// =================================================================
// FILE: add_user_process.php
// NEW FILE - This script handles the backend logic for adding a user.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Protection ---
// Check if user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
// Check if user has the correct role.
$allowed_roles = ['Super Admin', 'Admin'];
if (!in_array($_SESSION['role_name'], $allowed_roles)) {
    header("Location: dashboard.php");
    exit();
}

// Check if the form was submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- Get Form Data ---
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role_id = $_POST['role_id'];
    // Handle optional department ID
    $dept_id = !empty($_POST['dept_id']) ? $_POST['dept_id'] : NULL;

    // --- Validation ---
    if (empty($full_name) || empty($username) || empty($password) || empty($role_id)) {
        // Redirect back with an error message
        header("Location: manage_users.php?status=error&message=" . urlencode("Please fill in all required fields."));
        exit();
    }

    // Check if username already exists
    $stmt_check = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $stmt_check->close();
        header("Location: manage_users.php?status=error&message=" . urlencode("Username already exists."));
        exit();
    }
    $stmt_check->close();

    // --- Secure Password Hashing ---
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // --- Insert into Database using Prepared Statement ---
    $sql = "INSERT INTO users (username, full_name, password_hash, role_id_fk, dept_id_fk) VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        // Handle error
        header("Location: manage_users.php?status=error&message=" . urlencode("Database error: " . $conn->error));
        exit();
    }

    // Bind parameters
    // The types are: s (string), s (string), s (string), i (integer), i (integer)
    $stmt->bind_param("sssis", $username, $full_name, $password_hash, $role_id, $dept_id);

    // Execute the statement and check for success
    if ($stmt->execute()) {
        // Success
        header("Location: manage_users.php?status=success&message=" . urlencode("User added successfully."));
    } else {
        // Failure
        header("Location: manage_users.php?status=error&message=" . urlencode("Failed to add user: " . $stmt->error));
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();

} else {
    // If not a POST request, redirect to the form page
    header("Location: manage_users.php");
    exit();
}
?>
