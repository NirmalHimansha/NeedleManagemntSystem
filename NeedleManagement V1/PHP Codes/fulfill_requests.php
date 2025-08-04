<?php
// =================================================================
// FILE: fulfill_requests.php
// UPDATED - This page is now a fully functional fulfillment interface.
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

// --- Fetch approved requests ---
$sql = "SELECT 
            nr.request_id,
            u.full_name AS requester_name,
            m.model_name,
            m.serial_number,
            nt.needle_sku,
            nt.needle_size,
            nr.quantity_requested,
            nr.approved_at
        FROM needle_requests nr
        JOIN users u ON nr.requesting_user_id_fk = u.user_id
        JOIN machines m ON nr.machine_model_id_fk = m.model_id
        JOIN needle_types nt ON nr.needle_type_id_fk = nt.needle_type_id
        WHERE nr.request_status = 'Approved'
        ORDER BY nr.approved_at ASC";

$requests_result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fulfill Requests - Needle Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'nav.php'; ?>
        <div class="main-content">
            <div class="header"><h1>Fulfill Requests</h1></div>
            <div class="content">
                <div class="content-box">
                    <h2>Approved Needle Requests</h2>
                    
                    <?php 
                    if (isset($_GET['status'])): ?>
                        <div class="notification notification-<?php echo $_GET['status'] == 'success' ? 'success' : 'error'; ?>">
                            <?php echo htmlspecialchars($_GET['message']); ?>
                        </div>
                    <?php endif; ?>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Approved Date</th>
                                <th>Requester</th>
                                <th>Machine</th>
                                <th>Needle</th>
                                <th>Qty</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($requests_result && $requests_result->num_rows > 0): ?>
                                <?php while($req = $requests_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d H:i', strtotime($req['approved_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($req['requester_name']); ?></td>
                                        <td><?php echo htmlspecialchars($req['model_name'] . ' (' . $req['serial_number'] . ')'); ?></td>
                                        <td><?php echo htmlspecialchars($req['needle_sku'] . ' / ' . $req['needle_size']); ?></td>
                                        <td><?php echo $req['quantity_requested']; ?></td>
                                        <td class="request-actions">
                                            <form action="update_fulfillment_status.php" method="POST">
                                                <input type="hidden" name="request_id" value="<?php echo $req['request_id']; ?>">
                                                <button type="submit" name="action" value="Issued" class="btn btn-primary">Mark as Issued</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No approved requests waiting for fulfillment.</td>
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