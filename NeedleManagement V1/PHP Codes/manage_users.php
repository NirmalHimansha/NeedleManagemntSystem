<?php
// =================================================================
// FILE: manage_users.php
// UPDATED - This page is now a fully functional user management interface.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Page Protection ---
// Check if user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Check if user has the correct role.
$allowed_roles = ['Super Admin', 'Admin'];
if (!in_array($_SESSION['role_name'], $allowed_roles)) {
    // Redirect to dashboard if they don't have permission
    header("Location: dashboard.php");
    exit();
}

// --- Fetch Data for Form Dropdowns ---
// Fetch all roles
$roles_result = $conn->query("SELECT role_id, role_name FROM roles ORDER BY role_name");
// Fetch all departments
$departments_result = $conn->query("SELECT dept_id, dept_name FROM departments ORDER BY dept_name");

// --- Fetch Data for User Table ---
$users_sql = "SELECT u.user_id, u.username, u.full_name, r.role_name, d.dept_name, u.is_active 
              FROM users u 
              JOIN roles r ON u.role_id_fk = r.role_id 
              LEFT JOIN departments d ON u.dept_id_fk = d.dept_id
              ORDER BY u.full_name";
$users_result = $conn->query($users_sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Needle Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'nav.php'; ?>
        <div class="main-content">
            <div class="header"><h1>Manage Users</h1></div>
            <div class="content">

                <?php 
                // Display success or error messages if they exist
                if (isset($_GET['status'])): ?>
                    <div class="notification notification-<?php echo $_GET['status'] == 'success' ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Add New User Form -->
                <div class="content-box">
                    <h2>Add New User</h2>
                    <form action="add_user_process.php" method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" required>
                            </div>
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select id="role" name="role_id" required>
                                    <option value="">Select a role...</option>
                                    <?php while($role = $roles_result->fetch_assoc()): ?>
                                        <option value="<?php echo $role['role_id']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="department">Department (Optional)</label>
                                <select id="department" name="dept_id">
                                    <option value="">None</option>
                                    <?php while($dept = $departments_result->fetch_assoc()): ?>
                                        <option value="<?php echo $dept['dept_id']; ?>"><?php echo htmlspecialchars($dept['dept_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Add User</button>
                        </div>
                    </form>
                </div>

                <!-- Existing Users Table -->
                <div class="content-box">
                    <h2>Existing Users</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users_result->num_rows > 0): ?>
                                <?php while($user = $users_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['dept_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="status-<?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="#">Edit</a> | <a href="#">Deactivate</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No users found.</td>
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
