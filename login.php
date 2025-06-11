<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST["usuario"];
    $senha = $_POST["senha"];

    $usuarios = [
        "rh" => ["senha" => "123", "perfil" => "RH"],
        "admin" => ["senha" => "admin", "perfil" => "Admin"],
        "auditor" => ["senha" => "audit", "perfil" => "Auditor"]
    ];

    if (isset($usuarios[$usuario]) && $usuarios[$usuario]["senha"] == $senha) {
        $_SESSION["usuario"] = $usuario;
        $_SESSION["perfil"] = $usuarios[$usuario]["perfil"];
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Usuário ou senha inválidos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Login - RH Corporativo</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f0f0; }
        .login-container {
            max-width: 400px;
            margin: 80px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
        }
        .error {
            color: red;
            margin-bottom: 10px;
            padding: 8px;
            background-color: #ffe0e0;
            border: 1px solid red;
            border-radius: 4px;
        }
        label { font-weight: bold; }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 6px 0 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Login RH Corporativo</h2>
    
    <?php if (!empty($erro)): ?>
    <div class="error"><?= htmlspecialchars($erro) ?></div>
<?php endif; ?>

    <form method="post" action="">
        <label for="username">Usuário</label>
        <input type="text" id="username" name="usuario" value="<?= htmlspecialchars($username ?? '') ?>" required autofocus />

        <label for="password">Senha</label>
        <input type="password" id="password" name="senha" required />


        <button type="submit">Entrar</button>
    </form>
</div>
</body>
</html>
