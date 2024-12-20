<?php
require 'id_db.php'; // Inclui o arquivo com a conexão ao banco

header('Content-Type: application/json');

// Recebe os dados enviados via POST
$id = $_POST['id'] ?? null;
$nome = $_POST['nome'] ?? null;
$telefone = $_POST['telefone'] ?? null;
$email = $_POST['email'] ?? null;
$sexo = $_POST['sexo'] ?? null;
$dataNascimento = $_POST['data_nascimento'] ?? null;
$titular = $_POST['titular'] ?? null;
$cpf = $_POST['cpf'] ?? null;
$banco = $_POST['banco'] ?? null;
$tipo = $_POST['tipo'] ?? null;
$agencia = $_POST['agencia'] ?? null;
$numeroConta = $_POST['numero_conta'] ?? null;
$foto = $_FILES['foto'] ?? null;

// Log para debug
error_log("Dados recebidos:");
error_log("ID: $id, Nome: $nome, Telefone: $telefone, Email: $email, Sexo: $sexo, Data Nascimento: $dataNascimento, Titular: $titular, CPF: $cpf, Banco: $banco, Tipo: $tipo, Agência: $agencia, Número Conta: $numeroConta");

// Validações básicas
if (!$id || !$nome || !$telefone || !$email) {
    echo json_encode(["success" => false, "message" => "Dados incompletos fornecidos."]);
    exit;
}

// Diretório para salvar a foto
$uploadDir = __DIR__ . '/../../public_html/src/fotos/foto_profissional/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Cria o diretório, se não existir
}

// Processa o upload da foto, se houver
$fotoPath = null;
if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
    $fotoName = uniqid() . '-' . basename($foto['name']);
    $fotoFullPath = $uploadDir . $fotoName;
    $fotoPath = '/src/fotos/foto_profissional/' . $fotoName;
    

    if (!move_uploaded_file($foto['tmp_name'], $fotoFullPath)) {
        error_log("Erro ao mover a foto para $fotoFullPath");
        echo json_encode(["success" => false, "message" => "Erro ao fazer upload da foto."]);
        exit;
    }

    error_log("Foto salva em: $fotoFullPath");
}

// Prepara a consulta SQL
$sql = "UPDATE PROFISSIONAL 
        SET nome = ?, telefone = ?, email = ?, sexo = ?, dataNascimento = ?, 
            contaBancaria_titular = ?, contaBancaria_cpf = ?, contaBancaria_banco = ?, 
            contaBancaria_tipo = ?, contaBancaria_agencia = ?, contaBancaria_numero = ?";
if ($fotoPath) {
    $sql .= ", foto = ?";
}
$sql .= " WHERE id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Erro ao preparar consulta: " . $conn->error);
    echo json_encode(["success" => false, "message" => "Erro ao preparar a consulta."]);
    exit;
}

// Liga os parâmetros
if ($fotoPath) {
    $stmt->bind_param("ssssssssssssi", $nome, $telefone, $email, $sexo, $dataNascimento, $titular, $cpf, $banco, $tipo, $agencia, $numeroConta, $fotoPath, $id);
} else {
  $stmt->bind_param("sssssssssssi", $nome, $telefone, $email, $sexo, $dataNascimento, $titular, $cpf, $banco, $tipo, $agencia, $numeroConta, $id);

}

// Executa a consulta
if ($stmt->execute()) {
    error_log("Profissional atualizado com sucesso: ID $id");
    echo json_encode(["success" => true, "message" => "Profissional atualizado com sucesso."]);
} else {
    error_log("Erro ao atualizar profissional: " . $stmt->error);
    echo json_encode(["success" => false, "message" => "Erro ao atualizar o profissional."]);
}

// Fecha a declaração e a conexão
$stmt->close();
$conn->close();
?>
