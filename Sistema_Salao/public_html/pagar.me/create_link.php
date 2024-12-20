<?php
session_start();

require '../config/id_db.php'; // Conexão com o banco

$servicePrice = $_POST['servicePrice'] * 100; // Valor em centavos
$serviceId = $_POST['serviceId'];
$serviceTitle = "Serviço $serviceId"; // Nome genérico do serviço
$professionalId = $_POST['professionalId'];
$selectedDate = $_POST['selectedDate'];
$selectedTime = $_POST['selectedTime'];
$clientId = $_SESSION['user_id']; // Pega o ID do cliente da sessão

// Validações básicas
if (!$servicePrice || !$serviceId || !$professionalId || !$selectedDate || !$selectedTime || !$clientId) {
    echo json_encode(["success" => false, "error" => "Dados incompletos fornecidos."]);
    exit;
}

$status = 'pending'; // Status inicial
$createdAt = date('Y-m-d H:i:s'); // Timestamp atual

// Combina data e hora para o agendamento
$datetime = "$selectedDate $selectedTime";

// Criação do link de pagamento via API Pagar.me
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://sdx-api.pagar.me/core/v5/paymentlinks",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
        "is_building" => false,
        "type" => "order",
        "name" => $serviceTitle,
        "payment_settings" => [
            "accepted_payment_methods" => ["credit_card", "pix"],
            "credit_card_settings" => [
                "operation_type" => "auth_and_capture",
                "installments_setup" => [
                    "interest_type" => "simple",
                    "interest_rate" => 0,
                    "max_installments" => 1,
                    "amount" => $servicePrice
                ]
            ],
            "pix_settings" => [
                "expires_in" => 3600
            ]
        ],
        "cart_settings" => [
            "items" => [
                [
                    "amount" => $servicePrice,
                    "name" => $serviceTitle,
                    "default_quantity" => 1
                ]
            ]
        ]
    ]),
    CURLOPT_HTTPHEADER => [
        "accept: application/json",
        "authorization: Basic c2tfdGVzdF8yYzI5NjM4N2E3MTA0MDdjOWU0MjNjMzI4OGZjNjhjOTo=",
        "content-type: application/json"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    echo json_encode(["success" => false, "error" => $err]);
    exit;
}

$responseData = json_decode($response, true);
if (isset($responseData['id'])) {
    $orderCode = $responseData['id']; // Captura o código retornado pela API
    $paymentLink = $responseData['url']; // Captura o link de pagamento

    // Insere os dados no banco
    $stmt = $conn->prepare("
        INSERT INTO pending_payments (service_id, professional_id, selected_date, selected_time, service_price, order_code, status, created_at, client_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "Erro ao preparar a consulta: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("iissdssss", $serviceId, $professionalId, $selectedDate, $selectedTime, $servicePrice, $orderCode, $status, $createdAt, $clientId);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "error" => "Erro ao salvar no banco: " . $stmt->error]);
        exit;
    }

    $stmt->close();

    // Inclui o orderCode na resposta
    echo json_encode([
        "success" => true, 
        "paymentLink" => $paymentLink, 
        "orderCode" => $orderCode
    ]);
} else {
    echo json_encode(["success" => false, "error" => "Erro ao gerar link de pagamento. Resposta da API inválida."]);
}

