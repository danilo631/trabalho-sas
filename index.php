<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

echo "<h1>Bem-vindo, {$_SESSION['username']}</h1>";
echo "<p>Setor: {$_SESSION['setor']}</p>";
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
            </ul>
        </nav>
    </header>


    <!-- Seção de boas-vindas -->
    <section id="home" class="welcome">
        <h1>Bem-vindo ao RH Corporativo</h1>
        <p>Aqui você encontra ferramentas completas para gerenciar os funcionários, folha de pagamento, férias e mais.</p>
    </section>

    <!-- Seção de Cards ou Funcionalidades principais -->
    <section class="features">
    <h2>Funcionalidades</h2>
    <div class="features-container">
        <div class="card">
            <h3>Gestão de Funcionários</h3>
            <p>Cadastro, histórico e controle de afastamentos e férias.</p>
            <a href="./pages/funcionarios.php">Gerenciar</a>
        </div>
        <div class="card">
            <h3>Folha de Pagamento</h3>
            <p>Controle de salários, horas extras, descontos e faltas.</p>
            <a href="./pages/folha.php">Gerenciar</a>
        </div>
        <div class="card">
            <h3>Afastamentos</h3>
            <p>Gerenciamento de licenças, afastamentos e férias programadas.</p>
            <a href="./pages/afastamentos.php">Gerenciar</a>
        </div>
        <div class="card">
            <h3>Relatórios</h3>
            <p>Gerar relatórios de absenteísmo, folha de pagamento e muito mais.</p>
            <a href="./pages/relatorios.php">Gerar Relatórios</a>
        </div>
        <div class="card">
            <h3>Registrar Férias</h3>
            <p>Cadastre as férias dos funcionários de forma rápida e fácil.</p>
            <a href="./pages/ferias.php">Registrar Férias</a>
        </div>
    </div>
</section>


    <!-- Seção de contato -->
    <section id="contact" class="contact">
        <h2>Contato</h2>
        <p>Se você tem dúvidas ou precisa de suporte, entre em contato com nosso time de RH.</p>
        <form action="mailto:support@rhcorporativo.com" method="post" enctype="text/plain">
            <label for="name">Nome:</label><br>
            <input type="text" id="name" name="name" required><br><br>
            
            <label for="email">E-mail:</label><br>
            <input type="email" id="email" name="email" required><br><br>

            <label for="message">Mensagem:</label><br>
            <textarea id="message" name="message" rows="4" required></textarea><br><br>

            <input type="submit" value="Enviar">
        </form>
    </section>

    <!-- Rodapé -->
    <footer>
        <p>&copy; 2025 RH Corporativo | Todos os direitos reservados</p>
    </footer>

    <script src="./assets/js/script.js"></script>
</body>

</html>
