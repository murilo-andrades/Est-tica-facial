<?php
session_start();
require 'id_db.php'; // Conexão com o banco

$orderCode = $_GET['order_code'] ?? null;

if (!$orderCode) {
    echo json_encode(["success" => false, "error" => "Código do pedido não fornecido."]);
    exit;
}

$stmt = $conn->prepare("
    SELECT status, selected_date, selected_time 
    FROM pending_payments 
    WHERE order_code = ?
");
$stmt->bind_param("s", $orderCode);
$stmt->execute();
$stmt->bind_result($status, $selectedDate, $selectedTime);
$stmt->fetch();
$stmt->close();

if ($status === "paid") {
    echo json_encode([
        "success" => true,
        "status" => $status,
        "date" => $selectedDate,
        "time" => $selectedTime
    ]);
} else {
    echo json_encode(["success" => true, "status" => $status]);
}