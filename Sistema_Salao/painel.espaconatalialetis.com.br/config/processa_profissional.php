<?php
require 'id_db.php'; // Inclui o arquivo com a conexão ao banco

// Recebe os dados do formulário
$nome = $_POST['nome'];
$telefone = $_POST['telefone'];
$email = $_POST['email'];
$sexo = $_POST['sexo'];
$dataNascimento = $_POST['data_nascimento'];
$titular = $_POST['titular'];
$cpf = $_POST['cpf'];
$banco = $_POST['banco'];
$tipo = $_POST['tipo'];
$agencia = $_POST['agencia'];
$numeroConta = $_POST['numero_conta'];

// Diretórios para salvar a foto
$diretorioFisico = __DIR__ . '/../../public_html/src/fotos/foto_profissional/'; // Caminho físico
$diretorioPublico = '/src/fotos/foto_profissional/'; // Caminho público

// Garante que o diretório físico exista
if (!is_dir($diretorioFisico)) {
    mkdir($diretorioFisico, 0777, true); // Cria o diretório, se não existir
}

// Processa o upload da foto
$fotoPath = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    // Gera um nome único para evitar conflitos de nomes de arquivos
    $fotoName = uniqid() . '-' . basename($_FILES['foto']['name']);
    $fotoFullPath = $diretorioFisico . $fotoName; // Caminho físico completo
    $fotoPath = $diretorioPublico . $fotoName; // Caminho público para o banco de dados

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $fotoFullPath)) {
        die("Erro ao fazer upload da foto.");
    }
}

// Prepara e executa a consulta
$sql = "INSERT INTO PROFISSIONAL 
        (nome, foto, telefone, email, sexo, dataNascimento, 
         contaBancaria_titular, contaBancaria_cpf, contaBancaria_banco, 
         contaBancaria_tipo, contaBancaria_agencia, contaBancaria_numero, dataCadastro) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssssss", $nome, $fotoPath, $telefone, $email, $sexo, $dataNascimento, 
                  $titular, $cpf, $banco, $tipo, $agencia, $numeroConta);

if ($stmt->execute()) {
    header("Location: ../pages/profissional/visualizar_profissional.php ");
} else {
    echo "Erro ao cadastrar profissional: " . $conn->error;
}

// Fecha a conexão
$stmt->close();
$conn->close();
?>
