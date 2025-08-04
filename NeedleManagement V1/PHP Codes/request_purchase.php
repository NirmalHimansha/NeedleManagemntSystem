<?php
// =================================================================
// FILE: request_purchase.php
// NEW FILE - Allows Stores to request new stock.
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Purchase - Needle Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'nav.php'; ?>
        <div class="main-content">
            <div class="header"><h1>Request New Stock Purchase</h1></div>
            <div class="content">

                <?php 
                if (isset($_GET['status'])): ?>
                    <div class="notification notification-<?php echo $_GET['status'] == 'success' ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>

                <!-- New Purchase Request Form -->
                <div class="content-box">
                    <h2>Create New Purchase Request</h2>
                    <form action="add_purchase_request_process.php" method="POST">
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
                                <label for="quantity_requested">Quantity to Request</label>
                                <input type="number" id="quantity_requested" name="quantity_requested" min="1" required>
                            </div>
                        </div>
                         <div class="form-group" style="margin-top: 20px;">
                            <label for="request_notes">Notes (Optional)</label>
                            <textarea id="request_notes" name="request_notes" rows="3"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Submit Purchase Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>