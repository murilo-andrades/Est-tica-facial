<?php
header('Content-Type: application/json');
require '../config/id_db.php'; // Conexão com o banco

// Lê o corpo da requisição do webhook
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log para depuração
// error_log("Dados recebidos no webhook:");
// error_log(print_r($data, true));

// Função para enviar e-mail
function sendEmail($to, $subject, $message) {
    $headers = "From: salao@espaconatalialetis.com.br" . "\r\n" .
               "Reply-To: natalialetis09@gmail.com" . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    if (mail($to, $subject, $message, $headers)) {
        // error_log("E-mail enviado para: $to");
    } else {
        error_log("Erro ao enviar e-mail para: $to");
    }
}

// Verifica se o pagamento foi concluído
if (isset($data['data']['status']) && $data['data']['status'] === 'paid') {
    $orderCode = $data['data']['code'] ?? null;

    if (!$orderCode) {
        error_log("Erro: Código do pedido não encontrado no webhook.");
        http_response_code(400); // Bad Request
        exit;
    }

    // Atualiza o status na tabela de pagamentos
    $stmt = $conn->prepare("
        UPDATE pending_payments
        SET status = 'paid'
        WHERE order_code = ? AND status = 'pending'
    ");

    if (!$stmt) {
        error_log("Erro ao preparar a consulta: " . $conn->error);
        http_response_code(500); // Internal Server Error
        exit;
    }

    $stmt->bind_param("s", $orderCode);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Log de atualização bem-sucedida
        // error_log("Status do pagamento atualizado para 'paid' no pedido: $orderCode");

        // Insere na tabela de agendamento
        $scheduleStmt = $conn->prepare("
            INSERT INTO AGENDAMENTO (clienteId, profissionalId, servicoId, data, valor, transactionId, dataCadastro)
            SELECT client_id, professional_id, service_id, CONCAT(selected_date, ' ', selected_time), service_price / 100, order_code, NOW()
            FROM pending_payments
            WHERE order_code = ?
        ");

        if (!$scheduleStmt) {
            error_log("Erro ao preparar a consulta de agendamento: " . $conn->error);
            http_response_code(500);
            exit;
        }

        $scheduleStmt->bind_param("s", $orderCode);
        $scheduleStmt->execute();

        if ($scheduleStmt->affected_rows > 0) {
             //error_log("Agendamento criado com sucesso para o pedido: $orderCode");

            // Busca o cliente e o e-mail do cliente
            $clientQuery = $conn->prepare("
                SELECT c.email, p.selected_date, p.selected_time, p.service_price
                FROM CLIENTE c
                JOIN pending_payments p ON c.id = p.client_id
                WHERE p.order_code = ?
            ");

            if ($clientQuery) {
                $clientQuery->bind_param("s", $orderCode);
                $clientQuery->execute();
                $clientQuery->bind_result($clientEmail, $selectedDate, $selectedTime, $servicePrice);

                if ($clientQuery->fetch()) {
                    // Envia o e-mail para o cliente
                    $subjectClient = "Agendamento";
                    // Formata a hora para exibir apenas HH:mm
                    $timeFormatted = DateTime::createFromFormat('H:i:s', $selectedTime)->format('H:i');

                    // Formata a data para o formato BR (dd/mm/yyyy)
                    $dateFormatted = DateTime::createFromFormat('Y-m-d', $selectedDate)->format('d/m/Y');

                    // Mensagem do cliente
                    $messageClient = "
                        Olá,
                        
                        Seu agendamento foi confirmado.
                        Serviço: R$ " . number_format($servicePrice / 100, 2, ',', '.') . "
                        Data: $dateFormatted
                        Hora: $timeFormatted

                        Obrigado por escolher nosso salão!
                        Não responder este e-mail.
                    ";

                    sendEmail($clientEmail, $subjectClient, $messageClient);

                    // Envia o e-mail fixo para o salão
                    $salonEmail = "natalialetis09@gmail.com";
                    $subjectSalon = "Novo Agendamento Confirmado";
                    $messageSalon = "
                        Novo agendamento confirmado:
                        
                        Cliente: $clientEmail
                        Serviço: R$ " . number_format($servicePrice / 100, 2, ',', '.') . "
                        Data: $selectedDate
                        Hora: $selectedTime
                    ";
                    sendEmail($salonEmail, $subjectSalon, $messageSalon);
                }

                $clientQuery->close();
            } else {
                error_log("Erro ao buscar dados do cliente: " . $conn->error);
            }
        } else {
            error_log("Erro ao criar agendamento para o pedido: $orderCode");
        }

        $scheduleStmt->close();
    } else {
        error_log("Nenhum pedido foi atualizado. Pedido já processado ou código inválido: $orderCode");
    }

    $stmt->close();
} else {
    error_log("Pagamento não concluído ou estrutura inválida.");
}

// Retorna 200 para a Pagar.me
http_response_code(200);
exit;
