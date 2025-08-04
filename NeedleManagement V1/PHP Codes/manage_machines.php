
<?php
// =================================================================
// FILE: manage_machines.php
// UPDATED - Corrected the column name from machine_id to model_id.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Page Protection ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Allowed roles
$allowed_roles = ['Super Admin', 'Admin'];
if (!in_array($_SESSION['role_name'], $allowed_roles)) {
    header("Location: dashboard.php");
    exit();
}

// --- Fetch all existing machines ---
// CORRECTED a typo here: was machine_id, now model_id
$machines_result = $conn->query("SELECT model_id, model_name, serial_number, model_type FROM machines ORDER BY model_name");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Machines - Needle Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'nav.php'; ?>
        <div class="main-content">
            <div class="header"><h1>Manage Machines</h1></div>
            <div class="content">

                <?php 
                // Display success or error messages if they exist
                if (isset($_GET['status'])): ?>
                    <div class="notification notification-<?php echo $_GET['status'] == 'success' ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Add New Machine Form -->
                <div class="content-box">
                    <h2>Add New Machine</h2>
                    <form action="add_machine_process.php" method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="model_name">Machine Model Name</label>
                                <input type="text" id="model_name" name="model_name" required>
                            </div>
                             <div class="form-group">
                                <label for="serial_number">Serial Number</label>
                                <input type="text" id="serial_number" name="serial_number" required>
                            </div>
                            <div class="form-group">
                                <label for="model_type">Machine Type</label>
                                <select id="model_type" name="model_type" required>
                                    <option value="">Select a type...</option>
                                    <option value="Sewing">Sewing</option>
                                    <option value="Embroidery">Embroidery</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Add Machine</button>
                        </div>
                    </form>
                </div>

                <!-- Existing Machines Table -->
                <div class="content-box">
                    <h2>Existing Machines</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Machine ID</th>
                                <th>Model Name</th>
                                <th>Serial Number</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($machines_result && $machines_result->num_rows > 0): ?>
                                <?php while($machine = $machines_result->fetch_assoc()): ?>
                                    <tr>
                                        <!-- CORRECTED a typo here: was machine_id, now model_id -->
                                        <td><?php echo $machine['model_id']; ?></td>
                                        <td><?php echo htmlspecialchars($machine['model_name']); ?></td>
                                        <td><?php echo htmlspecialchars($machine['serial_number']); ?></td>
                                        <td><?php echo htmlspecialchars($machine['model_type']); ?></td>
                                        <td>
                                            <a href="#">Edit</a> | <a href="#">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No machines found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
