<?php
// =================================================================
// FILE: logout.php
// This file destroys the session and logs the user out.
// =================================================================

// Always start the session first.
session_start();

// Unset all of the session variables.
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to the login page.
header("location: index.php");
exit;
?>