<?php
$dsn = 'mysql:host=db;dbname=restaurant_app'; 
$username = 'root'; 
$password = 'root'; 
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,   
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,  
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    echo "Bağlantı hatası: " . $e->getMessage();
}
?>
