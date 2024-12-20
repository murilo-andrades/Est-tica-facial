<?php
require 'id_db.php'; // Inclui o arquivo com a conexão ao banco

header('Content-Type: application/json');

// Consulta os dados da tabela CONFIGURACAO
$sql = "SELECT * FROM CONFIGURACAO LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $config = $result->fetch_assoc();
    echo json_encode(["success" => true, "config" => $config]);
} else {
    echo json_encode(["success" => false, "message" => "Configuração não encontrada."]);
}

$conn->close();
