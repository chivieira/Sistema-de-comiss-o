<?php
$pdo = new PDO('mysql:host=localhost;dbname=south_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
header('Content-Type: application/json; charset=utf-8');
?>