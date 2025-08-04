<?php
// =================================================================
// FILE: add_compatibility_process.php
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $machine_id = $_POST['machine_id'];
    $needle_type_id = $_POST['needle_type_id'];

    if (empty($machine_id) || empty($needle_type_id)) {
        header("Location: manage_compatibility.php?status=error&message=" . urlencode("Both a machine and a needle must be selected."));
        exit();
    }

    $stmt_check = $conn->prepare("SELECT map_id FROM machine_needle_compatibility WHERE machine_id_fk = ? AND needle_type_id_fk = ?");
    $stmt_check->bind_param("ii", $machine_id, $needle_type_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $stmt_check->close();
        header("Location: manage_compatibility.php?status=error&message=" . urlencode("This link already exists."));
        exit();
    }
    $stmt_check->close();

    $sql = "INSERT INTO machine_needle_compatibility (machine_id_fk, needle_type_id_fk) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $machine_id, $needle_type_id);

    if ($stmt->execute()) {
        header("Location: manage_compatibility.php?status=success&message=" . urlencode("Compatibility link added successfully."));
    } else {
        header("Location: manage_compatibility.php?status=error&message=" . urlencode("Failed to add link: " . $stmt->error));
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: manage_compatibility.php");
    exit();
}
?>