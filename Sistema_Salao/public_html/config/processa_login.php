<?php
session_start();
require 'id_db.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];
$senha = $data['senha'];

$sql = "SELECT id, nome, senha FROM CLIENTE WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($id, $nome, $hashSenha);
$stmt->fetch();

if ($stmt && password_verify($senha, $hashSenha)) {
    // Definindo as variáveis de sessão
    $_SESSION['user_id'] = $id;
    $_SESSION['user_email'] = $email; // Adicionando o email na sessão
    $_SESSION['user_name'] = $nome;

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Email ou senha incorretos."]);
}

$stmt->close();
$conn->close();
?>
