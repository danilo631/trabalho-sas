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
require_once '../php/conexao.php';

$mensagem = "";
$erro = "";

// Tipos possíveis de afastamento
$tipos_afastamento = [
    "Doença",
    "Licença Maternidade",
    "Licença Paternidade",
    "Acidente de Trabalho",
    "Outros"
];

// Função para verificar conflito com férias
function temConflitoFerias($pdo, $funcionario_id, $data_inicio, $data_fim) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ferias 
        WHERE funcionario_id = ? 
        AND (
            (data_inicio <= ? AND data_fim >= ?) OR
            (data_inicio <= ? AND data_fim >= ?) OR
            (data_inicio >= ? AND data_fim <= ?)
        )");
    $stmt->execute([
        $funcionario_id,
        $data_inicio, $data_inicio,
        $data_fim, $data_fim,
        $data_inicio, $data_fim
    ]);
    return $stmt->fetchColumn() > 0;
}

// Inserir afastamento
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $funcionario_id = $_POST['funcionario_id'];
    $tipo = $_POST['tipo'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $descricao = trim($_POST['descricao']);

    // Validação datas
    if ($data_inicio > $data_fim) {
        $erro = "Data de início não pode ser maior que data fim.";
    } elseif (temConflitoFerias($pdo, $funcionario_id, $data_inicio, $data_fim)) {
        $erro = "Conflito: Funcionário possui férias nesse período.";
    } else {
        // Inserir no banco
        $stmt = $pdo->prepare("INSERT INTO afastamentos (funcionario_id, tipo, data_inicio, data_fim, descricao) 
            VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$funcionario_id, $tipo, $data_inicio, $data_fim, $descricao])) {
            $mensagem = "Afastamento cadastrado com sucesso.";
        } else {
            $erro = "Erro ao cadastrar afastamento.";
        }
    }
}

// Listar funcionários
$funcionarios = $pdo->query("SELECT id, nome FROM funcionarios ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Listar afastamentos
$afastamentos = $pdo->query("
    SELECT a.*, f.nome 
    FROM afastamentos a
    JOIN funcionarios f ON a.funcionario_id = f.id
    ORDER BY a.data_inicio DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Afastamentos - RH Corporativo</title>
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
    <link rel="stylesheet" href="../assets/css/afastamento.css">
</head>
<body>



<header>
    <h1>RH Corporativo – Afastamentos</h1>
        <nav>
            <ul>
                <li><a href="../index.php">Início</a></li>
                <li><a href="./dashboard.php">Dashboard</a></li>
                <li><a href="./funcionarios.php">Funcionários</a></li>
                <li><a href="./relatorios.php">Relatórios</a></li>
            </ul>
        </nav>
</header>

<?php if ($mensagem): ?>
    <div class="mensagem"><?= htmlspecialchars($mensagem) ?></div>
<?php endif; ?>
<?php if ($erro): ?>
    <div class="erro"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

<h2>Cadastrar Afastamento</h2>
<form method="POST">
    <label>Funcionário:</label>
    <select name="funcionario_id" required>
        <option value="">Selecione</option>
        <?php foreach ($funcionarios as $f): ?>
            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nome']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>Tipo de Afastamento:</label>
    <select name="tipo" required>
        <option value="">Selecione</option>
        <?php foreach ($tipos_afastamento as $tipo): ?>
            <option value="<?= $tipo ?>"><?= $tipo ?></option>
        <?php endforeach; ?>
    </select>

    <label>Data Início:</label>
    <input type="date" name="data_inicio" required>

    <label>Data Fim:</label>
    <input type="date" name="data_fim" required>

    <label>Descrição (opcional):</label>
    <textarea name="descricao" rows="3"></textarea>

    <button type="submit">Cadastrar Afastamento</button>
</form>

<h2>Afastamentos Cadastrados</h2>
<table>
    <thead>
        <tr>
            <th>Funcionário</th>
            <th>Tipo</th>
            <th>Data Início</th>
            <th>Data Fim</th>
            <th>Descrição</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($afastamentos) === 0): ?>
            <tr><td colspan="5" style="text-align:center; color: #D50000;">Nenhum afastamento cadastrado.</td></tr>
        <?php else: ?>
            <?php foreach ($afastamentos as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['nome']) ?></td>
                    <td class="tipo"><?= htmlspecialchars($a['tipo']) ?></td>
                    <td><?= date('d/m/Y', strtotime($a['data_inicio'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($a['data_fim'])) ?></td>
                    <td><?= nl2br(htmlspecialchars($a['descricao'])) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
