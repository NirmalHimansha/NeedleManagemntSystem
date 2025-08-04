<?php
// =================================================================
// FILE: manage_compatibility.php
// NEW FILE - Create this file.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Page Protection ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$allowed_roles = ['Super Admin', 'Admin'];
if (!in_array($_SESSION['role_name'], $allowed_roles)) {
    header("Location: dashboard.php");
    exit();
}

// --- Fetch Data for Form Dropdowns ---
$machines_result = $conn->query("SELECT model_id, model_name, serial_number FROM machines ORDER BY model_name");
$needles_result = $conn->query("SELECT needle_type_id, needle_sku, needle_size FROM needle_types ORDER BY needle_sku");

// --- Fetch Existing Mappings ---
$mappings_sql = "SELECT mnc.map_id, m.model_name, m.serial_number, nt.needle_sku, nt.needle_size
                 FROM machine_needle_compatibility mnc
                 JOIN machines m ON mnc.machine_id_fk = m.model_id
                 JOIN needle_types nt ON mnc.needle_type_id_fk = nt.needle_type_id
                 ORDER BY m.model_name, nt.needle_sku";
$mappings_result = $conn->query($mappings_sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Compatibility - Needle Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'nav.php'; ?>
        <div class="main-content">
            <div class="header"><h1>Manage Compatibility</h1></div>
            <div class="content">

                <?php 
                if (isset($_GET['status'])): ?>
                    <div class="notification notification-<?php echo $_GET['status'] == 'success' ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Link Machine to Needle Form -->
                <div class="content-box">
                    <h2>Link Needle to Machine</h2>
                    <form action="add_compatibility_process.php" method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="machine_id">Select Machine</label>
                                <select id="machine_id" name="machine_id" required>
                                    <option value="">Select a machine...</option>
                                    <?php while($machine = $machines_result->fetch_assoc()): ?>
                                        <option value="<?php echo $machine['model_id']; ?>">
                                            <?php echo htmlspecialchars($machine['model_name'] . ' (' . $machine['serial_number'] . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="needle_type_id">Select Needle Type</label>
                                <select id="needle_type_id" name="needle_type_id" required>
                                    <option value="">Select a needle...</option>
                                    <?php while($needle = $needles_result->fetch_assoc()): ?>
                                        <option value="<?php echo $needle['needle_type_id']; ?>">
                                            <?php echo htmlspecialchars($needle['needle_sku'] . ' / ' . $needle['needle_size']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Link Needle to Machine</button>
                        </div>
                    </form>
                </div>

                <!-- Existing Compatibility Table -->
                <div class="content-box">
                    <h2>Existing Compatibility Links</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Machine</th>
                                <th>Compatible Needle</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($mappings_result && $mappings_result->num_rows > 0): ?>
                                <?php while($map = $mappings_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($map['model_name'] . ' (' . $map['serial_number'] . ')'); ?></td>
                                        <td><?php echo htmlspecialchars($map['needle_sku'] . ' / ' . $map['needle_size']); ?></td>
                                        <td><a href="#">Remove</a></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">No compatibility links found.</td>
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