<?php
// =================================================================
// FILE: view_all_requests.php
// UPDATED - This page is now a fully functional transaction log.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Page Protection ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$allowed_roles = ['Super Admin', 'Admin','Manager'.'Operator','Stores','Purchasing','Observer'];
if (!in_array($_SESSION['role_name'], $allowed_roles)) {
    header("Location: dashboard.php");
    exit();
}

// --- Fetch all requests with all user details ---
$sql = "SELECT 
            nr.request_id,
            requester.full_name AS requester_name,
            manager.full_name AS manager_name,
            issuer.full_name AS issuer_name,
            m.model_name,
            m.serial_number,
            nt.needle_sku,
            nt.needle_size,
            nr.quantity_requested,
            nr.request_status,
            nr.created_at,
            nr.approved_at,
            nr.issued_at
        FROM needle_requests nr
        JOIN users requester ON nr.requesting_user_id_fk = requester.user_id
        LEFT JOIN users manager ON nr.manager_approver_id_fk = manager.user_id
        LEFT JOIN users issuer ON nr.stores_issuer_id_fk = issuer.user_id
        JOIN machines m ON nr.machine_model_id_fk = m.model_id
        JOIN needle_types nt ON nr.needle_type_id_fk = nt.needle_type_id
        ORDER BY nr.created_at DESC";

$requests_result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Requests - Needle Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'nav.php'; ?>
        <div class="main-content">
            <div class="header"><h1>All Requests</h1></div>
            <div class="content">
                <div class="content-box">
                    <h2>Complete Needle Request History</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Request Date</th>
                                <th>Requester</th>
                                <th>Machine</th>
                                <th>Needle</th>
                                <th>Status</th>
                                <th>Approved By</th>
                                <th>Approval Date</th>
                                <th>Issued By</th>
                                <th>Issued Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($requests_result && $requests_result->num_rows > 0): ?>
                                <?php while($req = $requests_result->fetch_assoc()): 
                                    $status_class = 'status-' . strtolower(str_replace(' ', '-', $req['request_status']));
                                ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d H:i', strtotime($req['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($req['requester_name']); ?></td>
                                        <td><?php echo htmlspecialchars($req['model_name']); ?></td>
                                        <td><?php echo htmlspecialchars($req['needle_sku'] . ' / ' . $req['needle_size']); ?></td>
                                        <td>
                                            <span class="status-label <?php echo $status_class; ?>">
                                                <?php echo htmlspecialchars($req['request_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($req['manager_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo $req['approved_at'] ? date('Y-m-d H:i', strtotime($req['approved_at'])) : 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($req['issuer_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo $req['issued_at'] ? date('Y-m-d H:i', strtotime($req['issued_at'])) : 'N/A'; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9">No transactions found.</td>
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