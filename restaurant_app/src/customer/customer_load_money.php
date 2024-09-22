<?php
session_start();
include '../db.php'; 

if ($_SESSION['role'] !== 'customer') {
    header('Location: ../index.php');
    exit;
}

if (isset($_POST['update_balance'])) {
    $new_balance = $_POST['new_balance'];


        $customer_id = $_SESSION['user_id']; 
        $query = "UPDATE users SET balance = balance + ? WHERE id = ?";
        $stmt = $pdo->prepare($query);  
        if ($stmt->execute([$new_balance, $customer_id])) {
            $_SESSION['balance'] += $new_balance;

            $success_message = "Bakiye başarıyla yüklendi!";
        } else {
            $error_message = "Bakiye yükleme sırasında bir hata oluştu.";
        }
    } 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Para Yükleme</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body class="login-register-page">
<div class="user-info">
    <p>Hoşgeldiniz, <?php echo $_SESSION['name']; ?> <?php echo $_SESSION['surname']; ?></p>
    <p>Hesap Bakiyesi: <?php echo $_SESSION['balance']; ?> TL</p>
    <a href="../index.php">Ana Sayfaya Dön</a><br>
    <a href="customer_profile_update.php">Profili Güncelle</a><br>
    <a href="customer_password_update.php">Şifreni Güncelle</a><br>
    <a href="customer_load_money.php">Bakiye Yükle</a><br>
    <a href="../orders.php">Tüm Siparişlerim</a><br>
    <a href="../logout.php">Çıkış Yap</a>
</div>

<div class="load-money">
    <h2>Para Yükleme</h2> 
    <form action="customer_load_money.php" method="POST">
        <label for="balance">Mevcut Bakiye</label>
        <input type="text" name="balance" value="<?php echo $_SESSION['balance']; ?>" disabled>

        <label for="new_balance">Yüklenecek Bakiye</label>
        <input type="number" name="new_balance" value="" placeholder="0.00" min="0.01" step="0.01" required>

        <button type="submit" name="update_balance">Yükle</button>
    </form>
    
    <?php if (isset($success_message)): ?>
        <p style="color: green;"><?php echo $success_message; ?></p>
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>

</div>
</body>
</html>
