<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->rowCount() > 0) {
        $user_find_error = "Bu kullanıcı adı zaten kullanılıyor.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_ARGON2ID);

        if ($role === 'company') {
            $company_name = $_POST['company_name'];
            $company_description = $_POST['company_description'];
            
            
            if (isset($_FILES['company_logo_path']) && $_FILES['company_logo_path']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = mime_content_type($_FILES['company_logo_path']['tmp_name']);
                
                if (in_array($file_type, $allowed_types)) {
                    $logo_dir = 'src/img/';
                    $logo_path = $logo_dir . basename($_FILES['company_logo_path']['name']);
                    
                    if (move_uploaded_file($_FILES['company_logo_path']['tmp_name'], $logo_path)) {
                        $img_upload_succes = "Logo Yükleme Başarılı!!";
                    }
                } else {
                    $img_upload_fail = "Logo Yükleme Başarısız!!"; 
                }
            }

            $stmt = $pdo->prepare("INSERT INTO company (name, description, logo_path) VALUES (?, ?, ?)");
            $stmt->execute([$company_name, $company_description, $logo_path]);
            $company_id = $pdo->lastInsertId(); 
        } else {
            $company_id = NULL; 
        }

        $stmt = $pdo->prepare( "INSERT INTO users (name, surname, username, password, role, company_id) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $surname, $username, $hashed_password, $role, $company_id])) {
            $user_cong = "Kayıt Başarılı!!";
            header('Location: login.php'); 
        } else {
            $user_error_register = "Kayıt sırasında bir hata oluştu.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol</title>
    <link rel="stylesheet" href="style.css"> 
    <script>
    function toggleCompanyFields() {
        const role = document.querySelector('input[name="role"]:checked').value;
        const companyFields = document.getElementById('company-fields');
        const companyName = document.getElementById('company_name');
        const companyDescription = document.getElementById('company_description');
        const companyLogo = document.getElementById('company_logo_path');
            
        if (role === 'company') {
            companyFields.style.display = 'block';
            companyName.required = true;
            companyDescription.required = true;
            companyLogo.required = true;
        } else {
            companyFields.style.display = 'none';
            companyName.required = false;
            companyDescription.required = false;
            companyLogo.required = false;
        }
    }
    </script>
</head>
<body class="login-register-page">
    <div class="register-container">
        <h1>Kayıt Ol</h1>
        <form action="register.php" method="POST" enctype="multipart/form-data">
            <div class="radio-group">
                <label for="role">Kayıt Tipi:</label>
                <label>
                    <input type="radio" name="role" value="customer" checked onclick="toggleCompanyFields()"> Müşteri
                </label>
                <label>
                    <input type="radio" name="role" value="company" onclick="toggleCompanyFields()"> Şirket
                </label>
            </div>

            <label for="name">Ad:</label>
            <input type="text" name="name" required>

            <label for="surname">Soyad:</label>
            <input type="text" name="surname" required>

            <label for="username">Kullanıcı Adı:</label>
            <input type="text" name="username" required>

            <label for="password">Şifre:</label>
            <input type="password" name="password" required>

            <div id="company-fields" style="display: none;">
                <label for="company_name">Şirket Adı:</label>
                <input type="text" name="company_name" id="company_name">

                <label for="company_address">Şirket Açıklaması:</label>
                <input type="text" name="company_description" id="company_description">

                <label for="company_logo_path">Şirket Logosu:</label>
                <input id="logo" type="file" name="company_logo_path" id="company_logo_path">
            </div>

            <button type="submit">Kayıt Ol</button>
        </form>

        <?php if (isset($user_error_register)): ?>
            <p class="error-message"><?php echo $user_error_register; ?></p>
        <?php elseif (isset($user_find_error)): ?>
            <p class="error-message"><?php echo $user_find_error; ?></p>
        <?php elseif (isset($user_cong) && $user_cong): ?>
            <script>
                alert('<?php echo $user_cong; ?>');
                window.location.href = 'login.php';
            </script>
        <?php //elseif (isset($img_upload_fail)): ?>
            <p class="error-message"><?php //echo $img_upload_fail?></p>
        <?php //elseif (isset($img_upload_succes)): ?>
            <p class="error-message"><?php //echo $img_upload_succes?></p>
        <?php endif; ?>

        <p>Zaten hesabınız var mı? <a href="login.php">Giriş yap</a></p>
    </div>
</body>
</html>