<?php
include '../session.php'; 
include '../db.php'; 

if ($_SESSION['role'] !== 'admin') {
    header("Location: /index.php");
    exit;
}

$customers = $pdo->query("SELECT * FROM users WHERE (role = 'customer' OR role = 'company')")->fetchAll(PDO::FETCH_ASSOC);

$edit_customer = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $edit_stmt->execute([$edit_id]);
    $edit_customer = $edit_stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (!empty($_POST['customer_id'])) {
        $customer_id = $_POST['customer_id'];
        
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_ARGON2ID);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, surname = ?, username = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $surname, $username, $hashed_password, $customer_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, surname = ?, username = ? WHERE id = ?");
            $stmt->execute([$name, $surname, $username, $customer_id]);
        }
        
    } else { 
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_ARGON2ID); 
            $stmt = $pdo->prepare("INSERT INTO users (name, surname, username, password, role) VALUES (?, ?, ?, ?, 'customer')");
            $stmt->execute([$name, $surname, $username, $hashed_password]);
        } else {
            echo "Lütfen şifre giriniz.";
        }
    }
    header("Location: customer.php");
    exit();
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->execute([$delete_id]);
    header("Location: customer.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri Yönetimi</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>

<div class="admin">
<div class="customer-admin">
<div class="user-info">
        <p>Hoşgeldiniz, <?php echo $name; ?></p>
        <a href="/index.php">Ana Sayfaya Dön</a><br>
        <a href="/admin.php">Admin Paneli</a><br>
        <a href="/customer-conf/customer.php">Müşteri İşlemleri</a><br>
        <a href="/company-conf/company.php">Firma İşlemleri</a><br>
        <a href="/logout.php">Çıkış Yap</a>
    </div>
    <h1>Müşteri Yönetimi</h1>
    
    <input type="text" id="search_customer" placeholder="Müşteri ara (Adı, Soyadı, Kullanıcı Adı, Durumu)">

    <table>
        <thead>
            <tr>
                <th>Ad</th>
                <th>Soyad</th>
                <th>Kullanıcı Adı</th>
                <th>Durum</th>
                <th>İşlem</th>
                <th>Ban</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach ($customers as $customer): ?>
        <tr>
            <td><?php echo $customer['name']; ?></td>
            <td><?php echo $customer['surname']; ?></td>
            <td><?php echo $customer['username']; ?></td>
            <td>
                <?php if ($customer['company_id']): ?>
                    <span>Firma Sahibi</span>
                <?php elseif ($customer['deleted_at']): ?>
                    <strong><span>Banlı</span></strong>
                <?php else: ?>
                    <strong><span>Aktif</span></strong>
                <?php endif; ?>
            </td>
            <td>
                <a href="?edit=<?php echo $customer['id']; ?>">Düzenle</a>
                <a href="?delete=<?php echo $customer['id']; ?>">Sil</a>
            </td>
            <td>
            <?php if($customer['company_id'] !== null): ?> 
                <p>İşlem Yapılamaz</p>      
            <?php elseif($customer['deleted_at'] !== null): ?>
                    <a href="unban_customer.php?id=<?php echo $customer['id']; ?>">Banı Kaldır</a>
                <?php else: ?>
                <a href="ban_customer.php?id=<?php echo $customer['id']; ?>">Banla</a>
                <?php endif;?>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
    </table>

    <form method="POST">
        <input type="hidden" name="customer_id" value="<?php echo $edit_customer ? $edit_customer['id'] : ''; ?>">
        <label for="name">Ad:</label>
        <input type="text" name="name" value="<?php echo $edit_customer ? $edit_customer['name'] : ''; ?>" required>
        <label for="surname">Soyad:</label>
        <input type="text" name="surname" value="<?php echo $edit_customer ? $edit_customer['surname'] : ''; ?>" required>
        <label for="username">Kullanıcı Adı:</label>
        <input type="text" name="username" value="<?php echo $edit_customer ? $edit_customer['username'] : ''; ?>" required>
        
        <label for="password">Şifre:</label>
        <input type="password" name="password" value="" placeholder="<?php echo $edit_customer ? 'Yeni şifreyi girin ya da boş bırakın' : ''; ?>">
        
        <button type="submit"><?php echo $edit_customer ? 'Güncelle' : 'Ekle'; ?></button>
    </form>

    <a href="../admin.php">
        <button class="customerphp_button" type="button">Admin Panele Gitmek İçin Tıklayın</button>
    </a>
</div>
</div>

<script>
document.querySelector('#search_customer').addEventListener('input', function () {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const name = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
        const surname = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const username = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        const deleted_at = row.querySelector('td:nth-child(4)').textContent.toLowerCase();

        if (name.includes(searchValue) || surname.includes(searchValue) || username.includes(searchValue) || deleted_at.includes(searchValue)) {
            row.style.display = '';  
        } else {
            row.style.display = 'none';  
        }
    });
});
</script>

</body>
</html>
