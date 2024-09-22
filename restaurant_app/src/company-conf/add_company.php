<?php
include '../db.php';
include '../session.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$owners = $pdo->query("SELECT id, name, surname FROM users WHERE role = 'customer'")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['company_name'];
    $description = $_POST['company_description'];
    $logo_path = null;

    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] == UPLOAD_ERR_OK) {
        $logo_tmp_name = $_FILES['company_logo']['tmp_name'];
        $logo_name = $_FILES['company_logo']['name'];
        $logo_target_dir = "../img/";
        $logo_target_file = $logo_target_dir . basename($logo_name);

        if (move_uploaded_file($logo_tmp_name, $logo_target_file)) {
            $logo_path = $logo_target_file;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO company (name, description, logo_path) VALUES (?, ?, ?)");
    $stmt->execute([$name, $description, $logo_path]);

    header("Location: company.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style.css">
    <title>Firma Ekle/Düzenle</title>
</head>
<body>

<div class="user-info">
            <p>Hoşgeldiniz, <?php echo $name; ?></p>
            <a href="../admin.php">Admin Paneli</a><br>
            <a href="../index.php">Ana Sayfaya Dön</a>
            <br><a href="../logout.php">Çıkış Yap</a>
</div>

<div class="admin">
    <h2>Yeni Firma Ekle</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <label for="company_name">Firma Adı:</label>
        <input type="text" name="company_name" required>

        <label for="company_description">Firma Açıklaması:</label>
        <input type="text" name="company_description" required>

        <label for="company_logo">Firma Logosu:</label>
        <input type="file" name="company_logo">

        <button type="submit">Ekle</button>
    </form>
</div>

</body>
</html>
