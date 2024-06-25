<?php

if (!isset($_SESSION['email'])) {
    header("Location: ../home.php");
    exit();
}
