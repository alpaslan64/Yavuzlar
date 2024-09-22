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

$editMode = false;
if (isset($_GET['edit_id'])) {
    $editMode = true;
    $restaurantId = $_GET['edit_id'];

    $stmt = $pdo->prepare("SELECT * FROM restaurant WHERE id = ?");
    $stmt->execute([$restaurantId]);
    $restaurantToEdit = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];

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

    if (isset($_POST['restaurant_id']) && !empty($_POST['restaurant_id'])) {
        $restaurantId = $_POST['restaurant_id'];
        $stmt = $pdo->prepare("UPDATE restaurant SET name = ?, description = ?, image_path = ? WHERE id = ?");
        $stmt->execute([$name, $description, $imagePath, $restaurantId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO restaurant (company_id, name, description, image_path, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$_SESSION['company_id'], $name, $description, $imagePath]);
    }

    header('Location: restaurants.php');
    exit;
}

if (isset($_GET['delete_id'])) {
    $restaurantId = $_GET['delete_id'];

    $stmt = $pdo->prepare("DELETE FROM restaurant WHERE id = ?");
    $stmt->execute([$restaurantId]);

    header('Location: restaurants.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/style.css">
    <title>Restoran İşlemleri</title>
</head>
<body>
    <div class="restaurants">
    <div class="user-info">
        <p>Hoşgeldiniz, <?php echo $_SESSION['name'] . " " . $_SESSION['surname']; ?></p>
        <p><?php echo "(". $_SESSION['company_name'] . ")"; ?></p>
        <a href="/index.php">Ana Sayfaya Dön</a><br>
        <a href="restaurants.php">Restoran İşlemleri</a><br>
        <a href="foods.php">Yemek İşlemleri</a><br>
        <a href="orders.php">Sipariş İşlemleri</a><br>
        <a href="/logout.php">Çıkış Yap</a>
    </div>

    <div class="restaurant-form">
        <h1><?php echo $editMode ? "Restoran Düzenle" : "Restoran Ekle"; ?></h1>
        <form action="restaurants.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="restaurant_id" value="<?php echo $editMode ? $restaurantToEdit['id'] : ''; ?>">

            <?php if ($editMode && !empty($restaurantToEdit['image_path'])): ?>
                <input type="hidden" name="existing_image_path" value="<?php echo $restaurantToEdit['image_path']; ?>">
            <?php endif; ?>
            
            <label for="name">Restoran Adı:</label>
            <input type="text" name="name" value="<?php echo $editMode ? $restaurantToEdit['name'] : ''; ?>" required><br>
            
            <label for="description">Açıklama:</label>
            <input type="text" name="description" value="<?php echo $editMode ? $restaurantToEdit['description'] : ''; ?>" required><br>
            
            <label for="image_path">Restoran Görseli:</label>
            <?php if ($editMode && !empty($restaurantToEdit['image_path'])): ?>
                <img src="<?php echo $restaurantToEdit['image_path']; ?>" alt="Mevcut Restoran Görseli" width="100"><br>
            <?php endif; ?>
            <input type="file" name="image_path"><br>
            
            <button type="submit"><?php echo $editMode ? "Güncelle" : "Ekle"; ?></button>
        </form>
    </div>

    <div class="restaurant-list">
    <h2>Mevcut Restoranlar</h2>
    <input type="text" id="search_restaurant" placeholder="Restoran Ara (Adı, Açıklaması, Tarihi)">
    <table>
        <thead>
            <tr>
                <th>Restoran Adı</th>
                <th>Açıklama</th>
                <th>Görsel</th>
                <th>Oluşturulma Tarihi</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($restaurants as $restaurant): ?>
            <tr>
                <td><?php echo $restaurant['name']; ?></td>
                <td><?php echo $restaurant['description']; ?></td>
                <td><img src="<?php echo $restaurant['image_path']; ?>" alt="Restoran Görseli" width="100"></td>
                <td><?php echo $restaurant['created_at']; ?></td>
                <td>
                    <a href="restaurants.php?edit_id=<?php echo $restaurant['id']; ?>">Düzenle</a>
                    <a href="restaurants.php?delete_id=<?php echo $restaurant['id']; ?>">Sil</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
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