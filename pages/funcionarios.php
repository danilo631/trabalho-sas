<?php
session_start();

// Obtendo os dados da sessão
$username = $_SESSION['username'];
$setor = $_SESSION['setor'];
?>

<?php
// Conexão com banco
require_once '../php/conexao.php';

// Variáveis de controle
$mensagem = "";

// Inserção de novo funcionário
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $rg = $_POST['rg'];
    $cargo = $_POST['cargo'];
    $setor = $_POST['setor'];
    $salario = $_POST['salario'];
    $admissao = $_POST['admissao'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];

    // Validação simples
    if ($nome && $cpf && $rg && $cargo && $setor && $salario && $admissao && $telefone && $email) {
        $sql = "INSERT INTO funcionarios (nome, cpf, rg, cargo, setor, salario, data_admissao, telefone, email) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$nome, $cpf, $rg, $cargo, $setor, $salario, $admissao, $telefone, $email])) {
            $mensagem = "Funcionário cadastrado com sucesso!";
        } else {
            $mensagem = "Erro ao cadastrar funcionário.";
        }
    } else {
        $mensagem = "Preencha todos os campos!";
    }
}

// Listar funcionários
$lista = $pdo->query("SELECT * FROM funcionarios ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Funcionários - RH Corporativo</title>
    <style>
    body { font-family: Arial, sans-serif; background: #fff; color: #424242; margin: 0; padding: 0; }
    header { background: #1565C0; color: white; padding: 1rem; text-align: center; }
        .mensagem {
            margin-top: 10px;
            padding: 10px;
            background-color: #00C853;
            color: white;
        }
        form {
            background: #F5F5F5;
            padding: 20px;
            margin-top: 20px;
            border: 1px solid #CCC;
        }
        input, select {
            padding: 8px;
            margin: 5px;
            width: calc(100% - 20px);
        }
        button {
            background-color: #1565C0;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
        }
        .tabela-funcionarios {
            margin-top: 30px;
            border-collapse: collapse;
            width: 100%;
        }
        .tabela-funcionarios th, .tabela-funcionarios td {
            border: 1px solid #CCC;
            padding: 8px;
        }
        .tabela-funcionarios th {
            background-color: #424242;
            color: white;
        }
        .tabela-funcionarios tr:nth-child(even) {
            background-color: #FAFAFA;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>



<header>
    <h1>RH Corporativo – Gestão de Funcionários</h1>
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
    <div class="mensagem"><?= $mensagem ?></div>
<?php endif; ?>

<h2>Cadastro de Funcionário</h2>
<form method="POST">
    <input type="text" name="nome" placeholder="Nome completo" required>
    <input type="text" name="cpf" placeholder="CPF" required>
    <input type="text" name="rg" placeholder="RG" required>
    <input type="text" name="cargo" placeholder="Cargo" required>
    <input type="text" name="setor" placeholder="Setor" required>
    <input type="number" step="0.01" name="salario" placeholder="Salário" required>
    <input type="date" name="admissao" required>
    <input type="text" name="telefone" placeholder="Telefone" required>
    <input type="email" name="email" placeholder="E-mail" required>
    <button type="submit">Cadastrar</button>
</form>

<h2>Funcionários Cadastrados</h2>
<table class="tabela-funcionarios">
    <thead>
        <tr>
            <th>Nome</th>
            <th>CPF</th>
            <th>RG</th>
            <th>Cargo</th>
            <th>Setor</th>
            <th>Salário</th>
            <th>Admissão</th>
            <th>Telefone</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lista as $func): ?>
            <tr>
                <td><?= htmlspecialchars($func['nome']) ?></td>
                <td><?= htmlspecialchars($func['cpf']) ?></td>
                <td><?= htmlspecialchars($func['rg']) ?></td>
                <td><?= htmlspecialchars($func['cargo']) ?></td>
                <td><?= htmlspecialchars($func['setor']) ?></td>
                <td>R$ <?= number_format($func['salario'], 2, ',', '.') ?></td>
                <td><?= htmlspecialchars($func['data_admissao']) ?></td>
                <td><?= htmlspecialchars($func['telefone']) ?></td>
                <td><?= htmlspecialchars($func['email']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
