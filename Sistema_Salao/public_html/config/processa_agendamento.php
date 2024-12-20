<?php
session_start();
require 'id_db.php';

header('Content-Type: application/json');

// Captura os dados enviados via POST
$serviceId = $_POST['serviceId'] ?? null;
$selectedDate = $_POST['selectedDate'] ?? null;
$selectedTime = $_POST['selectedTime'] ?? null;
$professionalId = $_POST['professionalId'] ?? null;
$orderCode = $_POST['orderCode'] ?? null;
$status = $_POST['status'] ?? null;
$amount = $_POST['amount'] ?? null;

error_log("Dados recebidos para processa_agendamento:");
error_log("serviceId: $serviceId, selectedDate: $selectedDate, selectedTime: $selectedTime");
error_log("professionalId: $professionalId, orderCode: $orderCode, status: $status, amount: $amount");

// Validação básica
if (!$serviceId || !$selectedDate || !$selectedTime || !$professionalId || !$orderCode || $status !== 'paid') {
    die("Erro: Dados incompletos ou inválidos.");
}

// Combina a data e o horário para formar um valor DATETIME
$agendamentoData = "$selectedDate $selectedTime";

// Verifica se o horário está livre
$stmtVerifica = $conn->prepare("
    SELECT id
    FROM AGENDAMENTO
    WHERE profissionalId = ? AND data = ?
");
$stmtVerifica->bind_param("is", $professionalId, $agendamentoData);
$stmtVerifica->execute();
$resultVerifica = $stmtVerifica->get_result();

if ($resultVerifica->num_rows > 0) {
    die("Erro: Este horário já foi reservado.");
}

// Insere o agendamento no banco de dados
$stmt = $conn->prepare("
    INSERT INTO AGENDAMENTO (clienteId, servicoId, profissionalId, data, valor, dataCadastro)
    VALUES (?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param("iiisd", $_SESSION['user_id'], $serviceId, $professionalId, $agendamentoData, $amount);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Agendamento realizado com sucesso."]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao realizar agendamento."]);
}

$stmt->close();
$conn->close();
