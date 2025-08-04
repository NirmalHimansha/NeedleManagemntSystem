<?php
// =================================================================
// FILE: manage_departments.php
// NEW FILE - Create this file.
// =================================================================

session_start();
require_once 'db_connect.php';

// --- Page Protection ---
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Only Super Admins can manage departments
if ($_SESSION['role_name'] !== 'Super Admin') {
    header("Location: dashboard.php");
    exit();
}

// --- Fetch all existing departments ---
$departments_result = $conn->query("SELECT dept_id, dept_name FROM departments ORDER BY dept_name");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments - Needle Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'nav.php'; ?>
        <div class="main-content">
            <div class="header"><h1>Manage Departments</h1></div>
            <div class="content">

                <?php 
                // Display success or error messages if they exist
                if (isset($_GET['status'])): ?>
                    <div class="notification notification-<?php echo $_GET['status'] == 'success' ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Add New Department Form -->
                <div class="content-box">
                    <h2>Add New Department</h2>
                    <form action="add_department_process.php" method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="dept_name">Department Name</label>
                                <input type="text" id="dept_name" name="dept_name" required>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Add Department</button>
                        </div>
                    </form>
                </div>

                <!-- Existing Departments Table -->
                <div class="content-box">
                    <h2>Existing Departments</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Department ID</th>
                                <th>Department Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($departments_result->num_rows > 0): ?>
                                <?php while($dept = $departments_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $dept['dept_id']; ?></td>
                                        <td><?php echo htmlspecialchars($dept['dept_name']); ?></td>
                                        <td>
                                            <a href="#">Edit</a> | <a href="#">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">No departments found.</td>
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
