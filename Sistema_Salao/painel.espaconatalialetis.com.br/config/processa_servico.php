<?php
require 'id_db.php'; // Inclui o arquivo com a conexão ao banco

// Recebe os dados do formulário
$titulo = $_POST['titulo'];
$preco = $_POST['preco'];
$duracao = $_POST['duracao']; // Campo adicionado
$descricao = $_POST['descricao'] ?? null; // Descrição opcional
$status = "ATIVO"; // Novo serviço sempre começa como ativo
$dataCadastro = date('Y-m-d');

// Diretórios físico e público
$diretorioFisico = __DIR__ . '/../../public_html/src/fotos/foto_servico/'; // Caminho físico
$diretorioPublico = '/src/fotos/foto_servico/'; // Caminho público

// Garante que o diretório físico exista
if (!is_dir($diretorioFisico)) {
    mkdir($diretorioFisico, 0777, true); // Cria o diretório, se não existir
}

// Processa o upload da foto
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    // Gera um nome único para evitar conflitos de arquivos
    $nomeArquivo = uniqid() . '-' . basename($_FILES['foto']['name']);
    $caminhoFisico = $diretorioFisico . $nomeArquivo; // Caminho físico completo no servidor
    $caminhoBanco = $diretorioPublico . $nomeArquivo; // Caminho público para armazenar no banco

    // Move o arquivo para o diretório físico
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminhoFisico)) {
        // Prepara a consulta para inserir os dados no banco
        $sql = "INSERT INTO SERVICO 
                (titulo, foto, preco, duracao, descricao, status, dataCadastro) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        // Prepara a declaração SQL
        $stmt = $conn->prepare($sql);

        // Liga os parâmetros da consulta
        $stmt->bind_param(
            "ssdssss", 
            $titulo, 
            $caminhoBanco, 
            $preco, 
            $duracao, 
            $descricao, 
            $status, 
            $dataCadastro
        );

        // Executa a consulta
        if ($stmt->execute()) {
            echo "<script>alert('Serviço cadastrado com sucesso!'); window.location.href='/pages/servico/servicos.php';</script>";
        } else {
            echo "Erro ao cadastrar serviço: " . $stmt->error;
        }

        // Fecha a declaração
        $stmt->close();
    } else {
        echo "<script>alert('Erro ao mover o arquivo. Verifique as permissões do diretório.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Erro ao fazer upload da foto. Código de erro: {$_FILES['foto']['error']}'); window.history.back();</script>";
}

// Fecha a conexão
$conn->close();
?>
