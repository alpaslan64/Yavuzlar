<?php
session_start();
include('db.php');

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        $stmt = $db->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        $stmt->execute([$username, $password, $role]);
    } elseif (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];

        $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
    }
}

$users = $db->query('SELECT * FROM users')->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Kullanıcı Yönetimi</title>
</head>
<body>
    <div class="container">
        <h1>Kullanıcı Yönetimi</h1>
        
        <h2>Kullanıcı Ekle</h2>
        <form method="post">
            <label for="username">Kullanıcı Adı:</label>
            <input type="text" name="username" id="username" required>
            
            <label for="password">Şifre:</label>
            <input type="password" name="password" id="password" required>
            
            <label for="role">Rol:</label>
            <select name="role" id="role">
                <option value="admin">Admin</option>
                <option value="student">Öğrenci</option>
            </select>
            
            <button type="submit" name="add_user">Kullanıcı Ekle</button>
        </form>

        <h2>Mevcut Kullanıcılar</h2>
        <ul>
            <?php foreach ($users as $user): ?>
                <li>
                    <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="delete_user">Sil</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <br>
        <button onclick="location.href='profile.php'">Profile Dön</button>
    </div>
</body>
</html>
