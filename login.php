<?php
session_start();

// Conexão com o banco de dados
$host = "localhost";
$db = "rh_corporativo";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"]);
    $senha = trim($_POST["senha"]);

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = :usuario LIMIT 1");
    $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se usar password_hash no cadastro, utilize password_verify
        if ($user["password"] === $senha || password_verify($senha, $user["password"])) {
            // Corrigido: usando "username" e "setor" para bater com index.php
            $_SESSION["username"] = $user["username"];
            $_SESSION["setor"] = $user["setor"];
            $_SESSION["perfil"] = $user["role"]; // opcional
            header("Location: index.php");
            exit;
        } else {
            $erro = "Senha incorreta.";
        }
    } else {
        $erro = "Usuário não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Login - RH Corporativo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
        }
        .login-container {
            max-width: 400px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
        }
        .error {
            color: red;
            background-color: #ffe0e0;
            border: 1px solid red;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            width: 100%;
            background-color: #007bff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Login RH Corporativo</h2>

    <?php if (!empty($erro)): ?>
        <div class="error"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <label for="usuario">Usuário</label>
        <input type="text" id="usuario" name="usuario" required autofocus />

        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required />

        <button type="submit">Entrar</button>
    </form>
</div>
</body>
</html>
