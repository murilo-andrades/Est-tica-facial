<?php
require 'id_db.php'; // Inclui o arquivo com a conexão ao banco

// Obtém os dados da requisição
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];

// Consulta o status atual
$sql = "SELECT status FROM SERVICO WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $newStatus = $row['status'] === 'ATIVO' ? 'INATIVO' : 'ATIVO';

    // Atualiza o status no banco de dados
    $updateSql = "UPDATE SERVICO SET status = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("si", $newStatus, $id);

    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'newStatus' => $newStatus]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o status.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Serviço não encontrado.']);
}

$conn->close();
?>
