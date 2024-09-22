<?php
session_start();

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        if (password_verify($password, $user['password'])) {
            if ($user['deleted_at'] !== null) {
                $banned_error = "Kullanıcı Banlıdır!!";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['surname'] = $user['surname'];
                $_SESSION['balance'] = $user['balance'];

                if (!empty($user['company_id'])) {
                    $stmtCompany = $pdo->prepare("SELECT name, description, logo_path FROM company WHERE id = ?");
                    $stmtCompany->execute([$user['company_id']]);
                    $company = $stmtCompany->fetch();

                    if ($company) {
                        $_SESSION['company_id'] = $user['company_id'];
                        $_SESSION['company_name'] = $company['name'];
                        $_SESSION['company_description'] = $company['description'];
                        $_SESSION['company_logo'] = $company['logo_path'];
                    }
                }

                header('Location: index.php');
                exit;
            }
        } else {
            $user_error = "Kullanıcı adı veya şifre hatalı.";
        }
    } else {
        $user_error = "Kullanıcı adı veya şifre hatalı.";
    }
}
?>


<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body class="login-register-page">
    <div class="login-container">
    <h1>Giriş Yap</h1>
    <form action="login.php" method="POST">
        <label for="username">Kullanıcı Adı:</label>
        <input type="text" name="username" required>

        <label for="password">Şifre:</label>
        <input type="password" name="password" required>

        <button type="submit">Giriş Yap</button>
    </form>
    <?php if (isset($user_error)): ?>
    <p class="error-message"><?php echo $user_error; ?></p>
<?php elseif (isset($banned_error)): ?>
    <p class="error-message"><?php echo $banned_error; ?></p>
<?php endif; ?>
<p>Hesabınız yok mu? <a href="register.php">Kayıt Ol</a></p>
</body>
</html>
