<?php
require 'id_db.php';


// Captura a data
$data = $_GET['data'] ?? null;
if (!$data) {
    die(json_encode(['error' => 'Data não fornecida']));
}

// Determina o dia da semana
$diaSemana = date('l', strtotime($data));

// Traduz o dia para português
$diasSemana = [
    'Sunday' => 'Domingo',
    'Monday' => 'Segunda-feira',
    'Tuesday' => 'Terça-feira',
    'Wednesday' => 'Quarta-feira',
    'Thursday' => 'Quinta-feira',
    'Friday' => 'Sexta-feira',
    'Saturday' => 'Sábado'
];
$diaEmPortugues = $diasSemana[$diaSemana];

// Consulta os horários de funcionamento para o dia
$stmt = $conn->prepare("SELECT inicio, fim FROM HORARIO_FUNCIONAMENTO WHERE dia = ? AND status = 'ABERTO'");
$stmt->bind_param("s", $diaEmPortugues);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['horarios' => []]);
    exit;
}

$horario = $result->fetch_assoc();
$inicio = new DateTime($horario['inicio']);
$fim = new DateTime($horario['fim']);

$horarios = [];
while ($inicio < $fim) {
    $horarios[] = $inicio->format('H:i');
    $inicio->modify('+30 minutes'); // Intervalo de 30 minutos
}

echo json_encode(['horarios' => $horarios]);
?>
