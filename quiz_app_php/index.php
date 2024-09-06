<?php
session_start();
session_destroy();
//header('Location: index.php');
//include('logout.php');

//$username = $_SESSION['username'];
//$role = $_SESSION['role'];


if (!isset($_SESSION) || !isset($_SESSION['role'])) {
    echo "<script>alert('Tüm Oturumlar Kapatıldı!!')</script>";
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Quiz Application</title>
</head>
<body>
    <div class="container-index">
        <h1>Yavuzlar Soru Uygulaması</h1>
        <!--<p>Kullanıcı Adı: <?php echo htmlspecialchars($username); ?></p>-->
        <!--<p>Profil: <?php echo htmlspecialchars($role); ?></p>-->
        <button onclick="location.href='login.php'">Giriş Yap</button><br>
        <button onclick="location.href='register.php'">Kayıt Ol</button><br>
        <!--<button onclick="location.href='quiz.php'">Soruları Çöz</button>-->
    </div>
</body>
</html>
