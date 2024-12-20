<?php
require 'id_db.php'; // Inclui o arquivo com a conexão ao banco

// Captura os dados enviados pelo formulário
$nomeSalao = $_POST['nome_salao'] ?? null;
$email = $_POST['email'] ?? null;
$telefone = $_POST['telefone'] ?? null;
$enderecoCidade = $_POST['endereco_cidade'] ?? null;
$enderecoUf = $_POST['endereco_uf'] ?? null;
$enderecoCep = $_POST['endereco_cep'] ?? null;
$enderecoNumero = $_POST['endereco_numero'] ?? null;
$enderecoPais = $_POST['endereco_pais'] ?? null;
$dataCadastro = date('Y-m-d');

// Processa uploads de fotos
function processUpload($inputName, $uploadDirPhysical, $uploadDirPublic)
{
    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
        // Gera um nome único para evitar conflitos
        $fileName = uniqid() . '-' . basename($_FILES[$inputName]['name']);
        $filePathPhysical = $uploadDirPhysical . $fileName; // Caminho físico no servidor
        $filePathPublic = $uploadDirPublic . $fileName;     // Caminho público para o banco de dados

        // Move o arquivo para o local físico
        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $filePathPhysical)) {
            return $filePathPublic; // Retorna o caminho público
        } else {
            die("Erro ao salvar o arquivo: " . htmlspecialchars($_FILES[$inputName]['name']));
        }
    }
    return null;
}

// Caminhos para upload de arquivos
$uploadDirPhysical = realpath(__DIR__ . '/../../public_html/src/fotos/') . '/'; // Caminho físico para salvar o arquivo
$uploadDirPublic = '/src/fotos/'; // Caminho público para armazenar no banco de dados

// Processa os uploads
$fotoSalao = processUpload('foto_salao', $uploadDirPhysical, $uploadDirPublic);
$logoSalao = processUpload('logo_salao', $uploadDirPhysical, $uploadDirPublic);

// Consulta se já existe um registro na tabela CONFIGURACAO
$sqlCheck = "SELECT id FROM CONFIGURACAO LIMIT 1";
$resultCheck = $conn->query($sqlCheck);

if ($resultCheck->num_rows > 0) {
    // Registro já existe, realizar UPDATE
    $configId = $resultCheck->fetch_assoc()['id'];

    // Monta a query dinamicamente
    $fields = [
        'nome_salao = ?',
        'email = ?',
        'telefone = ?',
        'endereco_cidade = ?',
        'endereco_uf = ?',
        'endereco_cep = ?',
        'endereco_numero = ?',
        'endereco_pais = ?',
    ];
    $params = [
        &$nomeSalao,
        &$email,
        &$telefone,
        &$enderecoCidade,
        &$enderecoUf,
        &$enderecoCep,
        &$enderecoNumero,
        &$enderecoPais,
    ];

    if ($fotoSalao) {
        $fields[] = 'foto_salao = ?';
        $params[] = &$fotoSalao;
    }
    if ($logoSalao) {
        $fields[] = 'logo_salao = ?';
        $params[] = &$logoSalao;
    }

    $fields = implode(', ', $fields);
    $sqlUpdate = "UPDATE CONFIGURACAO SET $fields WHERE id = ?";
    $params[] = &$configId;

    $stmt = $conn->prepare($sqlUpdate);
    $stmt->bind_param(str_repeat('s', count($params) - 1) . 'i', ...$params);

} else {
    // Não existe registro, realizar INSERT
    $sqlInsert = "INSERT INTO CONFIGURACAO 
                  (nome_salao, email, telefone, endereco_cidade, endereco_uf, endereco_cep, 
                   endereco_numero, endereco_pais, foto_salao, logo_salao, dataCadastro)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sqlInsert);
    $stmt->bind_param(
        "sssssssssss",
        $nomeSalao,
        $email,
        $telefone,
        $enderecoCidade,
        $enderecoUf,
        $enderecoCep,
        $enderecoNumero,
        $enderecoPais,
        $fotoSalao,
        $logoSalao,
        $dataCadastro
    );
}

// Executa a query
if ($stmt->execute()) {
    echo "<script>alert('Configuração salva com sucesso!'); window.location.href='/painel.php';</script>";
} else {
    echo "<script>alert('Erro ao salvar configuração: " . $stmt->error . "'); window.history.back();</script>";
}

// Fecha a conexão
$stmt->close();
$conn->close();
?>
