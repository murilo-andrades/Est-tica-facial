<?php
require 'id_db.php';
header('Content-Type: application/json');

$professionalId = $_GET['professionalId'] ?? null;
$date = $_GET['date'] ?? null;

if (!$professionalId || !$date) {
    echo json_encode(["success" => false, "message" => "Dados insuficientes fornecidos."]);
    exit;
}

// Obter o dia da semana correspondente
$diaSemana = (new DateTime($date))->format('l');

// Traduzir o dia da semana (ex.: "Monday" => "Segunda-feira")
function traduzirDiaSemana($diaIngles) {
    $dias = [
        'Sunday' => 'Domingo',
        'Monday' => 'Segunda-feira',
        'Tuesday' => 'Terça-feira',
        'Wednesday' => 'Quarta-feira',
        'Thursday' => 'Quinta-feira',
        'Friday' => 'Sexta-feira',
        'Saturday' => 'Sábado',
    ];
    return $dias[$diaIngles] ?? null;
}
$diaSemanaTraduzido = traduzirDiaSemana($diaSemana);

// Verificar horários de funcionamento
$stmtFuncionamento = $conn->prepare("
    SELECT inicio, fim
    FROM HORARIO_FUNCIONAMENTO
    WHERE dia = ? AND status = 'ABERTO'
");
$stmtFuncionamento->bind_param("s", $diaSemanaTraduzido);
$stmtFuncionamento->execute();
$resultFuncionamento = $stmtFuncionamento->get_result();

if ($resultFuncionamento->num_rows === 0) {
    echo json_encode(["success" => true, "times" => []]); // Nenhum horário disponível
    exit;
}

$funcionamento = $resultFuncionamento->fetch_assoc();
$inicio = new DateTime($date . ' ' . $funcionamento['inicio']);
$fim = new DateTime($date . ' ' . $funcionamento['fim']);

// Obter horários já reservados
$stmtAgendados = $conn->prepare("
    SELECT TIME(data) as hora
    FROM AGENDAMENTO
    WHERE profissionalId = ? AND DATE(data) = ?
");
$stmtAgendados->bind_param("is", $professionalId, $date);
$stmtAgendados->execute();
$resultAgendados = $stmtAgendados->get_result();

$horariosAgendados = [];
while ($row = $resultAgendados->fetch_assoc()) {
    $horariosAgendados[] = (new DateTime($row['hora']))->format('H:i'); // Garante o mesmo formato
}

// Gerar horários disponíveis
$horariosDisponiveis = [];
while ($inicio < $fim) {
    $horarioAtual = $inicio->format('H:i');
    // Apenas adiciona o horário se não estiver na lista de agendados
    if (!in_array($horarioAtual, $horariosAgendados)) {
        $horariosDisponiveis[] = $horarioAtual;
    }
    $inicio->modify('+30 minutes'); // Incrementa 30 minutos
}

echo json_encode(["success" => true, "times" => $horariosDisponiveis]);
?>
