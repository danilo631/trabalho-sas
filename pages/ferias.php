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
require_once '../php/conexao.php'; // Ajuste o caminho conforme a estrutura do seu projeto

// Função para verificar conflito com afastamentos
function temConflitoAfastamento($pdo, $funcionario_id, $inicio, $fim) {
    $sql = "SELECT COUNT(*) FROM afastamentos 
            WHERE funcionario_id = :funcionario_id 
              AND ((data_inicio <= :fim AND data_fim >= :inicio))";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':funcionario_id' => $funcionario_id,
        ':inicio' => $inicio,
        ':fim' => $fim
    ]);
    return $stmt->fetchColumn() > 0;
}

// Função para verificar férias sobrepostas
function feriasSobrepostas($pdo, $funcionario_id, $inicio, $fim) {
    $sql = "SELECT COUNT(*) FROM ferias
            WHERE funcionario_id = :funcionario_id
              AND ((data_inicio <= :fim AND data_fim >= :inicio))";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':funcionario_id' => $funcionario_id,
        ':inicio' => $inicio,
        ':fim' => $fim
    ]);
    return $stmt->fetchColumn() > 0;
}

// Processa o formulário
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $funcionario_id = $_POST['funcionario_id'] ?? '';
    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_fim = $_POST['data_fim'] ?? '';

    // Validações básicas
    if (!$funcionario_id || !$data_inicio || !$data_fim) {
        $mensagem = "Por favor, preencha todos os campos.";
    } elseif ($data_inicio > $data_fim) {
        $mensagem = "Data de início não pode ser maior que data de fim.";
    } elseif ($data_inicio < date('Y-m-d')) {
        $mensagem = "Não é permitido lançar férias retroativas.";
    } elseif (temConflitoAfastamento($pdo, $funcionario_id, $data_inicio, $data_fim)) {
        $mensagem = "Erro: Férias conflitam com período de afastamento do funcionário.";
    } elseif (feriasSobrepostas($pdo, $funcionario_id, $data_inicio, $data_fim)) {
        $mensagem = "Erro: Férias sobrepõem período já cadastrado.";
    } else {
        // Insere no banco
        $sql = "INSERT INTO ferias (funcionario_id, data_inicio, data_fim) VALUES (:funcionario_id, :data_inicio, :data_fim)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([
            ':funcionario_id' => $funcionario_id,
            ':data_inicio' => $data_inicio,
            ':data_fim' => $data_fim
        ])) {
            $mensagem = "Férias cadastradas com sucesso!";
        } else {
            $mensagem = "Erro ao cadastrar férias.";
        }
    }
}

// Busca funcionários para o select
$funcionarios = $pdo->query("SELECT id, nome FROM funcionarios ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Busca férias cadastradas
$ferias = $pdo->query("
    SELECT f.id, f.data_inicio, f.data_fim, func.nome 
    FROM ferias f
    JOIN funcionarios func ON f.funcionario_id = func.id
    ORDER BY f.data_inicio DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Cadastro de Férias - RH Corporativo</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
    body { font-family: Arial, sans-serif; background: #fff; color: #424242; margin: 0; padding: 0; }
    header { background: #004080; color: white; padding: 1rem; text-align: center; }
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

</head>
<body>


<header>
    <h1>RH Corporativo – Cadastro de Férias</h1>
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
    <form method="POST" action="">
        <label for="funcionario_id">Funcionário</label>
        <select id="funcionario_id" name="funcionario_id" required>
            <option value="">-- Selecione --</option>
            <?php foreach ($funcionarios as $func): ?>
                <option value="<?= htmlspecialchars($func['id']) ?>">
                    <?= htmlspecialchars($func['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="data_inicio">Data de Início</label>
        <input type="date" id="data_inicio" name="data_inicio" required />

        <label for="data_fim">Data de Fim</label>
        <input type="date" id="data_fim" name="data_fim" required />

        <button type="submit">Cadastrar Férias</button>
    </form>

    <?php if ($mensagem): ?>
        <div class="message <?= strpos($mensagem, 'Erro') === false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <h2>Férias Cadastradas</h2>
    <table>
        <thead>
            <tr>
                <th>Funcionário</th>
                <th>Data Início</th>
                <th>Data Fim</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($ferias): ?>
                <?php foreach ($ferias as $feria): ?>
                    <tr>
                        <td><?= htmlspecialchars($feria['nome']) ?></td>
                        <td><?= htmlspecialchars($feria['data_inicio']) ?></td>
                        <td><?= htmlspecialchars($feria['data_fim']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3">Nenhuma férias cadastrada.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

</body>
</html>
