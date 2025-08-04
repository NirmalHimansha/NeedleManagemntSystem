<?php
// =================================================================
// FILE: manage_needles.php
// UPDATED - This page is now a fully functional needle management interface.
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

// --- Fetch all existing needle types ---
$needles_result = $conn->query("SELECT needle_type_id, needle_sku, needle_size, manufacturer FROM needle_types ORDER BY needle_sku");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Needles - Needle Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'nav.php'; ?>
        <div class="main-content">
            <div class="header"><h1>Manage Needles</h1></div>
            <div class="content">

                <?php 
                // Display success or error messages if they exist
                if (isset($_GET['status'])): ?>
                    <div class="notification notification-<?php echo $_GET['status'] == 'success' ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Add New Needle Form -->
                <div class="content-box">
                    <h2>Add New Needle Type</h2>
                    <form action="add_needle_process.php" method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="needle_sku">Needle SKU / Type</label>
                                <input type="text" id="needle_sku" name="needle_sku" placeholder="e.g., 135x17" required>
                            </div>
                             <div class="form-group">
                                <label for="needle_size">Needle Size</label>
                                <input type="text" id="needle_size" name="needle_size" placeholder="e.g., 110/18" required>
                            </div>
                            <div class="form-group">
                                <label for="manufacturer">Manufacturer (Optional)</label>
                                <input type="text" id="manufacturer" name="manufacturer">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Add Needle Type</button>
                        </div>
                    </form>
                </div>

                <!-- Existing Needles Table -->
                <div class="content-box">
                    <h2>Existing Needle Types</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Needle ID</th>
                                <th>SKU / Type</th>
                                <th>Size</th>
                                <th>Manufacturer</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($needles_result && $needles_result->num_rows > 0): ?>
                                <?php while($needle = $needles_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $needle['needle_type_id']; ?></td>
                                        <td><?php echo htmlspecialchars($needle['needle_sku']); ?></td>
                                        <td><?php echo htmlspecialchars($needle['needle_size']); ?></td>
                                        <td><?php echo htmlspecialchars($needle['manufacturer'] ?? 'N/A'); ?></td>
                                        <td>
                                            <a href="#">Edit</a> | <a href="#">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No needle types found.</td>
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
