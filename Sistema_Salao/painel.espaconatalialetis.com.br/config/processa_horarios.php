<?php
require 'id_db.php'; // Inclui o arquivo com a conexão ao banco

// Verifica se o método da requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dias = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];

    foreach ($dias as $dia) {
        // Verifica se os dados foram enviados para este dia
        $status = $_POST['status'][$dia] ?? 'FECHADO'; // Padrão é 'FECHADO'
        $inicio = $_POST['inicio'][$dia] ?? '00:00:00'; // Valor padrão para horários fechados
        $fim = $_POST['fim'][$dia] ?? '00:00:00'; // Valor padrão para horários fechados

        // Verifica se já existe um registro para o dia
        $stmt = $conn->prepare("SELECT id FROM HORARIO_FUNCIONAMENTO WHERE dia = ?");
        $stmt->bind_param("s", $dia);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Atualiza os horários existentes
            $stmt = $conn->prepare("UPDATE HORARIO_FUNCIONAMENTO SET inicio = ?, fim = ?, status = ? WHERE dia = ?");
            $stmt->bind_param("ssss", $inicio, $fim, $status, $dia);
        } else {
            // Insere novos horários
            $stmt = $conn->prepare("INSERT INTO HORARIO_FUNCIONAMENTO (dia, inicio, fim, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $dia, $inicio, $fim, $status);
        }

        $stmt->execute();
    }
}

// Redireciona para a página de configuração com uma mensagem de sucesso
header("Location:/pages/horario/horario.php?success=1");
exit;
?>