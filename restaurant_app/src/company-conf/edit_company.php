<?php
include '../db.php';
include '../session.php';
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

if (isset($_GET['id'])) {
    $companyId = $_GET['id'];

    $query = $pdo->prepare("SELECT * FROM company WHERE id = ?");
    $query->execute([$companyId]);
    $company = $query->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_id = $_POST['edit_company_id'];
    $new_name = $_POST['company_name'];
    $new_description = $_POST['company_description'];
    $new_logo_path = null;

    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] == UPLOAD_ERR_OK) {
        $logo_tmp_name = $_FILES['company_logo']['tmp_name'];
        $logo_name = $_FILES['company_logo']['name'];
        $logo_target_dir = "../img/";  
        $logo_target_file = $logo_target_dir . basename($logo_name);

        if (move_uploaded_file($logo_tmp_name, $logo_target_file)) {
            $new_logo_path = $logo_target_file; 
        }
    }

    if ($new_logo_path) {
        $update_query = $pdo->prepare("UPDATE company SET name = ?, description = ?, logo_path = ? WHERE id = ?");
        $update_query->execute([$new_name, $new_description, $new_logo_path, $company_id]);
    } else {
        $update_query = $pdo->prepare("UPDATE company SET name = ?, description = ? WHERE id = ?");
        $update_query->execute([$new_name, $new_description, $company_id]);
    }

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
    <title>Firma Düzenleme</title>
</head>
<body>

<div class="admin">

<div class="user-info">
        <p>Hoşgeldiniz, <?php echo $name; ?></p>
        <a href="/index.php">Ana Sayfaya Dön</a><br>
        <a href="/admin.php">Admin Paneli</a><br>
        <a href="/customer-conf/customer.php">Müşteri İşlemleri</a><br>
        <a href="company.php">Firma İşlemleri</a><br>
        <a href="/logout.php">Çıkış Yap</a>
    </div>

    <h2>Firma Düzenleme</h2>
    <form method="POST" action="edit_company.php" enctype="multipart/form-data">
        <input type="hidden" name="edit_company_id" value="<?php echo isset($company) ? $company['id'] : ''; ?>">

        <label for="company_name">Firma Adı:</label>
        <input type="text" name="company_name" value="<?php echo isset($company) ? $company['name'] : ''; ?>" required>

        <label for="company_description">Firma Açıklaması:</label>
        <input type="text" name="company_description" value="<?php echo isset($company) ? $company['description'] : ''; ?>" required>

        <label for="company_logo">Firma Logosu:</label>
        <?php if (isset($company) && $company['logo_path']): ?>
            <img src="<?php echo $company['logo_path']; ?>" alt="Logo" width="100">
        <?php endif; ?>
        <input type="file" name="company_logo">

        <button type="submit">Kaydet</button>
    </form>
</div>

</body>
</html>
