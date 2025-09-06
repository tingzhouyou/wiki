<?php
$host = 'localhost';
$dbname = 'index';
$username = 'index';
$password = 'asdj1004488';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "连接失败: " . $e->getMessage();
}
?>