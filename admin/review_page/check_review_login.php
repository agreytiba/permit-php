<?php


// Redirect to login page if not logged in
if (!isset($_SESSION['admin_email'])) {
    header("Location: ../login.php");
    exit();
}

// Check if the user is approved
if ($_SESSION['user_type'] != 'review') {
    // Redirect unauthorized users
    header("Location: unauthorized.php");
    exit();
}

// Continue with the rest of the page content
