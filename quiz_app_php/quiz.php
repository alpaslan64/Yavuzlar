<?php
session_start();
include('db.php');

if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$score_stmt = $db->prepare('SELECT SUM(score) as total_score FROM submissions WHERE user_id = ?');
$score_stmt->execute([$user_id]);
$score = $score_stmt->fetch(PDO::FETCH_ASSOC)['total_score'];
$score = $score ? $score : 0;  

$submitted_questions = $db->prepare('SELECT question_id FROM submissions WHERE user_id = ?');
$submitted_questions->execute([$user_id]);
$submitted_ids = $submitted_questions->fetchAll(PDO::FETCH_COLUMN);

$question_placeholder = '0'; 
$submitted_ids_placeholder = !empty($submitted_ids) ? implode(',', $submitted_ids) : $question_placeholder;

$question = $db->prepare("SELECT * FROM questions WHERE id NOT IN ($submitted_ids_placeholder) LIMIT 1");
$question->execute();
$question = $question->fetch(PDO::FETCH_ASSOC);

$gunler = array(
    'Monday' => 'Pazartesi',
    'Tuesday' => 'Salı',
    'Wednesday' => 'Çarşamba',
    'Thursday' => 'Perşembe',
    'Friday' => 'Cuma',
    'Saturday' => 'Cumartesi',
    'Sunday' => 'Pazar'
);

$aylar = array(
    'January'=> 'Ocak',
    'February'=> 'Şubat',
    'March'=> 'Mart',
    'April'=> 'Nisan',
    'May'=> 'Mayıs',
    'June'=> 'Haziran',
    'July'=> 'Temmuz',
    'August'=> 'Ağustos',
    'September'=> 'Eylül',
    'October'=> 'Ekim',
    'November'=> 'Kasım',
    'December'=> 'Aralık'
);

$gun_ingilizce = date('l'); 
$ay_ingilizce = date('F');  

$gun_turkce = $gunler[$gun_ingilizce]; 
$ay_turkce = $aylar[$ay_ingilizce]; 


$mesaj = "Hayırlı Cumalar :)";
$tarih_turkce = date('j') . ' ' . $ay_turkce . ' ' . date('Y') . ' - ' . $gun_turkce;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Quiz</title>
</head>
<body>
    <div class="container">
        <div class="profile-info">
            <p>Kullanıcı Adı: <?php echo htmlspecialchars($username); ?></p>
            <p>Profil: <?php echo htmlspecialchars($role); ?></p>
            <p>Skor: <?php echo htmlspecialchars($score); ?></p>
            <?php if ($gun_turkce === 'Cuma'):?>
            <p>Tarih: <?php echo htmlspecialchars($tarih_turkce);?><br>       
            <?php echo htmlspecialchars($mesaj); ?>
            <?php endif?>
        </div>
        <br><br><br><br><br><br><br><br><br><br>
        <h1>Quiz</h1>
        <?php if (!$question): ?>
            <div class='container'>
                <h1>Çözebileceğiniz başka soru kalmadı.</h1>
                <button onclick="location.href='profile.php'">Profile Dön</button>
            </div>
        <?php else: ?>
            <div id="question-text-display">
                <p><?php echo htmlspecialchars($question['question_text']); ?></p>
            </div>
            <form method="post" id="quiz-form">
                <ul id="options-list">
                    <li>
                        <label>
                            <input type="radio" name="selected_option" value="a" required>
                            <?php echo htmlspecialchars($question['option_a']); ?>
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="selected_option" value="b" required>
                            <?php echo htmlspecialchars($question['option_b']); ?>
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="selected_option" value="c" required>
                            <?php echo htmlspecialchars($question['option_c']); ?>
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="radio" name="selected_option" value="d" required>
                            <?php echo htmlspecialchars($question['option_d']); ?>
                        </label>
                    </li>
                </ul>
                <button type="submit">Cevabı Gönder</button>
            </form>
            <br>
            <button onclick="location.href='profile.php'">Profile Dön</button>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($question)) {
        header('Location: quiz.php');
        exit;
    }

    $selected_option = $_POST['selected_option'];
    $correct = $selected_option === $question['correct_option'] ? 1 : 0;
    $score = $correct ? 10 : 0;
    
    $stmt = $db->prepare('INSERT INTO submissions (user_id, question_id, score) VALUES (?, ?, ?)');
    $stmt->execute([$user_id, $question['id'], $score]);

    header('Location: quiz.php');
    exit;
}
?>
