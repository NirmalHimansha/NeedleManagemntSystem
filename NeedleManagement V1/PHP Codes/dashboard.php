<?php
// =================================================================
// FILE: dashboard.php
// UPDATED - Added workflow diagram.
// =================================================================

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Needle Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <?php include 'nav.php'; // Include the navigation sidebar ?>
        
        <div class="main-content">
            <div class="header">
                <h1>Dashboard</h1>
            </div>
            
            <div class="content">
                <div class="content-box">
                    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h2>
                    <p>You are logged in as a/an: <strong><?php echo htmlspecialchars($_SESSION['role_name']); ?></strong>.</p>
                    <p>Select an option from the menu on the left to get started.</p>
                </div>

                <div class="content-box">
                    <h2>System Workflow</h2>
                    <p>This diagram shows the flow of a needle request from start to finish.</p>
                    <div class="workflow-image-container">
                        <img src="./NeedleSequence.png" alt="System Workflow Diagram">
                        <img src="./NeedleSequence.png" alt="System Workflow Diagram">
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
