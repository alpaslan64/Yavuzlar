<?php
session_start();

if ($_SESSION['role'] !== 'customer') {
    header("Location: ../index.php");
    exit;
}


include '../db.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $surname = $_POST['surname'];

    $stmt = $pdo->prepare("UPDATE users SET name = ?, surname = ? WHERE id = ?");
    if ($stmt->execute([$name, $surname, $user_id])) {
        $_SESSION['name'] = $name;
        $_SESSION['surname'] = $surname;

        $update_message = "Profiliniz başarıyla güncellendi.";
    } else {
        $update_error = "Profil güncelleme sırasında bir hata oluştu.";
    }
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Yönetimi</title>
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
        <a href="../logout.php">Çıkış Yap</a>
    </div>

    <div class="profile-update">
        <h2>Profil Güncelleme</h2>
        <form action="customer_profile_update.php" method="POST">
            <label for="name">Ad:</label>
            <input type="text" name="name" value="<?php echo $user['name']; ?>" required>

            <label for="surname">Soyad:</label>
            <input type="text" name="surname" value="<?php echo $user['surname']; ?>" required>

            <label for="username">Kullanıcı Adı:</label>
            <input type="text" name="username" value="<?php echo $user['username']; ?>" required>

            <button type="submit" name="update_profile">Güncelle</button>
        </form>
        <?php if (isset($update_message)) echo "<p style='color: green;'>$update_message</p>"; ?>
        <?php if (isset($update_error)) echo "<p style='color: red;'>$update_error</p>"; ?>
    </div>
</body>
</html>
