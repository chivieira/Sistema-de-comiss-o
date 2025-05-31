<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $res = $pdo->query("SELECT * FROM Funcionario")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($res);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("INSERT INTO Funcionario (nome, matricula, ativo) VALUES (?, ?, ?)");
    $stmt->execute([$data['nome'], $data['matricula'], $data['ativo'] ?? 1]);
    echo json_encode(['status' => 'success']);
}