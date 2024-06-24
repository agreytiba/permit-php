<?php

$status = "prod";

if ($status == "prod") {
    $dbuser = "fadhftvq_permit-user";
    $dbpass = "5S_eMiFrPtvd";
    $host = "localhost";
    $db = "fadhftvq_permitapp"; // Production database name
} else {
    $dbuser = "root"; // Development database username
    $dbpass = ""; // Development database password
    $host = "localhost"; // Development database host
    $db = "permitapp"; // Development database name
}

$conn = new mysqli($host, $dbuser, $dbpass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
