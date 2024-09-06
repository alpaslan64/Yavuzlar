<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    $stmt = $db->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
    $stmt->execute([$username, $password, $role]);

    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Kayıt Ol</title>
</head>
<body>
    <div class="container-register">
        <h1>Kayıt Ol</h1>
        <form method="post">
            <label for="username">Kullanıcı Adı:</label>
            <input type="text" name="username" required><br>
            <label for="password">Şifre:</label>
            <input type="password" name="password" required><br>
            <label for="role">Rol:</label>
            <select name="role">
                <option value="student">Öğrenci</option>
                <option value="admin">Admin</option>
            </select><br>
            <button type="submit">Kayıt Ol</button>
        </form>
        <button type="button" onclick="location.href='index.php'">Ana Sayfa</button>
    </div>
</body>
</html>
