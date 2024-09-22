<?php
session_start();

include 'db.php';


if($_SESSION['role'] !== 'customer'){
    header('Location: index.php');
}
$user_id = $_SESSION['user_id'];

$total_items = 0; 
if (isset($_SESSION['user_id'])) {
    $query = "SELECT COUNT(*) AS total_items FROM basket WHERE user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_items = $result['total_items']; 
}

$query = "
    SELECT o.id AS order_id, o.total_price, o.order_status, o.created_at,
           oi.food_id, oi.quantity, oi.price,
           f.name AS food_name, r.name AS restaurant_name, r.id AS restaurant_id
    FROM `order` o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN food f ON oi.food_id = f.id
    JOIN restaurant r ON f.restaurant_id = r.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$active_orders = [];
$past_orders = [];

foreach ($orders as $order) {
    if ($order['order_status'] === 'Teslim Edildi') {
        $past_orders[] = $order;
    } else {
        $active_orders[] = $order;
    }
}

if (isset($_POST['submit_comment'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $score = $_POST['score'];
    $restaurant_id = $_POST['restaurant_id'];
    $user_id = $_SESSION['user_id'];

    $insert_query = "INSERT INTO comments (user_id, restaurant_id, title, description, score, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $insert_stmt = $pdo->prepare($insert_query);
    $insert_stmt->execute([$user_id, $restaurant_id, $title, $description, $score]);

    header('Location: orders.php');
    exit();
}


$query_comments = "
    SELECT c.id, c.title, c.description, c.score, c.created_at, r.name AS restaurant_name
    FROM comments c
    JOIN restaurant r ON c.restaurant_id = r.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
";
$stmt_comments = $pdo->prepare($query_comments);
$stmt_comments->execute([$user_id]);
$comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);


if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['comment_id'])) {
    $commentId = $_GET['comment_id'];

    $delete_query = "DELETE FROM comments WHERE id = ?";
    $delete_stmt = $pdo->prepare($delete_query);
    
    if ($delete_stmt->execute([$commentId])) {
        header('Location: orders.php');
    }
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siparişlerim</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="user-info">
    <p>Hoşgeldiniz, <?php echo $_SESSION['name']; ?></p>
    <p>Hesap Bakiyesi: <?php echo $_SESSION['balance']; ?> TL</p>
    <a href="index.php">Ana Sayfaya Dön</a><br>
    <a href="customer/customer_profile_update.php">Profili Güncelle</a><br>
    <a href="customer/customer_password_update.php">Şifreni Güncelle</a><br>
    <a href="customer/customer_load_money.php">Bakiye Yükle</a><br>
    <a href="basket.php">Sepetim (<?php echo $total_items; ?>)</a><br>
    <a href="orders.php">Tüm Siparişlerim</a><br>
    <a href="logout.php">Çıkış Yap</a>
</div>


<div class="order">
<h1>Aktif Siparişlerim</h1>
<?php if (!empty($active_orders)): ?>
    <table>
        <thead>
            <tr>
                <th>Sipariş No</th>
                <th>Restoran</th>
                <th>Yemek Adı</th>
                <th>Adet</th>
                <th>Fiyat</th>
                <th>Durum</th>
                <th>Sipariş Tarihi</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($active_orders as $order): ?>
            <tr>
                <td><?php echo $order['order_id'] ?></td>
                <td><?php echo $order['restaurant_name'] ?></td>
                <td><?php echo $order['food_name'] ?></td>
                <td><?php echo $order['quantity'] ?></td>
                <td><?php echo $order['price'] ?> TL</td>
                <td><?php echo $order['order_status'] ?></td>
                <td><?php echo $order['created_at']; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Aktif siparişiniz bulunmamaktadır.</p>
<?php endif; ?>

<h1>Geçmiş Siparişlerim</h1>
<?php if (!empty($past_orders)): ?>
    <table>
        <thead>
            <tr>
                <th>Sipariş No</th>
                <th>Restoran</th>
                <th>Yemek Adı</th>
                <th>Adet</th>
                <th>Fiyat</th>
                <th>Durum</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($past_orders as $order): ?>
            <tr>
                <td><?php echo $order['order_id'] ?></td>
                <td><?php echo $order['restaurant_name'] ?></td>
                <td><?php echo $order['food_name'] ?></td>
                <td><?php echo $order['quantity'] ?></td>
                <td><?php echo $order['price'] ?> TL</td>
                <td><?php echo $order['order_status'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <form method="post" action="orders.php">
    <h3>Yorum Yap</h3>
    
    <label for="restaurant_id">Restoran Seç:</label>
    <select name="restaurant_id" required>
       <option value="" disabled selected>Seçiniz</option>

       <?php 
       $displayed_restaurants = [];

       foreach ($past_orders as $order): 
           if (!in_array($order['restaurant_id'], $displayed_restaurants)): 
               $displayed_restaurants[] = $order['restaurant_id']; 
       ?>
           <option value="<?php echo $order['restaurant_id'] ?>"><?php echo $order['restaurant_name'] ?></option>
       <?php 
           endif; 
       endforeach; 
       ?>
    </select>

    <label for="title">Başlık:</label>
    <input type="text" name="title" required>
    
    <label for="description">Açıklama:</label>
    <input type="text" name="description" required>
    
    <label for="score">Puan:</label>
    <input type="number" name="score" min="1" max="10" required>
    
    <button type="submit" name="submit_comment">Yorum Yap</button>
</form>
<?php else: ?>
    <p>Geçmiş siparişiniz bulunmamaktadır.</p>
<?php endif; ?>

<h1>Yorumlarım</h1>
<?php if (!empty($comments)): ?>
    <table>
        <thead>
            <tr>
                <th>Restoran</th>
                <th>Başlık</th>
                <th>Açıklama</th>
                <th>Puan</th>
                <th>Tarih</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($comments as $comment): ?>
            <tr>
                <td><?php echo $comment['restaurant_name']; ?></td>
                <td><?php echo $comment['title']; ?></td>
                <td><?php echo $comment['description']; ?></td>
                <td><?php echo $comment['score']; ?></td>
                <td><?php echo $comment['created_at']; ?></td>
                <td>
                    <a href="orders.php?action=delete&comment_id=<?php echo $comment['id']; ?>">Sil</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Henüz yorumunuz yok.</p>
<?php endif; ?>



</div>
</body>
</html>
