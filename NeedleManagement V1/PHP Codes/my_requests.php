<?php
// =================================================================
// FILE: my_requests.php
// UPDATED - This page is now a fully functional request history page.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Page Protection ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

// --- Fetch this user's requests ---
$sql = "SELECT 
            nr.request_id,
            m.model_name,
            m.serial_number,
            nt.needle_sku,
            nt.needle_size,
            nr.quantity_requested,
            nr.request_status,
            nr.created_at
        FROM needle_requests nr
        JOIN machines m ON nr.machine_model_id_fk = m.model_id
        JOIN needle_types nt ON nr.needle_type_id_fk = nt.needle_type_id
        WHERE nr.requesting_user_id_fk = ?
        ORDER BY nr.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$requests_result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests - Needle Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'nav.php'; ?>
        <div class="main-content">
            <div class="header"><h1>My Requests</h1></div>
            <div class="content">
                <div class="content-box">
                    <h2>My Needle Request History</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Machine</th>
                                <th>Needle</th>
                                <th>Qty</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($requests_result && $requests_result->num_rows > 0): ?>
                                <?php while($req = $requests_result->fetch_assoc()): 
                                    $status_class = 'status-' . strtolower(str_replace(' ', '-', $req['request_status']));
                                ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d H:i', strtotime($req['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($req['model_name'] . ' (' . $req['serial_number'] . ')'); ?></td>
                                        <td><?php echo htmlspecialchars($req['needle_sku'] . ' / ' . $req['needle_size']); ?></td>
                                        <td><?php echo $req['quantity_requested']; ?></td>
                                        <td>
                                            <span class="status-label <?php echo $status_class; ?>">
                                                <?php echo htmlspecialchars($req['request_status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">You have not made any requests yet.</td>
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