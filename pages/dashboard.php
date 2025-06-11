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
// Inclui o arquivo de conexão com o banco de dados
include('../php/conexao.php');

// Função para obter o total de funcionários
function getTotalFuncionarios($pdo) {
    $sql = "SELECT COUNT(*) FROM funcionarios";
    $stmt = $pdo->query($sql);
    return $stmt->fetchColumn();
}

// Função para obter a folha de pagamento total
function getTotalFolhaPagamento($pdo) {
    $sql = "SELECT 
                SUM(salario_base) AS total_salario,
                SUM(horas_extras) AS total_extras,
                SUM(descontos) AS total_descontos
            FROM folha_pagamento";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $total = floatval($result['total_salario']) + floatval($result['total_extras']) - floatval($result['total_descontos']);
    return $total;
}

// Função para obter funcionários em afastamento
function getFuncionariosAfastamento($pdo) {
    $sql = "SELECT COUNT(*) FROM funcionarios WHERE afastamento = 1";
    $stmt = $pdo->query($sql);
    return $stmt->fetchColumn();
}

// Função para obter funcionários em férias
function getFuncionariosFerias($pdo) {
    $sql = "SELECT COUNT(*) FROM funcionarios WHERE ferias = 1";
    $stmt = $pdo->query($sql);
    return $stmt->fetchColumn();
}

// Função para obter quantidade de funcionários por setor
function getFuncionariosPorSetor($pdo) {
    $sql = "SELECT setor, COUNT(*) AS total FROM funcionarios GROUP BY setor";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Buscando os dados
$totalFuncionarios = getTotalFuncionarios($pdo);
$totalFolhaPagamento = getTotalFolhaPagamento($pdo);
$funcionariosAfastamento = getFuncionariosAfastamento($pdo);
$funcionariosFerias = getFuncionariosFerias($pdo);
$setores = getFuncionariosPorSetor($pdo);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RH Corporativo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>
<body>
    <!-- Cabeçalho -->
    <header>
        <div class="logo">
            <img src="../assets/images/logo.png" alt="Logo da Empresa">
            <span>RH Corporativo</span>
        </div>
        <nav>
            <ul>
                <li><a href="../index.php">Início</a></li>
                <li><a href="./dashboard.php">Dashboard</a></li>
                <li><a href="./funcionarios.php">Funcionários</a></li>
                <li><a href="./relatorios.php">Relatórios</a></li>
            </ul>
        </nav>
    </header>

    <!-- Seção do Dashboard -->
    <section class="dashboard">
        <div class="container">
            <!-- Cards Informativos -->
            <div class="card">
                <h3>Total de Funcionários</h3>
                <p><?php echo $totalFuncionarios; ?></p>
            </div>
            <div class="card">
                <h3>Folha de Pagamento Total</h3>
                <p>R$ <?php echo number_format($totalFolhaPagamento, 2, ',', '.'); ?></p>
            </div>
            <div class="card">
                <h3>Funcionários em Afastamento</h3>
                <p><?php echo $funcionariosAfastamento; ?></p>
            </div>
            <div class="card">
                <h3>Funcionários com Férias</h3>
                <p><?php echo $funcionariosFerias; ?></p>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="charts">
            <!-- Gráfico de Absenteísmo -->
            <div id="absenteismo_chart" style="width: 900px; height: 500px;"></div>

            <!-- Gráfico de Distribuição de Funcionários por Setor -->
            <div id="setores_chart" style="width: 900px; height: 500px;"></div>
        </div>
    </section>

    <!-- Rodapé -->
    <footer>
        <p>&copy; 2025 RH Corporativo | Todos os direitos reservados</p>
    </footer>

    <script type="text/javascript">
        // Carregar o pacote de gráficos
        google.charts.load('current', {'packages':['corechart']});

        // Gráfico de Absenteísmo
        google.charts.setOnLoadCallback(drawAbsenteismoChart);
        function drawAbsenteismoChart() {
            var data = google.visualization.arrayToDataTable([
                ['Status', 'Número de Funcionários'],
                ['Afastados', <?php echo $funcionariosAfastamento; ?>],
                ['Em Férias', <?php echo $funcionariosFerias; ?>],
                ['Ativos', <?php echo $totalFuncionarios - ($funcionariosAfastamento + $funcionariosFerias); ?>]
            ]);

            var options = {
                title: 'Absenteísmo',
                pieHole: 0.4,
            };

            var chart = new google.visualization.PieChart(document.getElementById('absenteismo_chart'));
            chart.draw(data, options);
        }

        // Gráfico de Distribuição de Funcionários por Setor
        google.charts.setOnLoadCallback(drawSetoresChart);
        function drawSetoresChart() {
            var data = google.visualization.arrayToDataTable([
                ['Setor', 'Número de Funcionários'],
                <?php foreach ($setores as $setor): ?>
                    ['<?= htmlspecialchars($setor['setor'] ?: "Não Definido") ?>', <?= $setor['total'] ?>],
                <?php endforeach; ?>
            ]);

            var options = {
                title: 'Distribuição de Funcionários por Setor',
                is3D: true,
            };

            var chart = new google.visualization.PieChart(document.getElementById('setores_chart'));
            chart.draw(data, options);
        }

        // Redesenhar gráficos ao redimensionar a janela
        window.addEventListener('resize', function() {
            drawAbsenteismoChart();
            drawSetoresChart();
        });
    </script>
</body>
</html>
