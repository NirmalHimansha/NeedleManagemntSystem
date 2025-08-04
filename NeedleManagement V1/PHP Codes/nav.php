<?php
// =================================================================
// FILE: nav.php
// UPDATED - Added "Request Purchase" link for Stores.
// =================================================================

// We need session data here to know the user's role.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, otherwise they shouldn't see a nav bar.
if (!isset($_SESSION['user_id'])) {
    return;
}

$user_role = $_SESSION['role_name'];
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h3>Needle System</h3>
        <p><?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
    </div>
    <ul class="nav-menu">
        <li><a href="dashboard.php">Dashboard</a></li>

        <?php
        // Use a switch statement to show links based on role
        switch ($user_role) {
            case 'Super Admin':
                // Super Admin sees ALL links
                echo '<li style="padding: 10px 20px; color: #6c757d; font-size: 12px; text-transform: uppercase;">System Setup</li>';
                echo '<li><a href="manage_users.php">Manage Users</a></li>';
                echo '<li><a href="manage_departments.php">Manage Departments</a></li>';
                echo '<li><a href="manage_machines.php">Manage Machines</a></li>';
                echo '<li><a href="manage_needles.php">Manage Needles</a></li>';
                echo '<li><a href="manage_compatibility.php">Manage Compatibility</a></li>';
                
                echo '<li style="padding: 10px 20px; color: #6c757d; font-size: 12px; text-transform: uppercase;">Operations</li>';
                echo '<li><a href="view_all_requests.php">View All Requests</a></li>';
                echo '<li><a href="approve_requests.php">Approve Requests</a></li>';
                echo '<li><a href="new_request.php">New Needle Request</a></li>';
                echo '<li><a href="my_requests.php">My Requests</a></li>';
                echo '<li><a href="fulfill_requests.php">Fulfill Requests</a></li>';
                echo '<li><a href="manage_inventory.php">Manage Inventory</a></li>';
                echo '<li><a href="request_purchase.php">Request Purchase</a></li>';
                echo '<li><a href="place_orders.php">Place New Orders</a></li>';

                echo '<li style="padding: 10px 20px; color: #6c757d; font-size: 12px; text-transform: uppercase;">Reporting</li>';
                echo '<li><a href="view_department_reports.php">Department Reports</a></li>';
                echo '<li><a href="view_all_transactions.php">All Transactions</a></li>';
                break;

            case 'Admin':
                // Links for Admin
                echo '<li><a href="manage_users.php">Manage Users</a></li>';
                echo '<li><a href="manage_machines.php">Manage Machines</a></li>';
                echo '<li><a href="manage_needles.php">Manage Needles</a></li>';
                echo '<li><a href="manage_compatibility.php">Manage Compatibility</a></li>';
                echo '<li style="padding: 10px 20px; color: #6c757d; font-size: 12px; text-transform: uppercase;">Reporting</li>';
                echo '<li><a href="view_department_reports.php">Department Reports</a></li>';
                echo '<li><a href="view_all_requests.php">View All Requests</a></li>';
                break;

            case 'Manager':
                echo '<li><a href="approve_requests.php">Approve Requests</a></li>';
                echo '<li><a href="view_department_reports.php">View Reports</a></li>';
                echo '<li><a href="view_all_transactions.php">All Transactions</a></li>';
                break;
            
            case 'Operator':
                echo '<li><a href="new_request.php">New Needle Request</a></li>';
                echo '<li><a href="my_requests.php">My Requests</a></li>';
                break;

            case 'Stores':
                echo '<li><a href="fulfill_requests.php">Fulfill Requests</a></li>';
                echo '<li><a href="manage_inventory.php">Manage Inventory</a></li>';
                echo '<li><a href="request_purchase.php">Request Purchase</a></li>';
                echo '<li><a href="view_all_transactions.php">All Transactions</a></li>';
                echo '<li style="padding: 10px 20px; color: #6c757d; font-size: 12px; text-transform: uppercase;">Reporting</li>';
                echo '<li><a href="view_department_reports.php">Department Reports</a></li>';
                break;

            case 'Purchasing':
                echo '<li><a href="place_orders.php">Place New Orders</a></li>';
                break;

            case 'Observer':
                echo '<li><a href="view_all_transactions.php">View All Transactions</a></li>';
                echo '<li><a href="view_all_requests.php">View All Requests</a></li>';
                echo '<li style="padding: 10px 20px; color: #6c757d; font-size: 12px; text-transform: uppercase;">Reporting</li>';
                echo '<li><a href="view_department_reports.php">Department Reports</a></li>';
                break;
        }
        ?>
    </ul>
    <div class="sidebar-footer">
        <a href="logout.php">Logout</a>
    </div>
</div>
