<?php
require 'id_db.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$nome = $data['nome'];
$email = $data['email'];
$senha = password_hash($data['senha'], PASSWORD_DEFAULT);
$telefone = $data['telefone'] ?? null;

$sql = "INSERT INTO CLIENTE (nome, email, senha, telefone) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $nome, $email, $senha, $telefone);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao cadastrar: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
