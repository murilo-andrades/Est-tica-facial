<?php
require 'id_db.php'; // Inclui o arquivo com a conexão ao banco

header('Content-Type: application/json');

// Recebe os dados enviados via POST
$id = $_POST['id'] ?? null;
$titulo = $_POST['titulo'] ?? null;
$preco = $_POST['preco'] ?? null;
$duracao = $_POST['duracao'] ?? null;
$descricao = $_POST['descricao'] ?? null;
$foto = $_FILES['foto'] ?? null;

// Validações básicas
if (!$id || !$titulo || !$preco || !$duracao || !$descricao) {
    echo json_encode(["success" => false, "message" => "Dados incompletos fornecidos."]);
    exit;
}

// Verifica se os valores são válidos
$id = (int)$id;
$titulo = trim($titulo);
$preco = (float)$preco;
$duracao = (int)$duracao;
$descricao = trim($descricao);

if (empty($titulo) || $preco <= 0 || $duracao <= 0 || empty($descricao)) {
    echo json_encode(["success" => false, "message" => "Dados inválidos fornecidos."]);
    exit;
}

// Atualiza o caminho da foto, se necessário
$fotoPath = null;

if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../../public_html/src/fotos/foto_servico/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Cria o diretório, se não existir
    }

    $fotoName = basename($foto['name']);
    $fotoPath = '/src/fotos/foto_servico/' . $fotoName;
    $fotoFullPath = $uploadDir . $fotoName;

    // Move o arquivo enviado para o diretório especificado
    if (!move_uploaded_file($foto['tmp_name'], $fotoFullPath)) {
        echo json_encode(["success" => false, "message" => "Erro ao fazer upload da foto."]);
        exit;
    }
}

// Prepara a consulta para atualizar o serviço
$sql = "UPDATE SERVICO SET titulo = ?, preco = ?, duracao = ?, descricao = ?";
if ($fotoPath) {
    $sql .= ", foto = ?";
}
$sql .= " WHERE id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Erro ao preparar a consulta."]);
    exit;
}

// Liga os parâmetros e executa a consulta
if ($fotoPath) {
    $stmt->bind_param("sdissi", $titulo, $preco, $duracao, $descricao, $fotoPath, $id);
} else {
    $stmt->bind_param("sdisi", $titulo, $preco, $duracao, $descricao, $id);
}

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Serviço atualizado com sucesso."]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao atualizar o serviço."]);
}

// Fecha a declaração e a conexão
$stmt->close();
$conn->close();
?>
