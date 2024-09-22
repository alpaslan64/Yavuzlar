<?php
include '../session.php';
include '../db.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: /index.php");
    exit;
}

if (isset($_GET['id'])) {
    $customerId = $_GET['id'];

    $banCustomer = $pdo->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
    $banCustomer->execute([$customerId]);

    header("Location: customer.php");
    exit;
} else {
    echo "Geçersiz kullanıcı ID'si";
}
?>
