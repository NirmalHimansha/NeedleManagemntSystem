
<?php
// =================================================================
// FILE: login_process.php
// This file handles the logic of verifying user credentials.
// It does not display anything to the user, only processes data.
// =================================================================

// Start the session to store user data if login is successful.
session_start();

// Include our database connection file.
require_once 'db_connect.php';

// Check if the form was submitted using the POST method.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get username and password from the form.
    $username = $_POST['username'];
    $password = $_POST['password'];

    // --- SECURE DATABASE QUERY ---
    // Use a prepared statement to prevent SQL injection.
    $sql = "SELECT u.user_id, u.username, u.full_name, u.password_hash, r.role_name
            FROM users u
            JOIN roles r ON u.role_id_fk = r.role_id
            WHERE u.username = ?";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind the username parameter to the statement. 's' means the parameter is a string.
    $stmt->bind_param("s", $username);

    // Execute the query.
    $stmt->execute();

    // Get the result of the query.
    $result = $stmt->get_result();

    // Check if a user with that username was found (should be 1 row).
    if ($result->num_rows == 1) {
        // Fetch the user's data as an associative array.
        $user = $result->fetch_assoc();

        // --- Verify the password ---
        // Use password_verify() to securely compare the submitted password
        // with the hashed password from the database.
        if (password_verify($password, $user['password_hash'])) {
            // Password is correct! Login successful.

            // Store user data in the session.
            // This data will be available on all pages.
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role_name'] = $user['role_name'];

            // Redirect to the main dashboard.
            header("Location: dashboard.php");
            exit();

        } else {
            // Password is not correct.
            // Redirect back to the login page with an error message.
            header("Location: index.php?error=1");
            exit();
        }
    } else {
        // No user found with that username.
        // Redirect back to the login page with an error message.
        header("Location: index.php?error=1");
        exit();
    }

    // Close the statement and connection.
    $stmt->close();
    $conn->close();

} else {
    // If someone tries to access this file directly without submitting the form,
    // just redirect them to the login page.
    header("Location: index.php");
    exit();
}
?>