<?php
// =================================================================
// FILE: place_orders.php
// UPDATED - This page is now a fully functional ordering interface.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Page Protection ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$allowed_roles = ['Super Admin', 'Purchasing'];
if (!in_array($_SESSION['role_name'], $allowed_roles)) {
    header("Location: dashboard.php");
    exit();
}

// --- Fetch all needle types for the dropdown ---
$needles_dropdown_result = $conn->query("SELECT needle_type_id, needle_sku, needle_size FROM needle_types ORDER BY needle_sku");

// --- Fetch existing purchase orders ---
$orders_sql = "SELECT 
                    po.order_id,
                    po.needle_type_id_fk,
                    po.quantity_ordered,
                    nt.needle_sku,
                    nt.needle_size,
                    po.supplier_name,
                    po.order_status,
                    u.full_name AS placer_name,
                    po.placed_at
                FROM purchase_orders po
                JOIN needle_types nt ON po.needle_type_id_fk = nt.needle_type_id
                JOIN users u ON po.placing_user_id_fk = u.user_id
                ORDER BY po.placed_at DESC";
$orders_result = $conn->query($orders_sql);

// --- Fetch pending purchase requests ---
$purchase_requests_sql = "SELECT 
                            pr.prequest_id,
                            u.full_name AS requester_name,
                            nt.needle_sku,
                            nt.needle_size,
                            pr.quantity_requested,
                            pr.created_at
                          FROM purchase_requests pr
                          JOIN users u ON pr.requesting_user_id_fk = u.user_id
                          JOIN needle_types nt ON pr.needle_type_id_fk = nt.needle_type_id
                          WHERE pr.request_status = 'Pending'
                          ORDER BY pr.created_at ASC";
$purchase_requests_result = $conn->query($purchase_requests_sql);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place New Orders - Needle Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'nav.php'; ?>
        <div class="main-content">
            <div class="header"><h1>Place New Orders</h1></div>
            <div class="content">

                <?php 
                if (isset($_GET['status'])): ?>
                    <div class="notification notification-<?php echo $_GET['status'] == 'success' ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Pending Purchase Requests Table -->
                <div class="content-box">
                    <h2>Pending Purchase Requests from Stores</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Requester</th>
                                <th>Needle</th>
                                <th>Qty</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($purchase_requests_result && $purchase_requests_result->num_rows > 0): ?>
                                <?php while($pr = $purchase_requests_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d H:i', strtotime($pr['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($pr['requester_name']); ?></td>
                                        <td><?php echo htmlspecialchars($pr['needle_sku'] . ' / ' . $pr['needle_size']); ?></td>
                                        <td><?php echo $pr['quantity_requested']; ?></td>
                                        <td class="request-actions">
                                            <a href="place_orders.php?prequest_id=<?php echo $pr['prequest_id']; ?>" class="btn btn-primary">Create Order</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No pending purchase requests from Stores.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>


                <!-- New Order Form -->
                <div class="content-box">
                    <h2>Create New Purchase Order</h2>
                    <form action="add_order_process.php" method="POST">
                        <?php if(isset($_GET['prequest_id'])): ?>
                            <input type="hidden" name="purchase_request_id" value="<?php echo htmlspecialchars($_GET['prequest_id']); ?>">
                        <?php endif; ?>
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
                                <label for="quantity_ordered">Quantity to Order</label>
                                <input type="number" id="quantity_ordered" name="quantity_ordered" min="1" required>
                            </div>
                             <div class="form-group">
                                <label for="supplier_name">Supplier Name (Optional)</label>
                                <input type="text" id="supplier_name" name="supplier_name">
                            </div>
                        </div>
                         <div class="form-group" style="margin-top: 20px;">
                            <label for="order_notes">Notes (Optional)</label>
                            <textarea id="order_notes" name="order_notes" rows="3"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Place Order</button>
                        </div>
                    </form>
                </div>

                <!-- Existing Orders Table -->
                <div class="content-box">
                    <h2>Recent Purchase Orders</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date Placed</th>
                                <th>Placed By</th>
                                <th>Needle</th>
                                <th>Quantity</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                                <?php while($order = $orders_result->fetch_assoc()): 
                                    $status_class = 'status-' . strtolower(str_replace(' ', '-', $order['order_status']));
                                ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d H:i', strtotime($order['placed_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($order['placer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['needle_sku'] . ' / ' . $order['needle_size']); ?></td>
                                        <td><?php echo $order['quantity_ordered']; ?></td>
                                        <td><?php echo htmlspecialchars($order['supplier_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="status-label <?php echo $status_class; ?>">
                                                <?php echo htmlspecialchars($order['order_status']); ?>
                                            </span>
                                        </td>
                                        <td class="request-actions">
                                            <?php if ($order['order_status'] == 'Placed'): ?>
                                            <form action="update_order_status.php" method="POST">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <input type="hidden" name="needle_type_id" value="<?php echo $order['needle_type_id_fk']; ?>">
                                                <input type="hidden" name="quantity" value="<?php echo $order['quantity_ordered']; ?>">
                                                <button type="submit" name="action" value="Delivered" class="btn btn-success">Mark as Delivered</button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">No purchase orders found.</td>
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
