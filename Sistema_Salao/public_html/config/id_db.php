<?php
// id_db.php
$servername = "localhost";
$username = "espa0798_salao";
$password = "salao2024#@";
$dbname = "espa0798_EsteticaSalao";

// Criando conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificando conexão
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Erro na conexão com o banco de dados."]);
    exit;
}
?>
