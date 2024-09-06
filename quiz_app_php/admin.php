<?php
session_start();
include('db.php');


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$update_id = null;
$question_text = '';
$option_a = '';
$option_b = '';
$option_c = '';
$option_d = '';
$correct_option = '';

if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM questions WHERE id = ?');
    $stmt->execute([$_GET['edit']]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($question) {
        $update_id = $question['id'];
        $question_text = $question['question_text'];
        $option_a = $question['option_a'];
        $option_b = $question['option_b'];
        $option_c = $question['option_c'];
        $option_d = $question['option_d'];
        $correct_option = $question['correct_option'];
    }
}

if (isset($_GET['delete'])) {
    $stmt = $db->prepare('DELETE FROM questions WHERE id = ?');
    $stmt->execute([$_GET['delete']]);
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($update_id) {
        $stmt = $db->prepare('UPDATE questions SET question_text = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_option = ? WHERE id = ?');
        $stmt->execute([
            $_POST['question_text'],
            $_POST['option_a'],
            $_POST['option_b'],
            $_POST['option_c'],
            $_POST['option_d'],
            $_POST['correct_option'],
            $update_id
        ]);
    } else {
        $stmt = $db->prepare('INSERT INTO questions (question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $_POST['question_text'],
            $_POST['option_a'],
            $_POST['option_b'],
            $_POST['option_c'],
            $_POST['option_d'],
            $_POST['correct_option']
        ]);
    }
    header('Location: admin.php');
    exit;
}

$questions = $db->query('SELECT * FROM questions')->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Yönetim Paneli</title>
</head>
<body>
    <div class="container">
        <h1>Soru Paneli</h1>
        
        <form method="post">
            <label for="question-text">Soru</label>
            <textarea name="question_text" id="question-text" rows="4" placeholder="Soru" required><?php echo htmlspecialchars($question_text); ?></textarea>
            
            <label for="option-a">A Şıkkı</label>
            <input type="text" name="option_a" placeholder="A Şıkkı" value="<?php echo htmlspecialchars($option_a); ?>" required>
            
            <label for="option-b">B Şıkkı</label>
            <input type="text" name="option_b" placeholder="B Şıkkı" value="<?php echo htmlspecialchars($option_b); ?>" required>
            
            <label for="option-c">C Şıkkı</label>
            <input type="text" name="option_c" placeholder="C Şıkkı" value="<?php echo htmlspecialchars($option_c); ?>" required>
            
            <label for="option-d">D Şıkkı</label>
            <input type="text" name="option_d" placeholder="D Şıkkı" value="<?php echo htmlspecialchars($option_d); ?>" required>
            
            <label for="correct-option">Doğru Şık</label>
            <select name="correct_option" id="correct-option" required>
                <option value="a" <?php echo $correct_option == 'a' ? 'selected' : ''; ?>>A</option>
                <option value="b" <?php echo $correct_option == 'b' ? 'selected' : ''; ?>>B</option>
                <option value="c" <?php echo $correct_option == 'c' ? 'selected' : ''; ?>>C</option>
                <option value="d" <?php echo $correct_option == 'd' ? 'selected' : ''; ?>>D</option>
            </select>
            
            <button type="submit"><?php echo $update_id ? 'Soruyu Güncelle' : 'Soru Ekle'; ?></button>
            <br><button onclick="location.href='profile.php'" type="button">Profile Dön</button>
        </form><br><br>
        <h3>Kayıtlı Sorular</h3>
        <ul id="question-list">
            <?php foreach ($questions as $question): ?>
                <li>
                    <?php echo htmlspecialchars($question['question_text']); ?>
                    <button onclick="location.href='admin.php?edit=<?php echo $question['id']; ?>'">Düzenle</button>
                    <button onclick="if(confirm('Bu soruyu silmek istediğinizden emin misiniz?')) location.href='admin.php?delete=<?php echo $question['id']; ?>'">Sil</button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
