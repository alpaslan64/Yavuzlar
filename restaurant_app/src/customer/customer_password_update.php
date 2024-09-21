<?php
session_start();

include '../db.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_new_password = password_hash($new_password, PASSWORD_ARGON2ID);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed_new_password, $user_id])) {
                $password_update_message = "Şifreniz başarıyla güncellendi.";
            } else {
                $password_update_error = "Şifre güncelleme sırasında bir hata oluştu.";
            }
        } else {
            $password_update_error = "Yeni şifreler eşleşmiyor.";
        }
    } else {
        $password_update_error = "Mevcut şifre yanlış.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
        <a href="">Tüm Siparişlerim</a><br>
        <a href="/logout.php">Çıkış Yap</a>
    </div>
<div class="password-update">
        <h2>Şifre Güncelleme</h2>
        <form action="customer_password_update.php" method="POST">
            <label for="current_password">Mevcut Şifre:</label>
            <input type="password" name="current_password" required>

            <label for="new_password">Yeni Şifre:</label>
            <input type="password" name="new_password" required>

            <label for="confirm_password">Yeni Şifre Tekrar:</label>
            <input type="password" name="confirm_password" required>

            <button type="submit" name="update_password">Şifreyi Güncelle</button>
        </form>
        <?php if (isset($password_update_message)):?> 
            <script>
                alert("<?php echo $password_update_message; ?>");
                window.location.href = "../login.php";
            </script>
        <?php elseif (isset($password_update_error)):?> 
            <?php echo "<p>$password_update_error</p>"; ?>
            <?php endif; ?>
    </div>
</body>
</html>