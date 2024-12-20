<?php
require 'id_db.php'; // Inclui o arquivo com a conexão ao banco

header('Content-Type: application/json');

// Obtém o ID do profissional
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verifica se o ID foi enviado
if ($id === 0) {
    echo json_encode(["success" => false, "message" => "ID inválido."]);
    exit;
}

// Consulta os dados do profissional
$sql = "SELECT * FROM PROFISSIONAL WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Retorna os dados do profissional se encontrado
if ($result->num_rows > 0) {
    $profissional = $result->fetch_assoc();
    echo json_encode(["success" => true, "data" => $profissional]);
} else {
    echo json_encode(["success" => false, "message" => "Profissional não encontrado."]);
}

$stmt->close();
$conn->close();
?>
