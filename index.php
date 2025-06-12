<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Obtendo os dados da sessão
$username = $_SESSION['username'];
$setor = $_SESSION['setor'];
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RH Corporativo</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
    <!-- Cabeçalho -->
    <header>
        <div class="logo">
            <img src="./assets/images/logo.png" alt="Logo da Empresa">
            <span>RH Corporativo</span>
        </div>
        
        <!-- Menu de navegação -->
        <nav>
            <ul>
                <li><a href="#home">Início</a></li>
                <li><a href="./pages/dashboard.php">Dashboard</a></li>
                <li><a href="./logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <!-- Boas-vindas -->
    <section id="home" class="welcome">
        <h1>Bem-vindo, <?= htmlspecialchars($username) ?></h1>
        <p>Setor: <?= htmlspecialchars($setor) ?></p>
        <p>Aqui você encontra ferramentas completas para gerenciar os funcionários, folha de pagamento, férias e mais.</p>
    </section>

    <!-- Funcionalidades conforme setor -->
    <section class="features">
        <h2>Funcionalidades</h2>
        <div class="features-container">

            <?php if ($setor === 'administrativo' || $setor === 'RH'): ?>
            <div class="card">
                <h3>Gestão de Funcionários</h3>
                <p>Cadastro, histórico e controle de dados dos colaboradores.</p>
                <a href="./pages/funcionarios.php">Gerenciar</a>
            </div>
            <?php endif; ?>

            <?php if ($setor === 'administrativo'): ?>
            <div class="card">
                <h3>Folha de Pagamento</h3>
                <p>Controle de salários, horas extras, descontos e faltas.</p>
                <a href="./pages/folha.php">Gerenciar</a>
            </div>
            <?php endif; ?>

            <?php if ($setor === 'RH'): ?>
            <div class="card">
                <h3>Férias</h3>
                <p>Cadastro e controle das férias dos funcionários.</p>
                <a href="./pages/ferias.php">Registrar Férias</a>
            </div>

            <div class="card">
                <h3>Afastamentos</h3>
                <p>Gerenciamento de licenças, afastamentos e controle de faltas.</p>
                <a href="./pages/afastamentos.php">Gerenciar</a>
            </div>
             <?php endif; ?>

             <?php if ($setor === 'Auditoria'): ?>

            <div class="card">
                <h3>Relatórios</h3>
                <p>Gerar relatórios de absenteísmo, folha de pagamento e auditoria.</p>
                <a href="./pages/relatorios.php">Gerar Relatórios</a>
            </div>

            <?php endif; ?>

        </div>
    </section>

    <!-- Rodapé -->
    <footer>
        <p>&copy; 2025 RH Corporativo | Todos os direitos reservados</p>
    </footer>

    <script src="./assets/js/script.js"></script>
</body>

</html>
