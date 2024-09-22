<?php
session_start();

include '../db.php';

if($_SESSION['role'] !== 'admin'){
    header('Location: ../index.php');
}

$company_id = $_GET['id'];

$company = $pdo->prepare("SELECT * FROM company WHERE id = ?");
$company->execute([$company_id]);
$company = $company->fetch(PDO::FETCH_ASSOC);

if (!$company) {
    echo "Firma bulunamadı!";
    exit;
}

if ($company['deleted_at']) {
    $stmt = $pdo->prepare("UPDATE company SET deleted_at = NULL WHERE id = ?");
    $stmt->execute([$company_id]);

} else {
    $stmt = $pdo->prepare("UPDATE company SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$company_id]);

}

header("Location: company.php");
exit;
?>
