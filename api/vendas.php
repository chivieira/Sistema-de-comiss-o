<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $res = $pdo->query("SELECT v.*, f.nome 
                        FROM Valor v 
                        JOIN Funcionario f ON v.id_funcionario = f.id_funcionario")
               ->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($res);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("INSERT INTO Valor (id_funcionario, total_vendas, mes, ano, percentual_comissao)
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['id_funcionario'],
        $data['total_vendas'],
        $data['mes'],
        $data['ano'],
        $data['percentual_comissao']
    ]);
    echo json_encode(['status' => 'success']);
}