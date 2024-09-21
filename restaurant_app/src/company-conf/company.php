<?php
include '../db.php';
include '../session.php';

$companies = $pdo->query("
    SELECT company.*, users.name AS owner_name, users.surname AS owner_surname, users.deleted_at AS owner_ban_status
    FROM company
    LEFT JOIN users ON company.id = users.company_id
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style.css">
    <title>Firma Yönetimi</title>
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

    <div class="company-admin">
        <h1>Firma Yönetimi</h1>
        <form method="POST" action="add_company.php" enctype="multipart/form-data">
            <label for="company_name">Firma Adı:</label>
            <input type="text" name="company_name" required>

            <label for="company_description">Firma Açıklaması:</label>
            <input type="text" name="company_description" required>

            <label for="company_logo">Firma Logosu:</label>
            <input type="file" name="company_logo" required>

            <button type="submit">Firma Ekle</button>
        </form>

        <h3>Mevcut Firmalar</h3>
        <input type="text" id="search_company" placeholder="Firma Ara (Adı, Sahibi, Açıklaması, Durumu)">

        <table>
            <thead>
                <tr>
                    <th>Logo</th> 
                    <th>Firma Adı</th>
                    <th>Sahibi</th>
                    <th>Açıklama</th>
                    <th>Durum</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody class="company-table">
                <?php foreach ($companies as $company): ?>
                    <tr>
                        <td>
                            <?php if ($company['logo_path']): ?>
                                <img src="<?php echo $company['logo_path']; ?>" alt="Logo" class="company-logo">
                            <?php else: ?>
                                <span>Logo Yok</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $company['name']; ?></td>
                        <td><?php echo $company['owner_name'] . ' ' . $company['owner_surname']; ?>
                        <?php if(!$company['owner_name']): ?>
                            <strong><span>Firma Elle Eklendi!!</span></strong>
                            <?php endif; ?></td>
                        <td><?php echo $company['description']; ?></td>
                        <td>
                        <?php if ($company['deleted_at'] && $company['owner_ban_status']): ?>
                            <strong><span>Silindi (Banlı)</span></strong> 
                        <?php elseif ($company['deleted_at']): ?>
                            <strong><span>Silindi</span></strong> 
                        <?php elseif ($company['owner_ban_status']): ?>
                            <strong><span>Banlı</span></strong> 
                        <?php else: ?>
                            <span>Aktif</span> 
                        <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_company.php?id=<?php echo $company['id']; ?>">Düzenle</a>
                            <?php if ($company['deleted_at']): ?>
                                <a href="delete_company.php?id=<?php echo $company['id']; ?>">Geri Getir</a>
                            <?php else: ?>
                                <a href="delete_company.php?id=<?php echo $company['id']; ?>">Sil</a>
                            <?php endif; ?>                        
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
    </div>
</div>

<script>
    document.querySelector('#search_company').addEventListener('input', function () {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const owner_name = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const description = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
            const owner_ban_status = row.querySelector('td:nth-child(5)').textContent.toLowerCase();

            if (name.includes(searchValue) || owner_name.includes(searchValue) || description.includes(searchValue) || owner_ban_status.includes(searchValue)) {
                row.style.display = '';  
            } else {
                row.style.display = 'none';  
            }
        });
    });
</script>
</body>
</html>
