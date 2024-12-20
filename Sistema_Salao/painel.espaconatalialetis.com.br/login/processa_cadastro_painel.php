<?php
require '../config/id_db.php'; // Inclua seu arquivo de conexão com o banco de dados

// Captura os dados enviados pelo formulário
$nome = $_POST['nome'] ?? '';
$sobrenome = $_POST['sobrenome'] ?? '';
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';
$cpf = $_POST['cpf'] ?? '';

// Remove caracteres desnecessários do CPF
$cpf = preg_replace('/[^0-9]/', '', $cpf);

// Validações básicas
if (!$nome || !$sobrenome || !$email || !$senha || !$cpf) {
    die("Erro: Todos os campos são obrigatórios.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Erro: E-mail inválido.");
}

if (strlen($cpf) !== 11) {
    die("Erro: CPF inválido.");
}

// Verifica se o e-mail ou CPF já existe no banco de dados
$stmt = $conn->prepare("SELECT id FROM USUARIO_PAINEL WHERE email = ? OR cpf = ?");
$stmt->bind_param("ss", $email, $cpf);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die("Erro: E-mail ou CPF já cadastrado.");
}

// Criptografa a senha
$senhaHash = password_hash($senha, PASSWORD_BCRYPT);

// Insere o usuário no banco de dados
$stmt = $conn->prepare("INSERT INTO USUARIO_PAINEL (nome, sobrenome, email, senha, cpf) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $nome, $sobrenome, $email, $senhaHash, $cpf);

if ($stmt->execute()) {
    echo "Cadastro realizado com sucesso!";
    header("Location: login.php"); // Redireciona para a página de login
    exit();
} else {
    die("Erro ao cadastrar usuário: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>
