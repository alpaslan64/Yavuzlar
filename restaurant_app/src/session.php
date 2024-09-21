<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$surname = $_SESSION['surname'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$balance = $_SESSION['balance'];
?>
