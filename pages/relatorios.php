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

$erro = "";
$mensagem = "";

$tiposRelatorio = [
    "absenteismo" => "Absenteísmo",
    "ferias" => "Histórico de Férias",
    "folha" => "Folha de Pagamento"
];

$relatorioSelecionado = $_POST['tipo_relatorio'] ?? "";
$data_inicio = $_POST['data_inicio'] ?? "";
$data_fim = $_POST['data_fim'] ?? "";

$resultados = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!$relatorioSelecionado) {
        $erro = "Selecione um tipo de relatório.";
    } elseif (!$data_inicio || !$data_fim) {
        $erro = "Informe o intervalo de datas.";
    } elseif ($data_inicio > $data_fim) {
        $erro = "Data inicial não pode ser maior que a data final.";
    } else {
        try {
            if ($relatorioSelecionado === 'absenteismo') {
                $stmt = $pdo->prepare("
                    SELECT f.nome, af.tipo, af.data_inicio, af.data_fim, af.descricao
                    FROM afastamentos af
                    INNER JOIN funcionarios f ON af.funcionario_id = f.id
                    WHERE (af.data_inicio BETWEEN :data_inicio AND :data_fim) 
                       OR (af.data_fim BETWEEN :data_inicio AND :data_fim)
                       OR (:data_inicio BETWEEN af.data_inicio AND af.data_fim)
                    ORDER BY f.nome, af.data_inicio
                ");
            } elseif ($relatorioSelecionado === 'ferias') {
                $stmt = $pdo->prepare("
                    SELECT f.nome, fer.data_inicio, fer.data_fim
                    FROM ferias fer
                    INNER JOIN funcionarios f ON fer.funcionario_id = f.id
                    WHERE (fer.data_inicio BETWEEN :data_inicio AND :data_fim) 
                       OR (fer.data_fim BETWEEN :data_inicio AND :data_fim)
                    ORDER BY f.nome, fer.data_inicio
                ");
            } elseif ($relatorioSelecionado === 'folha') {
                $stmt = $pdo->prepare("
                    SELECT f.nome, fp.mes_referencia, fp.salario_base, fp.horas_extras, fp.faltas, fp.descontos, fp.total_liquido
                    FROM folha_pagamento fp
                    INNER JOIN funcionarios f ON fp.funcionario_id = f.id
                    WHERE fp.mes_referencia BETWEEN :data_inicio AND :data_fim
                    ORDER BY f.nome, fp.mes_referencia
                ");
            }

            $stmt->execute([
                'data_inicio' => $data_inicio,
                'data_fim' => $data_fim
            ]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $erro = "Erro ao buscar dados: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Relatórios - RH Corporativo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #333;
            margin: 0;
            padding: 0;
        }
header {
    background-color: #004080;
    color: var(--branco);
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    box-shadow: 0 2px 4px var(--sombra);
}

h1 {
    color: #f4f6f8;
}

        /* Navegação */
nav ul {
    list-style: none;
    display: flex;
    gap: 25px;
}

nav a {
    color: #f4f6f8;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s;
}

nav a:hover {
    color: var(--azul-claro);
}

        main {
            max-width: 960px;
            margin: 2rem auto;
            padding: 1rem;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        form {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        label {
            display: block;
            margin-top: 1rem;
            font-weight: bold;
        }

        select, input[type=date] {
            width: 100%;
            padding: 0.5rem;
            margin-top: 0.3rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            margin-top: 1rem;
            background: #1565C0;
            color: white;
            padding: 0.7rem 1.2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        button:hover {
            background: #0d47a1;
        }

        .erro {
            background-color: #ff5252;
            color: white;
            padding: 0.8rem;
            border-radius: 4px;
            margin-top: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 0.6rem;
            text-align: left;
        }

        th {
            background: #1565C0;
            color: white;
        }

        h2 {
            margin-top: 2rem;
        }
    </style>
</head>
<body>

<header>
    <h1>Relatórios - RH Corporativo</h1>
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
    <p>Selecione o tipo de relatório e o intervalo de datas para gerar o relatório desejado.</p>

    <?php if ($erro): ?>
        <div class="erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <label for="tipo_relatorio">Tipo de Relatório:</label>
        <select id="tipo_relatorio" name="tipo_relatorio" required>
            <option value="">-- Selecione --</option>
            <?php foreach ($tiposRelatorio as $key => $label): ?>
                <option value="<?= $key ?>" <?= ($relatorioSelecionado === $key) ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="data_inicio">Data Início:</label>
        <input type="date" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>" required>

        <label for="data_fim">Data Fim:</label>
        <input type="date" id="data_fim" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>" required>

        <button type="submit">Gerar Relatório</button>
    </form>

    <?php if ($_SERVER["REQUEST_METHOD"] === "POST" && !$erro): ?>
        <?php if (empty($resultados)): ?>
            <p>Nenhum dado encontrado para o filtro selecionado.</p>
        <?php else: ?>
            <h2>Resultado - <?= $tiposRelatorio[$relatorioSelecionado] ?></h2>

            <?php if ($relatorioSelecionado === 'absenteismo'): ?>
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
                        <?php foreach ($resultados as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nome']) ?></td>
                                <td><?= htmlspecialchars($row['tipo']) ?></td>
                                <td><?= htmlspecialchars($row['data_inicio']) ?></td>
                                <td><?= htmlspecialchars($row['data_fim']) ?></td>
                                <td><?= htmlspecialchars($row['descricao']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php elseif ($relatorioSelecionado === 'ferias'): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Funcionário</th>
                            <th>Início</th>
                            <th>Fim</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultados as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nome']) ?></td>
                                <td><?= htmlspecialchars($row['data_inicio']) ?></td>
                                <td><?= htmlspecialchars($row['data_fim']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php elseif ($relatorioSelecionado === 'folha'): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Funcionário</th>
                            <th>Mês Referência</th>
                            <th>Salário Base</th>
                            <th>Horas Extras</th>
                            <th>Faltas</th>
                            <th>Descontos</th>
                            <th>Total Líquido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultados as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nome']) ?></td>
                                <td><?= date('m/Y', strtotime($row['mes_referencia'])) ?></td>
                                <td>R$ <?= number_format($row['salario_base'], 2, ',', '.') ?></td>
                                <td><?= $row['horas_extras'] ?></td>
                                <td><?= $row['faltas'] ?></td>
                                <td>R$ <?= number_format($row['descontos'], 2, ',', '.') ?></td>
                                <td>R$ <?= number_format($row['total_liquido'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</main>

</body>
</html>
