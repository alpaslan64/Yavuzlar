<?php
session_start();
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

include('db.php');
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$score_query = $db->prepare('SELECT SUM(score) FROM submissions WHERE user_id = ?');
$score_query->execute([$user_id]);
$score = $score_query->fetchColumn();
$score = $score ? $score : 0; 

$users_query = $db->query('SELECT u.username, COALESCE(SUM(s.score), 0) as total_score 
                           FROM users u
                           LEFT JOIN submissions s ON u.id = s.user_id 
                           WHERE u.role = "student"
                           GROUP BY u.id 
                           ORDER BY total_score DESC');
$users = $users_query->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Profil</title>
</head>
<body>
    <div class="container">
        <div class="profile-info">
        <p>Kullanıcı Adı: <?php echo htmlspecialchars($username); ?></p>
        <p>Profil: <?php echo htmlspecialchars($role); ?></p>
        <?php if ($_SESSION['role'] !== 'admin'): ?>
        <p>Skor: <?php echo htmlspecialchars($score);?></p>
        <?php endif; ?>
        <?php if ($gun_turkce === 'Cuma'):?>
        <p>Tarih: <?php echo htmlspecialchars($tarih_turkce);?><br>       
        <?php echo htmlspecialchars($mesaj); ?>
        <?php endif?>
        </div>


        <div class="profile">
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <button onclick="location.href='user_management.php'">Kullanıcı Paneline Git</button><br>
            <button onclick="location.href='admin.php'">Soru Paneline Git</button>
            <!--<br><button onclick="alert('Adminler soru çözemez :)')">Soruları Çöz</button>-->
        <?php else: ?>
            <!--<button onclick="alert('Öğrenciler buna erişemez :)');">Yönetim Paneline Git</button>-->
            <button onclick="location.href='quiz.php'">Soruları Çöz</button><br>
            <?php endif; ?>
        <br><button onclick="location.href='logout.php'">Çıkış Yap</button>
    </div>
    </div>

    <div class="score-list">
        <table>
            <h2>Scoreboard</h2>
            <thead>
                <tr>
                    <th>Kullanıcı Adı</th>
                    <th>Skor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['total_score']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
</body>
</html>
