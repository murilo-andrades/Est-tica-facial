<?php
session_start();
require 'config/id_db.php';

// Verifica se o usuário está logado
$isLoggedIn = isset($_SESSION['user_id']);

// Captura o ID do serviço
$serviceId = $_GET['id'] ?? null;

if ($serviceId) {
    // Busca os dados do serviço no banco de dados
    $stmt = $conn->prepare("SELECT titulo, preco, foto FROM SERVICO WHERE id = ?");
    $stmt->bind_param("i", $serviceId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $service = $result->fetch_assoc();
        $serviceTitle = $service['titulo'];
        $servicePrice = $service['preco'];
        $servicePhoto = $service['foto'] ?? '/src/img/default-service.jpg';
    } else {
        die("Serviço não encontrado.");
    }
} else {
    // Se o ID não foi fornecido, redirecionar ou mostrar uma mensagem amigável
    echo "<script>alert('ID do serviço não fornecido. Por favor, selecione um serviço para continuar.'); window.location.href='/servicos.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/src/css/agendar.css">
    <title>Agendar Serviço</title>
</head>

<body>

    <!-- Barra de navegação -->
    <div class="navbar">
        <div class="navbar-left">
            <!-- Saudação ao usuário -->
            <?php if (isset($_SESSION['user_name'])): ?>
                <span>Olá, <?= htmlspecialchars($_SESSION['user_name']); ?>!</span>
            <?php endif; ?>
        </div>
        <div class="navbar-right">
            <!-- Exibe botão de Login ou Logout -->
            <?php if ($isLoggedIn): ?>
                <form action="/config/logout.php" method="POST" style="display: inline;">
                    <button type="submit" class="btn-logout">Sair</button>
                </form>
            <?php else: ?>
                <button type="button" class="btn-login" onclick="openModal()">Login</button>
            <?php endif; ?>
        </div>
    </div>


    <div class="container">
        <!-- Serviço Selecionado -->
        <div class="service-header">
            <img src="<?= htmlspecialchars($servicePhoto); ?>" alt="<?= htmlspecialchars($serviceTitle); ?>" class="service-img">
            <div class="service-info">
                <h2><?= htmlspecialchars($serviceTitle); ?></h2>
                <p>Total: <span class="price">R$ <?= number_format((float)$servicePrice, 2, ',', '.'); ?></span></p>
            </div>
        </div>

        <!-- Seleção de Profissional -->
        <div class="section">
            <h3>Profissional</h3>
            <div id="professional-list">
                <?php
                // Consulta os profissionais no banco de dados
                $sql = "SELECT id, nome, foto FROM PROFISSIONAL";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $fotoPath = !empty($row['foto']) ? $row['foto'] : '/src/img/default-avatar.jpg';
                        echo "<div class='professional-item' onclick='selectProfessional({$row['id']}, \"{$row['nome']}\")'>
                                <img src='{$fotoPath}' alt='{$row['nome']}' class='professional-img'>
                                <span>{$row['nome']}</span>
                              </div>";
                    }
                } else {
                    echo "<p>Nenhum profissional cadastrado.</p>";
                }
                ?>
            </div>
        </div>

        <!-- Seleção de Data -->
        <div class="section" id="date-section" style="display: none;">
            <h3>Para quando você gostaria de agendar?</h3>
            <div class="date-selector" id="date-selector">
                <!-- As datas disponíveis serão carregadas dinamicamente via JS -->
            </div>
        </div>

        <!-- Seleção de Horário -->
        <div class="section" id="time-section" style="display: none;">
            <h3>Qual horário?</h3>
            <div class="time-selector" id="time-selector">
                <!-- Os horários disponíveis serão carregados dinamicamente via JS -->
            </div>
        </div>

       <!-- Modal de Login/Cadastro -->
        <div id="loginModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <div id="modal-header">
                    <h2 id="modalTitle">Login Necessário</h2>
                </div>

                <!-- Mensagem de erro ou sucesso -->
                <div id="modalMessage" style="color: red; margin-bottom: 10px;"></div>

                <!-- Formulário de Login -->
                <form id="loginForm" style="display: block;" onsubmit="return processLogin(event)">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>
                    
                    <label for="senha">Senha:</label>
                    <input type="password" name="senha" id="senha" required>
                    
                    <button type="submit">Login</button>
                    <p>Não tem uma conta? <a href="#" onclick="showRegisterForm()">Cadastre-se</a></p>
                </form>

                <!-- Formulário de Cadastro -->
                <form id="registerForm" style="display: none;" onsubmit="return processRegister(event)">
                    <label for="registerName">Nome:</label>
                    <input type="text" name="nome" id="registerName" required>
                    
                    <label for="registerEmail">Email:</label>
                    <input type="email" name="email" id="registerEmail" required>
                    
                    <label for="registerPassword">Senha:</label>
                    <input type="password" name="senha" id="registerPassword" required>
                    
                    <label for="registerPhone">Telefone:</label>
                    <input type="text" name="telefone" id="registerPhone">
                    
                    <button type="submit">Cadastrar</button>
                    <p>Já tem uma conta? <a href="#" onclick="showLoginForm()">Faça login</a></p>
                </form>
            </div>
        </div>

    
        <!-- FORM ANTIGO -->
         <!-- Botão Confirmar -->
        <!-- <form action="pagar.me/create_link.php" method="POST">
            <input type="hidden" name="serviceId" value="?= htmlspecialchars($serviceId) ?>">
            <input type="hidden" name="serviceTitle" value="?= htmlspecialchars($serviceTitle) ?>">
            <input type="hidden" name="servicePrice" value="?= htmlspecialchars($servicePrice) ?>">
            <input type="hidden" id="selectedProfessional" name="professionalId" value="">
            <input type="hidden" id="selectedDate" name="selectedDate" value="">
            <input type="hidden" id="selectedTime" name="selectedTime" value="">
            
            <div class="confirm-section" id="confirm-section" style="display: none;">
                ?php if ($isLoggedIn): ?>
                    <button type="submit" class="btn-confirm">Confirmar meu agendamento</button>
              ?php else: ?>
                    <button type="button" class="btn-confirm" onclick="openModal()">Confirmar meu agendamento</button>
                ?php endif; ?>
            </div>
        </form> -->



        <!-- Botão Confirmar -->
        <form id="paymentForm" method="POST">
            <input type="hidden" name="serviceId" value="<?= htmlspecialchars($serviceId) ?>">
            <input type="hidden" name="serviceTitle" value="<?= htmlspecialchars($serviceTitle) ?>">
            <input type="hidden" name="servicePrice" value="<?= htmlspecialchars($servicePrice) ?>"> <!-- Valor em centavos -->
            <input type="hidden" id="selectedProfessional" name="professionalId" value="">
            <input type="hidden" id="selectedDate" name="selectedDate" value="">
            <input type="hidden" id="selectedTime" name="selectedTime" value="">
            
            <div class="confirm-section" id="confirm-section" style="display: none;">
                <?php if ($isLoggedIn): ?>
                    <button type="button" id="generatePaymentLink" class="btn-confirm" onclick="initializePaymentLinkButton()">Confirmar meu agendamento</button>

                <?php else: ?>
                    <button type="button" class="btn-confirm" onclick="openModal()">Login Necessário</button>
                <?php endif; ?>
            </div>
        </form>
        
    </div>
    <!-- MODAL AGUARDANDO PAGAMENTO -->
         <!-- Modal -->
         <div id="paymentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 1000; justify-content: center; align-items: center; color: #fff;">
            <div style=" padding: 20px; border-radius: 20px; text-align: center; width: 300px;">
                <div id="modalContent">
                    <h3>Aguardando pagamento...</h3>
                    <p>
                        <img src="src/img_aplicacao/loading.com.png" alt="Carregando" style="width: 100px; height: 100px; animation: spin 1s linear infinite;">
                    </p>
                </div>
            </div>
        </div>
        <!-- CSS para o spinner -->
        <style>
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        </style>

    <script src="/src/js/agendar.js" ></script>     
</body>
</html>
