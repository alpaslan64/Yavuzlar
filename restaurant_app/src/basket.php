<?php
session_start();

include 'db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer'){
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id']; 

$query = "SELECT COUNT(*) AS total_items FROM basket WHERE user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_items = $result['total_items']; 


$coupon_code = isset($_POST['coupon_code']) ? $_POST['coupon_code'] : '';
$coupon_discount = 0;

if (!empty($coupon_code)) {
    $query = "SELECT discount FROM cupon WHERE name = ? AND restaurant_id IN (SELECT DISTINCT restaurant_id FROM food WHERE id IN (SELECT food_id FROM basket WHERE user_id = ?))";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$coupon_code, $user_id]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($coupon) {
        $coupon_discount = $coupon['discount'];
    }
}

if (isset($_POST['remove_coupon'])) {
    $coupon_code = '';
    $coupon_discount = 0;
}



if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['basket_id'])) {
    $basket_id = $_GET['basket_id'];

    $delete_query = "DELETE FROM basket WHERE id = :basket_id";
    $delete_stmt = $pdo->prepare($delete_query);
    $delete_stmt->execute(['basket_id' => $basket_id]);

    header('Location: basket.php');
    exit();
}


$query = "SELECT basket.*, 
       food.name AS food_name, 
       food.price AS food_price, 
       food.discount AS food_discount,
       restaurant.name AS restaurant_name 
FROM basket
JOIN food ON basket.food_id = food.id
JOIN restaurant ON food.restaurant_id = restaurant.id
WHERE basket.user_id = :user_id";

$stmt = $pdo->prepare($query);
$stmt->execute(['user_id' => $user_id]);
$baskets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_price = 0;

foreach ($baskets as $basket): 
    $price = $basket['food_price'];  
    $discount = $basket['food_discount'];  
    $discounted_price = $price - ($price * ($discount / 100));  
    $subtotal = $discounted_price * $basket['quantity'];  
    $total_price += $subtotal;
endforeach;


if (isset($_POST['apply_coupon'])) {
    $coupon_code = $_POST['coupon_code'];

    $query = "SELECT discount FROM cupon WHERE `name` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$coupon_code]);
    $coupon = $stmt->fetch();

    if ($coupon) {
        $coupon_discount = $coupon['discount'];
        $_SESSION['coupon_discount'] = $coupon_discount; 
        $cupon_success = "Kupon İndirimi: $coupon_discount%";
    } else {
        $cupon_error = "Geçersiz kupon kodu.";
        $_SESSION['coupon_discount'] = 0;
    }
}

$coupon_discount = isset($_SESSION['coupon_discount']) ? $_SESSION['coupon_discount'] : 0;

$final_total_price = $total_price; 

if ($coupon_discount > 0) {
    $final_total_price = $total_price - ($total_price * ($coupon_discount / 100)); 
}


////////////////////////////////////////////////////////////////////////////////

if (isset($_POST['place_order'])) {
    $query = "INSERT INTO `order` (user_id, order_status, total_price, created_at) VALUES (?, 'Alındı', ?, NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $final_total_price]); 

    $order_id = $pdo->lastInsertId(); 

    foreach ($baskets as $basket) {
        $query = "INSERT INTO order_items (food_id, order_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $price = $basket['food_price'] - ($basket['food_price'] * ($basket['food_discount'] / 100));
        $stmt->execute([$basket['food_id'], $order_id, $basket['quantity'], $price]);
    }

    $new_balance = $_SESSION['balance'] - $final_total_price;
    if ($new_balance >= 0) {
        $update_balance_query = "UPDATE users SET balance = ? WHERE id = ?";
        $update_stmt = $pdo->prepare($update_balance_query);
        $update_stmt->execute([$new_balance, $user_id]);

        $_SESSION['balance'] = $new_balance;

        $delete_query = "DELETE FROM basket WHERE user_id = ?";
        $delete_stmt = $pdo->prepare($delete_query);
        $delete_stmt->execute([$user_id]);

        unset($_SESSION['coupon_discount']);

        header("Location: orders.php");
        exit();
    } else {
        $balance_error = "Yetersiz bakiye. Lütfen bakiyenizi kontrol edin.";
    }
}


if (!empty($coupon_code) && $coupon_discount > 0) {
    $cupon_success = "Kupon: $coupon_code";
} elseif (!empty($coupon_code)) {
    $cupon_error = "Geçersiz kupon kodu: $coupon_code";
}

if (isset($_POST['clear_basket'])) {
    $clear_query = "DELETE FROM basket WHERE user_id = ?";
    $clear_stmt = $pdo->prepare($clear_query);
    $clear_stmt->execute([$user_id]);

    header('Location: basket.php');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'save') {
    if (isset($_GET['basket_id'])) {
        $basket_id = $_GET['basket_id'];
        $new_quantity = $_POST['quantity']; 
        $new_note = $_POST['note']; 

        $query = "UPDATE basket SET quantity = ?, note = ? WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$new_quantity, $new_note, $basket_id]);

        header("Location: basket.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sepetim</title>
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

<div class="basket-list">
    <h1>Sepetim</h1>

    <?php if (empty($baskets)): ?>
        <p>Sepetinizde ürün bulunmamaktadır.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Restoran</th>
                    <th>Yemek</th>
                    <th>Fiyat</th>
                    <th>Adet</th>
                    <th>İndirim</th>
                    <th>İndirimli Fiyat</th>
                    <th>Net</th>
                    <th>Not</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($baskets as $basket): 
                    $price = $basket['food_price'];
                    $discount = $basket['food_discount'];
                    $discounted_price = $price - ($price * ($discount / 100)); 
                    $subtotal = $discounted_price * $basket['quantity']; 
                ?>
                    <tr>
                        <form action="basket.php?action=save&basket_id=<?php echo $basket['id']; ?>" method="POST">
                            <td><?php echo $basket['restaurant_name']; ?></td>
                            <td><?php echo $basket['food_name']; ?></td>
                            <td><?php echo number_format($price, 2); ?> TL</td>
                            <td><input type="text" name="quantity" value="<?php echo $basket['quantity']; ?>"></td>
                            <td><?php echo $discount; ?>%</td>
                            <td><?php echo number_format($discounted_price, 2); ?> TL</td>
                            <td class="td-subtotal"><?php echo number_format($subtotal, 2); ?> TL</td>
                            <td><input type="text" name="note" value="<?php echo $basket['note']; ?>"></td>
                            <td>
                                <a href="basket.php?action=delete&basket_id=<?php echo $basket['id']; ?>">Sil</a>
                                <button type="submit">Kaydet</button>
                            </td>
                        </form>
                    </tr>

                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    

    <div class="cupons">
        <form method="POST">
            <input type="text" name="coupon_code" placeholder="Kupon Kodu" required>
            <button type="submit" name="apply_coupon">Kupon Uygula</button>
        </form>
        
    </div>
    <form method="POST" action="">
    <div class="total">
    
    <?php if (isset($cupon_success)): ?>
    <p class="cupon-ps"><?php echo $cupon_success; ?></p>
<?php elseif (isset($cupon_error)): ?>
    <p class="cupon-pe"><?php echo $cupon_error; ?></p>
<?php endif; ?>
<br>
<h3><span class="td-subtotal">Toplam Tutar: <?php echo number_format($final_total_price, 2); ?> TL</span></h3>

    <button type="submit" name="place_order" class="button">Sipariş Ver</button>
</form>
<form method="POST" action="">
    <button type="submit" name="clear_basket" class="button">Sepeti Temizle</button>
</form>

</div>        
    
</div>
    
</body>
</html>
