<?php
// Ativa erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pdo = new PDO('mysql:host=localhost;dbname=south_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$acao = $_GET['acao'] ?? '';
$id = $_GET['id'] ?? '';
$aba = $_GET['aba'] ?? 'vendas';
$tipo_relatorio = $_GET['rel'] ?? '1';
$formato_export = $_GET['export'] ?? '';

// Exportação de dados (relatório 1)
if ($aba === 'relatorios' && $formato_export) {
    $sql = "SELECT f.nome, SUM(v.total_vendas) AS total_vendas
            FROM valor v
            JOIN funcionario f ON v.id_funcionario = f.id_funcionario
            GROUP BY f.nome";
    $dados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    if ($formato_export === 'excel') {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=relatorio_vendas.xls");
        echo "Vendedor\tTotal de Vendas (R$)\n";
        foreach ($dados as $linha) {
            echo $linha['nome'] . "\t" . number_format($linha['total_vendas'], 2, ',', '.') . "\n";
        }
        exit;
    } elseif ($formato_export === 'csv') {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=relatorio_vendas.csv");
        $saida = fopen('php://output', 'w');
        fputcsv($saida, ['Vendedor', 'Total de Vendas (R$)'], ';');
        foreach ($dados as $linha) {
            fputcsv($saida, [$linha['nome'], number_format($linha['total_vendas'], 2, ',', '.')], ';');
        }
        fclose($saida);
        exit;
    }
}

// CRUDs
if ($acao === 'salvar_vendedor') {
    $stmt = $pdo->prepare("INSERT INTO Funcionario (nome, matricula, ativo) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['nome'], $_POST['matricula'], $_POST['ativo'] ?? 0]);
    header("Location: index.php?aba=vendedores");
    exit;
}
if ($acao === 'salvar_venda') {
    $stmt = $pdo->prepare("INSERT INTO Valor (id_funcionario, total_vendas, mes, ano, percentual_comissao)
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['id_funcionario'], $_POST['total_vendas'], $_POST['mes'], $_POST['ano'], $_POST['percentual_comissao']]);
    header("Location: index.php?aba=vendas");
    exit;
}
if ($acao === 'excluir_venda' && $id) {
    $stmt = $pdo->prepare("DELETE FROM Valor WHERE id_valor = ?");
    $stmt->execute([$id]);
    header("Location: index.php?aba=vendas");
    exit;
}

$vendedores = $pdo->query("SELECT * FROM Funcionario WHERE ativo = 1")->fetchAll();
$vendas = $pdo->query("SELECT v.*, f.nome FROM Valor v JOIN Funcionario f ON v.id_funcionario = f.id_funcionario")->fetchAll();
$relatorio_vendedor = $pdo->query("SELECT f.nome, SUM(v.total_vendas * v.percentual_comissao / 100) AS total_comissao FROM Valor v JOIN Funcionario f ON v.id_funcionario = f.id_funcionario GROUP BY f.nome")->fetchAll();
$relatorio_mes = $pdo->query("SELECT mes, ano, SUM(total_vendas) AS total_mes FROM Valor GROUP BY ano, mes ORDER BY ano, mes")->fetchAll();
$relatorio_participacao = $pdo->query("SELECT f.nome, SUM(v.total_vendas) AS total_vendas FROM Valor v JOIN Funcionario f ON v.id_funcionario = f.id_funcionario GROUP BY f.nome")->fetchAll();
$total_geral = array_sum(array_column($relatorio_participacao, 'total_vendas'));
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sistema de Comissões</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Comissões</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link <?= $aba === 'vendedores' ? 'active' : '' ?>" href="?aba=vendedores">Vendedores</a></li>
            <li class="nav-item"><a class="nav-link <?= $aba === 'vendas' ? 'active' : '' ?>" href="?aba=vendas">Vendas</a></li>
            <li class="nav-item"><a class="nav-link <?= $aba === 'relatorios' ? 'active' : '' ?>" href="?aba=relatorios">Relatórios</a></li>
        </ul>
    </div>
</nav>

<div class="container">
<?php if ($aba === 'vendedores'): ?>
<!-- FORM VENDEDORES -->
<form method="POST" action="?acao=salvar_vendedor&aba=vendedores" class="mb-3">
    <h4>Cadastro de Vendedores</h4>
    <input name="nome" class="form-control mb-2" placeholder="Nome" required>
    <input name="matricula" class="form-control mb-2" placeholder="Matrícula" required>
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" name="ativo" value="1" checked>
        <label class="form-check-label">Ativo</label>
    </div>
    <button class="btn btn-success">Salvar</button>
</form>

<?php elseif ($aba === 'vendas'): ?>
<!-- FORM VENDAS -->
<form method="POST" action="?acao=salvar_venda&aba=vendas" class="mb-3">
    <h4>Cadastro de Vendas</h4>
    <select name="id_funcionario" class="form-select mb-2" required>
        <option value="">Selecione o Vendedor</option>
        <?php foreach ($vendedores as $v): ?>
            <option value="<?= $v['id_funcionario'] ?>"><?= $v['nome'] ?></option>
        <?php endforeach; ?>
    </select>
    <input type="number" name="mes" class="form-control mb-2" placeholder="Mês" required>
    <input type="number" name="ano" class="form-control mb-2" placeholder="Ano" required>
    <input type="number" name="total_vendas" step="0.01" class="form-control mb-2" placeholder="Total de Vendas" required>
    <input type="number" name="percentual_comissao" step="0.01" class="form-control mb-2" placeholder="% Comissão" required>
    <button class="btn btn-success">Salvar</button>
</form>

<!-- LISTA DE VENDAS -->
<h5>Vendas Registradas</h5>
<table class="table table-bordered">
    <thead><tr><th>Vendedor</th><th>Mês</th><th>Ano</th><th>Venda</th><th>%</th><th>Comissão</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($vendas as $v): ?>
        <tr>
            <td><?= $v['nome'] ?></td>
            <td><?= $v['mes'] ?></td>
            <td><?= $v['ano'] ?></td>
            <td>R$ <?= number_format($v['total_vendas'], 2, ',', '.') ?></td>
            <td><?= $v['percentual_comissao'] ?>%</td>
            <td>R$ <?= number_format($v['total_vendas'] * $v['percentual_comissao'] / 100, 2, ',', '.') ?></td>
            <td><a href="?acao=excluir_venda&id=<?= $v['id_valor'] ?>&aba=vendas" class="btn btn-danger btn-sm">Excluir</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php elseif ($aba === 'relatorios'): ?>
<!-- RELATÓRIOS -->
<h4>Relatórios</h4>

<form method="GET" class="mb-3">
    <input type="hidden" name="aba" value="relatorios">
    <label>Selecionar tipo de relatório:</label>
    <select name="rel" class="form-select w-25 d-inline" onchange="this.form.submit()">
        <option value="1" <?= $tipo_relatorio === '1' ? 'selected' : '' ?>>Relatório de Participação nas Vendas</option>
        <option value="2" <?= $tipo_relatorio === '2' ? 'selected' : '' ?>>Relatório por Comissão e Mês</option>
    </select>
</form>

<?php if ($tipo_relatorio === '1'): ?>
<!-- RELATÓRIO 1 -->
<p>Total Geral de Vendas: <strong>R$ <?= number_format($total_geral, 2, ',', '.') ?></strong></p>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Vendedor</th>
            <th>Total de Vendas (R$)</th>
            <th>Participação (%)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($relatorio_participacao as $v):
            $porcentagem = $total_geral > 0 ? ($v['total_vendas'] / $total_geral) * 100 : 0;
        ?>
        <tr>
            <td><?= $v['nome'] ?></td>
            <td>R$ <?= number_format($v['total_vendas'], 2, ',', '.') ?></td>
            <td><?= number_format($porcentagem, 2, ',', '.') ?>%</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="?aba=relatorios&rel=1&export=excel" class="btn btn-outline-success">Exportar Excel</a>
<a href="?aba=relatorios&rel=1&export=csv" class="btn btn-outline-primary">Exportar CSV</a>

<?php elseif ($tipo_relatorio === '2'): ?>
<!-- RELATÓRIO 2 -->
<h5>Comissão por Vendedor</h5>
<table class="table table-bordered">
    <thead><tr><th>Vendedor</th><th>Total Comissão</th></tr></thead>
    <tbody>
        <?php foreach ($relatorio_vendedor as $r): ?>
        <tr>
            <td><?= $r['nome'] ?></td>
            <td>R$ <?= number_format($r['total_comissao'], 2, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h5>Total de Vendas por Mês</h5>
<table class="table table-bordered">
    <thead><tr><th>Mês</th><th>Ano</th><th>Total Vendas</th></tr></thead>
    <tbody>
        <?php foreach ($relatorio_mes as $r): ?>
        <tr>
            <td><?= $r['mes'] ?></td>
            <td><?= $r['ano'] ?></td>
            <td>R$ <?= number_format($r['total_mes'], 2, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php endif; ?>
</div>
</body>
</html>
