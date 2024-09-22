<?php
include '../session.php';
include '../db.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: /index.php");
    exit;
}

if (isset($_GET['id'])) {
    $customerId = $_GET['id'];

    $unbanCustomer = $pdo->prepare("UPDATE users SET deleted_at = NULL WHERE id = ?");
    $unbanCustomer->execute([$customerId]);

    header("Location: customer.php");
    exit;
} else {
    echo "Geçersiz kullanıcı ID'si";
}
?>
