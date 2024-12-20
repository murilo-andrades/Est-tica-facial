<?php
require 'id_db.php';
header('Content-Type: application/json');

$professionalId = $_GET['professionalId'] ?? null;
if (!$professionalId) {
    echo json_encode(["success" => false, "message" => "ID do profissional não fornecido."]);
    exit;
}

// Função para traduzir os dias da semana
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

// Função para traduzir os meses
function traduzirMes($mesIngles) {
    $meses = [
        'Jan' => 'Jan',
        'Feb' => 'Fev',
        'Mar' => 'Mar',
        'Apr' => 'Abr',
        'May' => 'Mai',
        'Jun' => 'Jun',
        'Jul' => 'Jul',
        'Aug' => 'Ago',
        'Sep' => 'Set',
        'Oct' => 'Out',
        'Nov' => 'Nov',
        'Dec' => 'Dez',
    ];
    return $meses[$mesIngles] ?? $mesIngles;
}

// Configura o intervalo de datas
$hoje = new DateTime();
$limite = new DateTime('+3 weeks');
$datasDisponiveis = [];

while ($hoje <= $limite) {
    $diaSemana = traduzirDiaSemana($hoje->format('l')); // Traduz o dia da semana
    $sql = "SELECT * FROM HORARIO_FUNCIONAMENTO WHERE status = 'ABERTO' AND dia = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $diaSemana);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Traduz o mês para PT-BR
        $mesPtBr = traduzirMes($hoje->format('M'));

        $datasDisponiveis[] = [
            "value" => $hoje->format('Y-m-d'),
            "display" => $hoje->format('d') . ' ' . $mesPtBr // Exibe o dia e o mês em PT-BR
        ];
    }

    $hoje->modify('+1 day'); // Incrementa para o próximo dia
}

echo json_encode(["success" => true, "dates" => $datasDisponiveis]);
?>
