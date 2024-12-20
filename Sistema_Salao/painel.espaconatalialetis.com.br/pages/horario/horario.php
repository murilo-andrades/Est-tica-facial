<?php
require '../../restricted/verificar_login.php'; // Inclua o script de verificação
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Horários</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="/src/style.css">
</head>

<body>
  <!-- Botão Hamburguer -->
  <button class="menu-toggle" onclick="toggleSidebar()">
    <i class="fa-solid fa-bars"></i>
  </button>
    <div class="admin-panel">
        <!-- Menu Lateral -->
    <aside class="sidebar">
    <?php
      // Busca os dados de configuração usando o arquivo get_configuracao.php
      $configData = json_decode(file_get_contents("https://painel.espaconatalialetis.com.br/config/get_configuracao.php"), true);

      // Verifica se os dados foram carregados com sucesso
      $config = $configData['config'] ?? [];

      // Exibir a logo
      if (!empty($config['logo_salao'])) {
          // Inclui o domínio antes do caminho retornado pelo banco de dados
          $logoPath = "https://espaconatalialetis.com.br/" . ltrim($config['logo_salao'], '/');
          $logoPath = htmlspecialchars($logoPath);
      }
    ?>

    <div class="logo-container">
        <a href="/painel.php">
        <img src="<?= $logoPath ?>" alt="Logo do Salão">
     </a>
    </div>
    <nav>
  <ul>
    <!-- Agendamentos -->
    <li>
      <a href="/pages/agendamentos/agendamento.php">
        <i class="fa-solid fa-calendar-days"></i>
        Agendamentos
      </a>
    </li>

    <!-- Clientes -->
    <li>
      <a href="/pages/clientes/clientes.php">
        <i class="fa-solid fa-users"></i>
        Clientes
      </a>
    </li>

    <!-- Salão -->
    <li>
      <a href="/pages/edit_config/edit_config.php">
        <i class="fa-solid fa-store"></i>
        Salão
      </a>
    </li>

    <!-- Horários -->
    <li>
      <a href="/pages/horario/horario.php">
        <i class="fa-regular fa-clock"></i>
        Horários
      </a>
    </li>

    <!-- Grupo Profissionais -->
    <li class="group-title">
      <i class="fa-solid fa-user-check"></i> Profissionais
    </li>
    <li>
      <a href="/pages/profissional/profissional.php">
        <i class="fa-solid fa-plus"></i>
        Incluir Profissionais
      </a>
    </li>
    <li>
      <a href="/pages/profissional/visualizar_profissional.php">
        <i class="fa-solid fa-eye"></i>
        Visualizar Profissionais
      </a>
    </li>

    <!-- Grupo Serviços -->
    <li class="group-title">
      <i class="fa-solid fa-briefcase"></i> Serviços
    </li>
    <li>
      <a href="/pages/servico/servicos.php">
        <i class="fa-solid fa-plus"></i>
        Incluir Serviços
      </a>
    </li>
    <li>
      <a href="/pages/servico/visualizar_servico.php">
        <i class="fa-solid fa-eye"></i>
        Visualizar Serviços
      </a>
    </li>
    <li>
      <a href="/config/logout.php">
      <i class="fa-solid fa-right-from-bracket" style="color: #ffffff;"></i>
        Sair
      </a>
    </li>
  </ul>
</nav>
    </aside>
    <!-- Conteúdo Principal -->
    <div class="main-content">
      <!-- Cabeçalho -->
      <header class="header">
      <div class="user-profile">
              <span>
                  <?php
                  // Mensagem de boas-vindas com o nome do usuário
                  echo "Bem-vindo ao painel " . htmlspecialchars($userNome);
                  ?>
              </span>
          </div>
          <span>
              <?php
              // Nome do salão
              echo htmlspecialchars($config['nome_salao']);
              ?>
          </span> 
      </header>

            <section class="content">
                <h1>Configurar Horários de Funcionamento</h1>
                <form action="/config/processa_horarios.php" method="POST" class="form-container">
                    <?php
                    $dias = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];

                    require '../../config/id_db.php'; // Inclui o arquivo com a conexão ao banco

                    foreach ($dias as $dia) {
                        // Busca os horários atuais para cada dia
                        $stmt = $conn->prepare("SELECT inicio, fim, status FROM HORARIO_FUNCIONAMENTO WHERE dia = ?");
                        $stmt->bind_param("s", $dia);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $horario = $result->fetch_assoc();

                        $inicio = $horario['inicio'] ?? '';
                        $fim = $horario['fim'] ?? '';
                        $status = $horario['status'] ?? 'ABERTO';

                        echo "
                            <div class='form-group'>
                                <label>$dia</label>
                                <select name='status[$dia]' class='status-select' data-dia='$dia'>
                                    <option value='ABERTO' " . ($status == 'ABERTO' ? 'selected' : '') . ">Aberto</option>
                                    <option value='FECHADO' " . ($status == 'FECHADO' ? 'selected' : '') . ">Fechado</option>
                                </select>
                                <label>Início:</label>
                                <input type='time' name='inicio[$dia]' id='inicio-$dia' value='$inicio' " . ($status == 'FECHADO' ? 'disabled' : '') . " required>
                                <label>Fim:</label>
                                <input type='time' name='fim[$dia]' id='fim-$dia' value='$fim' " . ($status == 'FECHADO' ? 'disabled' : '') . " required>
                            </div>
                        ";
                    }
                    ?>
                    <div class="button-container">
                        <button type="submit" class="btn-confirm">Salvar Configurações</button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <script>
        // Adiciona eventos para ativar/desativar campos de horário
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function () {
                const dia = this.getAttribute('data-dia');
                const inicioField = document.getElementById(`inicio-${dia}`);
                const fimField = document.getElementById(`fim-${dia}`);

                if (this.value === 'FECHADO') {
                    inicioField.value = ''; // Limpa o campo
                    fimField.value = ''; // Limpa o campo
                    inicioField.disabled = true; // Desativa o campo
                    fimField.disabled = true; // Desativa o campo
                } else {
                    inicioField.disabled = false; // Ativa o campo
                    fimField.disabled = false; // Ativa o campo
                }
            });
        });
    </script>
    <script>
    // Alterna o estado do menu lateral
    function toggleSidebar() {
      const sidebar = document.querySelector('.sidebar');
      const menuToggle = document.querySelector('.menu-toggle');
      
      if (sidebar && menuToggle) {
        sidebar.classList.toggle('open');
        menuToggle.classList.toggle('open');
      }
    }

    // Fecha o menu ao clicar fora dele
    window.addEventListener('click', function (event) {
      const sidebar = document.querySelector('.sidebar');
      const menuToggle = document.querySelector('.menu-toggle');
      
      if (sidebar && menuToggle && !sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
        sidebar.classList.remove('open');
        menuToggle.classList.remove('open');
      }
    });
  </script>
</body>

</html>
