<?php
session_start();
if ($_SESSION['role'] !== 'company') {
    header('Location: ../index.php');
    exit;
}

include '../db.php';

$stmt = $pdo->prepare("SELECT * FROM restaurant WHERE company_id = ?");
$stmt->execute([$_SESSION['company_id']]);
$restaurants = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT food.*, restaurant.name AS restaurant_name 
    FROM food 
    JOIN restaurant ON food.restaurant_id = restaurant.id 
    WHERE restaurant.company_id = ?
");
$stmt->execute([$_SESSION['company_id']]);
$foods = $stmt->fetchAll();


$editMode = false;
if (isset($_GET['edit_id'])) {
    $editMode = true;
    $foodId = $_GET['edit_id'];

    $stmt = $pdo->prepare("SELECT * FROM food WHERE id = ?");
    $stmt->execute([$foodId]);
    $foodToEdit = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $restaurantId = $_POST['restaurant_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $discount = $_POST['discount'];

    $imagePath = $_POST['existing_image_path'] ?? null;

    if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] == UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['image_path']['tmp_name'];
        $image_name = $_FILES['image_path']['name'];
        $image_target_dir = "../img/";
        $image_target_file = $image_target_dir . basename($image_name);

        if (move_uploaded_file($image_tmp_name, $image_target_file)) {
            $imagePath = $image_target_file; 
        } else {
            echo "Dosya yüklenirken hata oluştu!";
            exit;
        }
    }

    if (isset($_POST['food_id']) && !empty($_POST['food_id'])) {
        $foodId = $_POST['food_id'];
        $stmt = $pdo->prepare("UPDATE food SET restaurant_id = ?, name = ?, description = ?, image_path = ?, price = ?, discount = ? WHERE id = ?");
        $stmt->execute([$restaurantId, $name, $description, $imagePath, $price, $discount, $foodId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO food (restaurant_id, name, description, image_path, price, discount, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$restaurantId, $name, $description, $imagePath, $price, $discount]);
    }

    header('Location: foods.php');
    exit;
}

if (isset($_GET['delete_id'])) {
    $foodId = $_GET['delete_id'];

    $stmt = $pdo->prepare("DELETE FROM food WHERE id = ?");
    $stmt->execute([$foodId]);

    header('Location: foods.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style.css">
    <title>Yemek İşlemleri</title>
</head>
<body>
    <div class="foods">
    <div class="user-info">
        <p>Hoşgeldiniz, <?php echo $_SESSION['name'] . $_SESSION['surname']; ?></p>
        <p><?php echo "(". $_SESSION['company_name'] . ")"; ?></p>
        <a href="/index.php">Ana Sayfaya Dön</a><br>
        <a href="restaurants.php">Restoran İşlemleri</a><br>
        <a href="foods.php">Yemek İşlemleri</a><br>
        <a href="orders.php">Sipariş İşlemleri</a><br>
        <a href="/logout.php">Çıkış Yap</a>
    </div>

        <div class="food-form">
            <h1>Yemek Ekleme</h1>
            <form action="foods.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="food_id" value="<?php echo $editMode ? $foodToEdit['id'] : ''; ?>">

                <?php if ($editMode && !empty($foodToEdit['image_path'])): ?>
                    <input type="hidden" name="existing_image_path" value="<?php echo $foodToEdit['image_path']; ?>">
                    <img src="<?php echo $foodToEdit['image_path']; ?>" alt="Mevcut Yemek Görseli" width="100"><br>
                <?php endif; ?>

                <label for="restaurant_id">Restoran Seçin:</label>
                <select name="restaurant_id" required>
                    <option value="">Seçin</option>
                    <?php foreach ($restaurants as $restaurant): ?>
                        <option value="<?php echo $restaurant['id']; ?>" <?php echo ($editMode && $restaurant['id'] == $foodToEdit['restaurant_id']) ? 'selected' : ''; ?>>
                            <?php echo $restaurant['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>

                <label for="name">Yemek Adı:</label>
                <input type="text" name="name" value="<?php echo $editMode ? $foodToEdit['name'] : ''; ?>" required><br>

                <label for="description">Açıklama:</label>
                <input type="text" name="description" value="<?php echo $editMode ? $foodToEdit['description'] : ''; ?>" required><br>

                <label for="price">Fiyat:</label>
                <input type="number" name="price" value="<?php echo $editMode ? $foodToEdit['price'] : ''; ?>"required><br>

                <label for="discount">İndirim (%):</label>
                <input type="number" name="discount" value="<?php echo $editMode ? $foodToEdit['discount'] : ''; ?>0"min="0" max="100"><br>

                <label for="image_path">Yemek Görseli:</label>
                <input type="file" name="image_path"><br>

                <button type="submit"><?php echo $editMode ? "Güncelle" : "Ekle"; ?></button>
            </form>
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
                    <a href="foods.php?delete_id=<?php echo $food['id']; ?>">Sil</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

    </div>

    <script>
    document.querySelector('#search_food').addEventListener('input', function () {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const restaurant = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
            const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const description = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const price = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
            const discount = row.querySelector('td:nth-child(5)').textContent.toLowerCase();
            const date = row.querySelector('td:nth-child(7)').textContent.toLowerCase();


            if (restaurant.includes(searchValue) || name.includes(searchValue) || description.includes(searchValue) || price.includes(searchValue) || discount.includes(searchValue) || date.includes(searchValue)) {
                row.style.display = '';  
            } else {
                row.style.display = 'none';  
            }
        });
    });
</script>
    
</body>
</html>