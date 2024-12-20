<?php
header('Content-Type: application/json; charset=utf-8');

session_start();
require '../config/id_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $senha = $_POST['senha'];

    if (empty($usuario) || empty($senha)) {
        echo json_encode(['success' => false, 'message' => 'Por favor, preencha todos os campos.']);
        exit();
    }

    $isEmail = filter_var($usuario, FILTER_VALIDATE_EMAIL);
    $query = $isEmail
        ? "SELECT id, nome, sobrenome, senha FROM USUARIO_PAINEL WHERE email = ?"
        : "SELECT id, nome, sobrenome, senha FROM USUARIO_PAINEL WHERE cpf = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Usuário ou senha incorretos.']);
        exit();
    }

    $user = $result->fetch_assoc();

    if (!password_verify($senha, $user['senha'])) {
        echo json_encode(['success' => false, 'message' => 'Usuário ou senha incorretos.']);
        exit();
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nome'] = $user['nome'];
    $_SESSION['user_sobrenome'] = $user['sobrenome'];

    echo json_encode(['success' => true, 'message' => 'Login realizado com sucesso.']);
    exit();
}
