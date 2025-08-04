<?php
// =================================================================
// FILE: manage_inventory.php
// UPDATED - This page is now a fully functional inventory management interface.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Page Protection ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$allowed_roles = ['Super Admin', 'Stores'];
if (!in_array($_SESSION['role_name'], $allowed_roles)) {
    header("Location: dashboard.php");
    exit();
}

// --- Fetch all needle types for the dropdown ---
$needles_dropdown_result = $conn->query("SELECT needle_type_id, needle_sku, needle_size FROM needle_types ORDER BY needle_sku");

// --- Fetch current inventory levels ---
$inventory_sql = "SELECT 
                    nt.needle_type_id, 
                    nt.needle_sku, 
                    nt.needle_size, 
                    nt.manufacturer, 
                    COALESCE(ni.quantity, 0) AS quantity
                  FROM needle_types nt
                  LEFT JOIN needle_inventory ni ON nt.needle_type_id = ni.needle_type_id_fk
                  ORDER BY nt.needle_sku";
$inventory_result = $conn->query($inventory_sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory - Needle Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'nav.php'; ?>
        <div class="main-content">
            <div class="header"><h1>Manage Inventory</h1></div>
            <div class="content">

                <?php 
                if (isset($_GET['status'])): ?>
                    <div class="notification notification-<?php echo $_GET['status'] == 'success' ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Add Stock Form -->
                <div class="content-box">
                    <h2>Add Stock (Refill)</h2>
                    <form action="add_stock_process.php" method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="needle_type_id">Select Needle Type</label>
                                <select id="needle_type_id" name="needle_type_id" required>
                                    <option value="">Select a needle...</option>
                                    <?php while($needle = $needles_dropdown_result->fetch_assoc()): ?>
                                        <option value="<?php echo $needle['needle_type_id']; ?>">
                                            <?php echo htmlspecialchars($needle['needle_sku'] . ' / ' . $needle['needle_size']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="quantity_to_add">Quantity to Add</label>
                                <input type="number" id="quantity_to_add" name="quantity_to_add" min="1" required>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Add to Stock</button>
                        </div>
                    </form>
                </div>

                <!-- Current Inventory Table -->
                <div class="content-box">
                    <h2>Current Needle Inventory</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>SKU / Type</th>
                                <th>Size</th>
                                <th>Manufacturer</th>
                                <th>Current Quantity in Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($inventory_result && $inventory_result->num_rows > 0): ?>
                                <?php while($item = $inventory_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['needle_sku']); ?></td>
                                        <td><?php echo htmlspecialchars($item['needle_size']); ?></td>
                                        <td><?php echo htmlspecialchars($item['manufacturer'] ?? 'N/A'); ?></td>
                                        <td><strong><?php echo $item['quantity']; ?></strong></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No needle types found. Add needle types in the 'Manage Needles' page first.</td>
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
