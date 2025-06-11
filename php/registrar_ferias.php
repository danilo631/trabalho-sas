<?php
$cpf = $_POST['cpf'];
$inicio = $_POST['inicio'];
$fim = $_POST['fim'];

$arquivo = fopen("ferias.csv","a");
$putcsv($arquivo, [$cpf, $inicio, $fim]);
fclose($arquivo);

echo "Ferias registradas com sucesso!";
?>