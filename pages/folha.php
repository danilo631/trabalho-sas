<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

echo "<h1>Bem-vindo, {$_SESSION['username']}</h1>";
echo "<p>Setor: {$_SESSION['setor']}</p>";
?>

<?php
require_once '../php/conexao.php'; // Ajuste o caminho conforme necessário

setlocale(LC_TIME, 'pt_BR.UTF-8');

// Inicializa variáveis
$folhas = [];
$funcionarios = [];

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['funcionario_id'])) {
    $funcionario_id = $_POST['funcionario_id'];
    $mes_referencia = $_POST['mes_referencia'] . '-01'; // Formato DATE
    $salario_base = floatval($_POST['salario_base']);
    $horas_extras = floatval($_POST['horas_extras']);
    $faltas = floatval($_POST['faltas']);
    $descontos = floatval($_POST['descontos']);

    // Cálculos
    $valor_hora = $salario_base / 160;
    $extra = $valor_hora * $horas_extras;
    $desconto_faltas = $valor_hora * $faltas;
    $total_liquido = $salario_base + $extra - $desconto_faltas - $descontos;

    // Inserção no banco
    $stmt = $pdo->prepare("INSERT INTO folha_pagamento 
        (funcionario_id, mes_referencia, salario_base, horas_extras, faltas, descontos, total_liquido) 
        VALUES (:funcionario_id, :mes_referencia, :salario_base, :horas_extras, :faltas, :descontos, :total_liquido)");

    $stmt->execute([
        ':funcionario_id' => $funcionario_id,
        ':mes_referencia' => $mes_referencia,
        ':salario_base' => $salario_base,
        ':horas_extras' => $horas_extras,
        ':faltas' => $faltas,
        ':descontos' => $descontos,
        ':total_liquido' => $total_liquido
    ]);
}

// Busca folha
$sql = "SELECT f.*, func.nome, func.cargo 
        FROM folha_pagamento f 
        JOIN funcionarios func ON f.funcionario_id = func.id 
        ORDER BY f.mes_referencia DESC";

$stmt = $pdo->query($sql);
$folhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lista funcionários
$stmtFuncionarios = $pdo->query("SELECT id, nome FROM funcionarios ORDER BY nome ASC");
$funcionarios = $stmtFuncionarios->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Folha de Pagamento</title>
    <style>
    body { font-family: Arial, sans-serif; background: #fff; color: #424242; margin: 0; padding: 0; }
    header { background: #1565C0; color: white; padding: 1rem; text-align: center; }
    main { max-width: 900px; margin: 2rem auto; padding: 1rem; }
    h1 { margin-bottom: 1rem; }
    form { background: #f9f9f9; padding: 1rem; border: 1px solid #ccc; border-radius: 5px; }
    label { display: block; margin-top: 1rem; font-weight: bold; }
    select, input[type=date] { width: 100%; padding: 0.5rem; margin-top: 0.3rem; border: 1px solid #ccc; border-radius: 3px; }
    button { margin-top: 1rem; background: #1565C0; color: white; padding: 0.7rem 1.2rem; border: none; border-radius: 3px; cursor: pointer; font-size: 1rem; }
    button:hover { background: #0d47a1; }
    .message { margin-top: 1rem; padding: 0.8rem; border-radius: 4px; }
    .success { background: #00C853; color: white; }
    .error { background: #D50000; color: white; }
    table { width: 100%; border-collapse: collapse; margin-top: 2rem; }
    th, td { border: 1px solid #ccc; padding: 0.6rem; text-align: left; }
    th { background: #1565C0; color: white; }
</style>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header>
    <h1>Folha de Pagamento</h1>
    <nav>
        <ul>
            <li><a href="../index.php">Início</a></li>
            <li><a href="./dashboard.php">Dashboard</a></li>
            <li><a href="./funcionarios.php">Funcionários</a></li>
            <li><a href="./relatorios.php">Relatórios</a></li>
        </ul>
    </nav>
</header>

<main>
    <h2>Folhas Registradas</h2>
    <table>
        <thead>
            <tr>
                <th>Funcionário</th>
                <th>Cargo</th>
                <th>Mês Referência</th>
                <th>Salário Base</th>
                <th>Horas Extras</th>
                <th>Faltas</th>
                <th>Descontos</th>
                <th>Total Líquido</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($folhas) === 0): ?>
            <tr><td colspan="8" style="text-align:center; color:red;">Nenhuma folha registrada.</td></tr>
        <?php else: ?>
            <?php foreach ($folhas as $folha): ?>
                <tr>
                    <td><?= htmlspecialchars($folha['nome']) ?></td>
                    <td><?= htmlspecialchars($folha['cargo']) ?></td>
                    <td><?= strftime('%B/%Y', strtotime($folha['mes_referencia'])) ?></td>
                    <td>R$ <?= number_format($folha['salario_base'], 2, ',', '.') ?></td>
                    <td><?= number_format($folha['horas_extras'], 2, ',', '.') ?> h</td>
                    <td><?= number_format($folha['faltas'], 2, ',', '.') ?> h</td>
                    <td>R$ <?= number_format($folha['descontos'], 2, ',', '.') ?></td>
                    <td><strong>R$ <?= number_format($folha['total_liquido'], 2, ',', '.') ?></strong></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <h2>Nova Folha de Pagamento</h2>
    <form method="POST">
        <label for="funcionario_id">Funcionário:</label>
        <select name="funcionario_id" id="funcionario_id" required>
            <option value="">Selecione</option>
            <?php foreach ($funcionarios as $func): ?>
                <option value="<?= $func['id'] ?>"><?= htmlspecialchars($func['nome']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="mes_referencia">Mês de Referência:</label>
        <input type="month" name="mes_referencia" id="mes_referencia" required>

        <label for="salario_base">Salário Base:</label>
        <input type="number" step="0.01" name="salario_base" id="salario_base" required>

        <label for="horas_extras">Horas Extras:</label>
        <input type="number" step="0.01" name="horas_extras" id="horas_extras" value="0" required>

        <label for="faltas">Horas de Faltas:</label>
        <input type="number" step="0.01" name="faltas" id="faltas" value="0" required>

        <label for="descontos">Descontos (R$):</label>
        <input type="number" step="0.01" name="descontos" id="descontos" value="0" required>

        <button type="submit">Salvar</button>
    </form>
</main>

</body>
</html>
