<?php
require_once '../config.php';
session_start();

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
?>


