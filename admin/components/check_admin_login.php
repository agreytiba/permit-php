<?php


// Redirect to login page if not logged in
if (!isset($_SESSION['adminId'])) {
    header("Location: login.php");
    exit();
}

// Check if the user is approved
if ($_SESSION['user_type'] != 'admin') {
    // Redirect unauthorized users
    header("Location:../not_found.php");
    exit();
}

// Continue with the rest of the page content