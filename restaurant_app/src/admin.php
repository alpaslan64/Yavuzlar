<?php
include 'session.php'; 
include 'db.php'; 

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$customers = $pdo->query("SELECT * FROM users WHERE (role = 'customer' OR role = 'company')")->fetchAll(PDO::FETCH_ASSOC);

$companies = $pdo->query("
    SELECT company.*, users.name AS owner_name, users.surname AS owner_surname, users.deleted_at AS owner_ban_status
    FROM company
    LEFT JOIN users ON company.id = users.company_id
")->fetchAll(PDO::FETCH_ASSOC);

$restaurants = $pdo->query("SELECT id, name FROM restaurant")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cupon_name'], $_POST['discount'], $_POST['restaurant_id'])) {
    $cupon_name = $_POST['cupon_name'];
    $discount = $_POST['discount'];
    $restaurant_id = $_POST['restaurant_id'];

    $stmt = $pdo->prepare("INSERT INTO cupon (name, discount, restaurant_id) VALUES (:name, :discount, :restaurant_id)");
    $stmt->execute([
        ':name' => $cupon_name,
        ':discount' => $discount,
        ':restaurant_id' => $restaurant_id
    ]);
}

$cupons = $pdo->query("
    SELECT cupon.*, restaurant.name AS restaurant_name 
    FROM cupon 
    JOIN restaurant ON cupon.restaurant_id = restaurant.id
")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['delete_cupon_id'])) {
    $delete_id = $_GET['delete_cupon_id'];
    $stmt = $pdo->prepare("DELETE FROM cupon WHERE id = :id");
    $stmt->execute([':id' => $delete_id]);
    
    header("Location: admin.php");
    exit;
}

$comments = $pdo->query("
    SELECT c.*, u.name AS user_name, u.surname AS user_surname, r.name AS restaurant_name
    FROM comments c
    JOIN users u ON c.user_id = u.id
    JOIN restaurant r ON c.restaurant_id = r.id
    ORDER BY c.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['delete_comment_id'])) {
    $delete_comment_id = $_GET['delete_comment_id'];
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = :id");
    $stmt->execute([':id' => $delete_comment_id]);

    header("Location: admin.php");
    exit;
}


?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="user-info">
        <p>Hoşgeldiniz, <?php echo $name; ?></p>
        <a href="customer-conf/customer.php">Müşteri İşlemleri</a><br>
        <a href="company-conf/company.php">Firma İşlemleri</a><br>
        <a href="index.php">Ana Sayfaya Dön</a><br>
        <a href="logout.php">Çıkış Yap</a>
    </div>
    <div class="admin">
    <h1>Admin Panel</h1>
    <div class="customer-admin">
    <h2>Mevcut Müşteriler</h2>
    
    <input type="text" id="search_customer" placeholder="Müşteri ara (Adı, Soyadı, Kullanıcı Adı, Durumu)">

    <table>
        <thead>
            <tr>
                <th>Ad</th>
                <th>Soyad</th>
                <th>Kullanıcı Adı</th>
                <th>Durum</th>
            </tr>
        </thead>
        <tbody class="customer-table">
            <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?php echo $customer['name']; ?></td>
                    <td><?php echo $customer['surname']; ?></td>
                    <td><?php echo $customer['username']; ?></td>
                    <td>
                        <?php if ($customer['deleted_at']): ?>
                            <strong><span>Banlı</span></strong>
                        <?php else: ?>
                            <span>Aktif</span>
                        <?php endif; ?>
                    </td>                    
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <!--<a href="/customer-conf/customer.php">
        <button class="customerphp_button" type="button">Kullanıcı İşlemleri İçin Tıklayın</button>
    </a>-->
</div>
    

<div class="company-admin">
    <h2>Mevcut Firmalar</h2>
    <input type="text" id="search_company" placeholder="Firma Ara (Adı, Sahibi, Açıklaması, Durumu)">

    <table>
    <thead>
        <tr>
            <th>Firma Adı</th>
            <th>Sahibi</th>
            <th>Açıklama</th>
            <th>Durum</th> 
        </tr>
    </thead>
    <tbody class="company-table">
        <?php foreach ($companies as $company): ?>
            <tr>
                <td><?php echo $company['name']; ?></td>
                <td><?php echo $company['owner_name'] . ' ' . $company['owner_surname']; ?></td>
                <td><?php echo $company['description']; ?></td>
                <td>
                    <?php if ($company['owner_ban_status']): ?>
                        <strong><span>Banlı</span></strong> 
                    <?php else: ?>
                        <span>Aktif</span> 
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<!--<a href="/company-conf/company.php">
        <button class="customerphp_button" type="button">Firma İşlemleri İçin Tıklayın</button>
    </a>-->
</div>
    
<div class="coupon-admin">
    <h2>Kupon Yönetimi</h2>
    <form method="POST" action="">
        <label for="cupon_name">Kupon Adı:</label>
        <input type="text" name="cupon_name" required>

        <label for="discount">İndirim (%):</label>
        <input type="number" name="discount" min="0" max="100" required>

        <label for="restaurant_id">Restoran Seç:</label>
        <select name="restaurant_id" required>
            <option value="" disabled selected>Seçiniz</option>
            <?php foreach ($restaurants as $restaurant): ?>
                <option value="<?php echo $restaurant['id']; ?>"><?php echo $restaurant['name']; ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Kupon Ekle</button>
    </form>

    <h3>Mevcut Kuponlar</h3>
    <table>
        <thead>
            <tr>
                <th>Kupon Adı</th>
                <th>İndirim</th>
                <th>Restoran</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cupons as $cupon): ?>
                <tr>
                    <td><?php echo $cupon['name']; ?></td>
                    <td><?php echo $cupon['discount']; ?>%</td>
                    <td><?php echo $cupon['restaurant_name']; ?></td>
                    <td>
                        <a href="admin.php?delete_cupon_id=<?php echo $cupon['id']; ?>">Sil</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="comment-admin">
    <h2>Yorum Yönetimi</h2>
    <table>
        <thead>
            <tr>
                <th>Yorum Sahibi</th>
                <th>Restoran</th>
                <th>Başlık</th>
                <th>Açıklama</th>
                <th>Puan</th>
                <th>Tarih</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comments as $comment): ?>
                <tr>
                    <td><?php echo $comment['user_name'] . ' ' . $comment['user_surname']; ?></td>
                    <td><?php echo $comment['restaurant_name']; ?></td>
                    <td><?php echo $comment['title']; ?></td>
                    <td><?php echo $comment['description']; ?></td>
                    <td><?php echo $comment['score']; ?></td>
                    <td><?php echo $comment['created_at']; ?></td>
                    <td>
                        <a href="admin.php?delete_comment_id=<?php echo $comment['id']; ?>">Sil</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


    </div>

    <script>
    function searchTable(inputSelector, tableRowsSelector, columnsToSearch) {
        document.querySelector(inputSelector).addEventListener('input', function () {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll(tableRowsSelector);

            rows.forEach(row => {
                let found = false;

                columnsToSearch.forEach(colIndex => {
                    const cellValue = row.querySelector(`td:nth-child(${colIndex})`).textContent.toLowerCase();
                    if (cellValue.includes(searchValue)) {
                        found = true;
                    }
                });

                row.style.display = found ? '' : 'none';  
            });
        });
    }

    searchTable('#search_customer', '.customer-table tr', [1, 2, 3, 4]);

    searchTable('#search_company', '.company-table tr', [1, 2, 3, 4]);
</script>
</body>
</html>
