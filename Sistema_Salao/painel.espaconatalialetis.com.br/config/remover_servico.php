<?php
require 'id_db.php'; // Inclui o arquivo com a conexão ao banco

header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

$profissionalId = $data['profissionalId'] ?? null;
$servicoId = $data['servicoId'] ?? null;

if (!$profissionalId || !$servicoId) {
    echo json_encode(["success" => false, "message" => "Dados insuficientes."]);
    exit;
}

$stmt = $conn->prepare("UPDATE PROFISSIONAL_SERVICO SET status = 'INATIVO' WHERE profissionalId = ? AND servicoId = ?");
$stmt->bind_param("ii", $profissionalId, $servicoId);
if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao remover serviço."]);
}
$stmt->close();
$conn->close();
?>
