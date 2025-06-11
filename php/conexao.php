<?php
$host = 'localhost';
$db = 'rh_corporativo';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // mostra erros
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // retorno associativo
    PDO::ATTR_EMULATE_PREPARES => false, // usa prepared statements nativos
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Não foi possível conectar ao banco de dados: " . $e->getMessage());
}
?>
