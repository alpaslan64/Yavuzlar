<?php
session_start();
include '../db.php';

if ($_SESSION['role'] !== 'company') {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$company_id = $_SESSION['company_id']; 

$query = "
    SELECT o.id AS order_id, o.total_price, o.order_status, r.name AS restaurant_name
    FROM `order` o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN food f ON oi.food_id = f.id
    JOIN restaurant r ON f.restaurant_id = r.id
    WHERE r.company_id = :company_id
    GROUP BY o.id, r.name, o.total_price, o.order_status
    ORDER BY o.id ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute([':company_id' => $company_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$completed_orders = [];

if (isset($_POST['update_order'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['order_status'];

    $update_query = "UPDATE `order` SET order_status = ? WHERE id = ?";
    $update_stmt = $pdo->prepare($update_query);
    $update_stmt->execute([$new_status, $order_id]);

    header('Location: orders.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siparişler</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>

<div class="user-info">
        <p>Hoşgeldiniz, <?php echo $_SESSION['name'] . $_SESSION['surname']; ?></p>
        <p><?php echo "(". $_SESSION['company_name'] . ")"; ?></p>
        <a href="/index.php">Ana Sayfaya Dön</a><br>
        <a href="restaurants.php">Restoran İşlemleri</a><br>
        <a href="foods.php">Yemek İşlemleri</a><br>
        <a href="orders.php">Sipariş İşlemleri</a><br>
        <a href="/logout.php">Çıkış Yap</a>
    </div>

<div class="orders-list">
    <h1>Siparişler</h1>
    <input type="text" id="search_order" placeholder="Sipariş Ara (Sipariş numarası, Restoran, Toplam fiyat..)" />

<div class="completed-orders-list">
    <h2>Aktif Siparişler</h2>

    <?php if (empty($orders)): ?>
        <p>Henüz sipariş yok.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order" data-price="<?php echo $order['total_price']; ?>">
                <h3>Sipariş Numarası: <?php echo $order['order_id']; ?></h3>
                <p>Restoran: <?php echo $order['restaurant_name']; ?></p>
                <p style="color: green; font-weight: bold;">Toplam Fiyat: <?php echo $order['total_price']; ?> TL</p>
                
                <form method="post" action="orders.php">
                    <label for="order_status">Durum:</label>
                    <select name="order_status">
                        <option value="Hazırlanıyor" <?php echo $order['order_status'] == 'Hazırlanıyor' ? 'selected' : '' ?>>Hazırlanıyor</option>
                        <option value="Yola Çıktı" <?php echo $order['order_status'] == 'Yola Çıktı' ? 'selected' : '' ?>>Yola Çıktı</option>
                        <option value="Teslim Edildi" <?php echo $order['order_status'] == 'Teslim Edildi' ? 'selected' : '' ?>>Teslim Edildi</option>
                    </select>
                    <input type="hidden" name="order_id" value="<?php echo $order['order_id'] ?>">
                    <button type="submit" name="update_order">Güncelle</button>
                </form>

                <div class="food-list">
                    <h3>Yemekler</h3>
                    <?php
                    $foodQuery = "
                        SELECT f.name AS food_name, f.price, oi.quantity 
                        FROM order_items oi
                        JOIN food f ON oi.food_id = f.id
                        JOIN restaurant r ON f.restaurant_id = r.id
                        WHERE oi.order_id = ? AND r.company_id = ?";
                    $foodStmt = $pdo->prepare($foodQuery);
                    $foodStmt->execute([$order['order_id'], $company_id]);
                    $foods = $foodStmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <table>
                        <tr>
                            <th>Yemek Adı</th>
                            <th>Fiyat</th>
                            <th>Adet</th>
                        </tr>
                        <?php if ($foods): ?>
                            <?php foreach ($foods as $food): ?>
                                <tr>
                                    <td><?php echo $food['food_name']; ?></td>
                                    <td><?php echo $food['price']; ?> TL</td>
                                    <td><?php echo $food['quantity']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">Bu siparişe ait yemek bulunmamaktadır.</td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <?php
            if ($order['order_status'] === 'Teslim Edildi') {
                $completed_orders[] = $order;
            }
            ?>

        <?php endforeach; ?>
    <?php endif; ?>
</div>
    <div class="completed-orders-list">
    <h2>Geçmiş Siparişler</h2>
    <?php if (empty($completed_orders)): ?>
        <p>Henüz geçmiş sipariş yok.</p>
    <?php else: ?>
        <?php foreach ($completed_orders as $completed_order): ?>
            <div class="order" data-price="<?php echo $completed_order['total_price']; ?>">
                <h2>Sipariş Numarası: <?php echo $completed_order['order_id']; ?></h2>
                <p>Restoran: <?php echo $completed_order['restaurant_name']; ?></p>
                <p>Toplam Fiyat: <?php echo $completed_order['total_price']; ?> TL</p>

                <div class="food-list">
                    <h3>Yemekler</h3>
                    <?php
                    $completedFoodQuery = "
                        SELECT f.name AS food_name, f.price, oi.quantity 
                        FROM order_items oi
                        JOIN food f ON oi.food_id = f.id
                        WHERE oi.order_id = ?";
                    $completedFoodStmt = $pdo->prepare($completedFoodQuery);
                    $completedFoodStmt->execute([$completed_order['order_id']]);
                    $completed_foods = $completedFoodStmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <table>
                        <tr>
                            <th>Yemek Adı</th>
                            <th>Fiyat</th>
                            <th>Adet</th>
                        </tr>
                        <?php if ($completed_foods): ?>
                            <?php foreach ($completed_foods as $food): ?>
                                <tr>
                                    <td><?php echo $food['food_name']; ?></td>
                                    <td><?php echo $food['price']; ?> TL</td>
                                    <td><?php echo $food['quantity']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">Bu siparişe ait yemek bulunmamaktadır.</td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</div>

<script>
document.querySelector('#search_order').addEventListener('input', function () {
    const searchValue = this.value.toLowerCase();
    const orders = document.querySelectorAll('.order');

    orders.forEach(order => {
        const orderNumber = order.querySelector('h3').textContent.toLowerCase();
        const restaurantName = order.querySelector('p:nth-child(2)').textContent.toLowerCase();
        const price = order.getAttribute('data-price');
        
        const foodNames = order.querySelectorAll('.food-list tr td:first-child');
        let foodMatch = false;

        foodNames.forEach(foodName => {
            if (foodName.textContent.toLowerCase().includes(searchValue)) {
                foodMatch = true; 
            }
        });

        if (orderNumber.includes(searchValue) || restaurantName.includes(searchValue) || price.includes(searchValue) || foodMatch) {
            order.style.display = '';  
        } else {
            order.style.display = 'none';  
        }
    });
});


</script>


</body>
</html>