<?php
session_start();

// Redirect to login page if the user is not logged in
include 'components/check_admin_login.php';

// Include database connection
include 'db_connection.php';

// Get the file name from the URL parameter
if (isset($_GET['file'])) {
    $fileName = $_GET['file'];

    // Security check: sanitize the file name and prevent directory traversal attacks
    $fileName = str_replace(['..\\', '../'], '', $fileName);

    // Specify the directory where the files are stored
    $filePath = realpath(__DIR__ . '/' . $fileName);

    // Verify the file path is within the uploads directory
    $uploadsDir = realpath(__DIR__ . '/../uploads');
    if (strpos($filePath, $uploadsDir) === 0 && file_exists($filePath)) {
        // Set headers to trigger file view in the browser
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename=' . basename($filePath));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo "File not found or invalid file path.";
    }
} else {
    echo "No file specified.";
}
