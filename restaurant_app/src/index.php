<?php
session_start();

include 'db.php';

if (isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    $name = $_SESSION['name'] . ' ' . $_SESSION['surname'];
    if ($role === 'admin') {
        $name = $_SESSION['name'];
    }
}

$total_items = 0; 
if (isset($_SESSION['user_id'])) {
    $query = "SELECT COUNT(*) AS total_items FROM basket WHERE user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_items = $result['total_items']; 
}

$query = "
    SELECT food.*, restaurant.name AS restaurant_name, restaurant.id AS restaurant_id, 
           company.name AS company_name, restaurant.image_path AS restaurant_image, 
           AVG(comments.score) AS avg_score
    FROM food
    JOIN restaurant ON food.restaurant_id = restaurant.id
    JOIN company ON restaurant.company_id = company.id
    LEFT JOIN comments ON restaurant.id = comments.restaurant_id
    GROUP BY restaurant.id, food.id
    ORDER BY company.id, restaurant.id
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

$commentsQuery = "
    SELECT comments.*, users.name AS user_name, users.surname AS user_surname
    FROM comments
    JOIN users ON comments.user_id = users.id
";
$commentsStmt = $pdo->prepare($commentsQuery);
$commentsStmt->execute();
$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

$cuponQuery = "
    SELECT restaurant.id AS restaurant_id, GROUP_CONCAT(cupon.name) AS cupon_codes
    FROM restaurant
    LEFT JOIN cupon ON restaurant.id = cupon.restaurant_id
    GROUP BY restaurant.id;
";

$cuponStmt = $pdo->prepare($cuponQuery);
$cuponStmt->execute();
$cuponResults = $cuponStmt->fetchAll(PDO::FETCH_ASSOC);

$cuponCodes = [];
foreach ($cuponResults as $cupon) {
    $cuponCodes[$cupon['restaurant_id']] = $cupon['cupon_codes'];
}

$currentCompany = null;
$currentRestaurant = null;

$food['avg_score'] = 0;

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ana Sayfa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="user-info">
        <?php if (!isset($role)): ?>
            <p>Hoşgeldiniz</p><br>
            <a href="login.php">Giriş Yap</a><br>
            <a href="register.php">Kayıt Ol</a>
        <?php elseif ($role === 'admin'): ?>
            <p>Hoşgeldiniz, <?php echo $name; ?></p><br>
            <a href="index.php">Ana Sayfaya Dön</a><br>
            <a href="admin.php">Admin Paneli</a><br>
            <a href="logout.php">Çıkış Yap</a>
        <?php elseif ($role === 'company'): ?>
            <p>Hoşgeldiniz, <?php echo $name; ?></p>
            <a href="index.php">Ana Sayfaya Dön</a><br>
            <a href="company/company_profile.php">Firma Profili</a><br>
            <a href="logout.php">Çıkış Yap</a>
        <?php elseif ($role === 'customer'): ?>
            <p>Hoşgeldiniz, <?php echo $name; ?></p>
            <p>Hesap Bakiyesi: <?php echo $_SESSION['balance']; ?> TL</p>
            <a href="index.php">Ana Sayfaya Dön</a><br>
            <a href="customer/customer_profile_update.php">Profili Güncelle</a><br>
            <a href="customer/customer_password_update.php">Şifreni Güncelle</a><br>
            <a href="customer/customer_load_money.php">Bakiye Yükle</a><br>
            <a href="basket.php">Sepetim (<?php echo $total_items; ?>)</a><br>
            <a href="orders.php">Tüm Siparişlerim</a><br>
            <a href="logout.php">Çıkış Yap</a>
        <?php endif; ?>
    </div>

    <div class="content">
        <h1>Restoranlar ve Yiyecekler</h1>

        <?php foreach ($foods as $food): ?>

<?php if ($currentCompany !== $food['company_name']): ?>
    <?php if ($currentCompany !== null): ?>
        </div> 
    <?php endif; ?>

    <h2 class="company-name"><?php echo $food['company_name']; ?></h2>
    <?php $currentCompany = $food['company_name']; ?>
    <?php $currentRestaurant = null; ?>
<?php endif; ?>

<?php if ($currentRestaurant !== $food['restaurant_id']): ?>
    <?php if ($currentRestaurant !== null): ?>
        </div> 
    <?php endif; ?>

    <h3 class="restaurant-name">
        <?php 
            echo $food['restaurant_name'] . " (" . 
            (!empty($food['avg_score']) ? number_format($food['avg_score'], 1) : '0') . ")";
        ?>
        <?php if (isset($cuponCodes[$food['restaurant_id']])): ?>
            <span class="cupon-show">- Kupon Kodu: <?php echo $cuponCodes[$food['restaurant_id']]; ?></span>
        <?php endif; ?>
        </h3>    
    <a href="#" class="show-comments" data-restaurant-id="<?php echo $food['restaurant_id']; ?>">Yorumlar</a><br><br>
    <div class="restaurant-row">
    <?php $currentRestaurant = $food['restaurant_id']; ?>
<?php endif; ?>

<div class="food-card">
    <img src="<?php echo $food['image_path']; ?>" alt="<?php echo $food['name']; ?>">
    <div class="food-info">
        <h3><?php echo $food['name']; ?></h3>
        <p><?php echo $food['description']; ?></p>
        <p>Fiyat: <?php echo $food['price']; ?> TL</p>
        <?php if ($food['discount'] > 0):
            $discountedPrice = $food['price'] * (1 - $food['discount'] / 100);
        ?>
            <p>İndirim: <?php echo $food['discount']; ?>%</p>
            <p style="font-size: 20px; font-weight: bold; color: green;">
                İndirimli Fiyat: <?php echo number_format($discountedPrice, 2); ?> TL
            </p>
        <?php else: ?>
            <p>İndirim: 0%</p>
            <p style="font-size: 20px; font-weight: bold; color: green;">
                Son Fiyat: <?php echo $food['price']; ?> TL
            </p>
        <?php endif; ?>
        </div>
        <form action="basket.php" method="post">
            <input type="hidden" name="food_id" value="<?php echo $food['id']; ?>">
            <input type="number" name="quantity" value="1" min="1">
            <input type="text" name="note" placeholder="Not (isteğe bağlı)">
            <button type="submit">Sepete Ekle</button>
        </form>
        
</div>

<div id="comments-<?php echo $food['restaurant_id']; ?>" class="comments-popup" style="display: none;">
    <table>
        <tr>
            <th>Kullanıcı</th> 
            <th>Başlık</th>
            <th>Açıklama</th>
            <th>Skor</th>
        </tr>

        <?php 
        $hasComment = false; 
        foreach ($comments as $comment): 
            if ($comment['restaurant_id'] == $food['restaurant_id']): 
                $hasComment = true; 
        ?>
                <tr>
                    <td><?php echo htmlspecialchars($comment['user_name'] . ' ' . $comment['user_surname']); ?></td> <!-- Kullanıcı adı ve soyadı -->
                    <td><?php echo htmlspecialchars($comment['title']); ?></td>
                    <td><?php echo htmlspecialchars($comment['description']); ?></td>
                    <td><?php echo htmlspecialchars($comment['score']); ?>/10</td>
                </tr>
        <?php 
            endif;
        endforeach;

        if (!$hasComment): ?>
            <tr><td colspan="4">Henüz Yorum Yapılmadı!</td></tr>
        <?php endif; ?>
    </table>
</div>


<?php endforeach; ?>

    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const commentLinks = document.querySelectorAll('.show-comments');

    commentLinks.forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault(); 

            const restaurantId = link.getAttribute('data-restaurant-id');
            const commentDiv = document.getElementById('comments-' + restaurantId);

            commentDiv.style.display = commentDiv.style.display === 'none' ? 'block' : 'none';

            if (commentDiv.style.display === 'block') {
                const rect = commentDiv.getBoundingClientRect();
                commentDiv.style.width = rect.width + 'px'; 
                commentDiv.style.height = 'auto'; 
            }
        });
    });
});

</script>



</body>
</html>