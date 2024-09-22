<?php
session_start();
include '../db.php';

if ($_SESSION['role'] !== 'company') {
    header('Location: ../index.php');
    exit;
}

$query = "SELECT * FROM company WHERE id = :company_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':company_id', $_SESSION['company_id'], PDO::PARAM_INT);
$stmt->execute();
$company = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM restaurant WHERE company_id = ?");
$stmt->execute([$_SESSION['company_id']]);
$restaurants = $stmt->fetchAll();

$stmtFood = $pdo->prepare("
    SELECT food.*, restaurant.name AS restaurant_name 
    FROM food 
    JOIN restaurant ON food.restaurant_id = restaurant.id 
    WHERE restaurant.company_id = ?
");
$stmtFood->execute([$_SESSION['company_id']]);
$foods = $stmtFood->fetchAll();

$query = "
    SELECT comments.*, restaurant.name AS restaurant_name
    FROM comments
    JOIN restaurant ON comments.restaurant_id = restaurant.id
    JOIN company ON restaurant.company_id = company.id
    WHERE company.id = ?
";

$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['company_id']]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $commentId = $_GET['id'];

    $query = "DELETE FROM comments WHERE id = ?";
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute([$commentId])) {
        header('Location: company_profile.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Profili</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>

<div class="user-info">
        <p>Hoşgeldiniz, <?php echo $_SESSION['name'] . $_SESSION['surname']; ?></p>
        <p><?php echo "(". $_SESSION['company_name'] . ")"; ?></p>
        <a href="../index.php">Ana Sayfaya Dön</a><br>
        <a href="restaurants.php">Restoran İşlemleri</a><br>
        <a href="foods.php">Yemek İşlemleri</a><br>
        <a href="orders.php">Sipariş İşlemleri</a><br>
        <a href="/logout.php">Çıkış Yap</a>
    </div>

<div class="restaurant-profile">
    <h1>Firma Profili</h1>

    <div class="restaurant-list">
    <center><h2>Mevcut Restoranlar</h2></center>
    <input type="text" id="search_restaurant" placeholder="Restoran Ara (Adı, Açıklaması, Tarihi)">
    <table>
        <thead>
            <tr>
                <th>Restoran Adı</th>
                <th>Açıklama</th>
                <th>Görsel</th>
                <th>Oluşturulma Tarihi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($restaurants as $restaurant): ?>
            <tr>
                <td><?php echo $restaurant['name']; ?></td>
                <td><?php echo $restaurant['description']; ?></td>
                <td><img src="<?php echo $restaurant['image_path']; ?>" alt="Restoran Görseli" width="100"></td>
                <td><?php echo $restaurant['created_at']; ?></td>

            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>



<div class="food-list">
    <h2>Mevcut Yemekler</h2>
    <input type="text" id="search_food" placeholder="Yemek Ara (Restoran, Yemek Adı, Açıklama, Fiyat, İndirim, Tarih)">

    <table>
        <thead>
            <tr>
                <th>Restoran</th>
                <th>Yemek Adı</th>
                <th>Açıklama</th>
                <th>Fiyat</th>
                <th>İndirim (%)</th>
                <th>Görsel</th>
                <th>Oluşturulma Tarihi</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($foods as $food): ?>
            <tr>
                <td><?php echo $food['restaurant_name']; ?></td>
                <td><?php echo $food['name']; ?></td>
                <td><?php echo $food['description']; ?></td>
                <td><?php echo $food['price']; ?></td>
                <td><?php echo $food['discount']; ?></td>
                <td><img src="<?php echo $food['image_path']; ?>" alt="Yemek Görseli" width="100"></td>
                <td><?php echo $food['created_at']; ?></td>
                <td>
                    <a href="foods.php?edit_id=<?php echo $food['id']; ?>">Düzenle</a>
                    <a href="foods.php?delete_id=<?php echo $food['id']; ?>" onclick="return confirm('Bu yemeği silmek istediğinizden emin misiniz?')">Sil</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="comments-section">
    <h2>Restoran Yorumları</h2>
    <table>
        <tr>
            <th>Restoran Adı</th>
            <th>Yorum Başlığı</th>
            <th>Açıklama</th>
            <th>Puan</th>
            <th>Tarih</th>
            <th>İşlem</th>
        </tr>
        <?php foreach ($comments as $comment): ?>
            <tr>
                <td><?php echo $comment['restaurant_name']; ?></td>
                <td><?php echo $comment['title']; ?></td>
                <td><?php echo $comment['description']; ?></td>
                <td><?php echo $comment['score']; ?>/10</td>
                <td><?php echo $comment['created_at']; ?></td>
                <td><a href="company_profile.php?id=<?php echo $comment['id']; ?>&action=delete">Sil</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
</div>



<script>
    document.querySelector('#search_restaurant').addEventListener('input', function () {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const name = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
            const description = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const date = row.querySelector('td:nth-child(4)').textContent.toLowerCase();

            if (name.includes(searchValue) || description.includes(searchValue) || date.includes(searchValue)) {
                row.style.display = '';  
            } else {
                row.style.display = 'none';  
            }
        });
    });
</script>

</body>
</html>
