<?php
require '../../restricted/verificar_login.php'; // Inclua o script de verificação
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/src/style.css">
  <title>Painel - Agendamentos</title>
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
      // Configuração do salão
      $configData = json_decode(file_get_contents("https://painel.espaconatalialetis.com.br/config/get_configuracao.php"), true);
      $config = $configData['config'] ?? [];
      $logoPath = !empty($config['logo_salao'])
        ? "https://espaconatalialetis.com.br/" . ltrim($config['logo_salao'], '/')
        : '/src/img/default-logo.png';
      ?>
      <div class="logo-container">
        <a href="/painel.php">
          <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo do Salão">
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

      <!-- Área de Conteúdo -->
      <section class="content">
        <div class="table-content">
          <h1>Agendamentos Feitos</h1>
          <div class="table-scroll">
          <table class="table-profissional">
            <thead>
              <tr>
                <th>Cliente</th>
                <th>Serviço</th>
                <th>Profissional</th>
                <th>Data e Hora Agendada</th>
                <th>Valor</th>
                <th>Data Cadastro</th>
              </tr>
            </thead>
            <tbody>
            <?php
            require '../../config/id_db.php'; // Inclua seu arquivo de conexão com o banco de dados

            // Consulta os agendamentos com JOIN para buscar informações de cliente, serviço e profissional
            $sql = "
              SELECT 
                AG.id,
                CLI.nome AS cliente,
                SER.titulo AS servico,
                PRO.nome AS profissional,
                AG.data AS data_hora,
                AG.valor,
                AG.dataCadastro
              FROM AGENDAMENTO AG
              LEFT JOIN CLIENTE CLI ON AG.clienteId = CLI.id
              LEFT JOIN SERVICO SER ON AG.servicoId = SER.id
              LEFT JOIN PROFISSIONAL PRO ON AG.profissionalId = PRO.id
            ";

            $result = $conn->query($sql);

            // Verifica se existem registros
            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                // Formata a data de agendamento e a data de cadastro
                $dataAgendamento = date('d/m/Y H:i', strtotime($row['data_hora']));
                $dataCadastro = date('d/m/Y', strtotime($row['dataCadastro']));
                
                echo "<tr>
                        <td>" . htmlspecialchars($row['cliente'] ?? 'Não informado') . "</td>
                        <td>" . htmlspecialchars($row['servico']) . "</td>
                        <td>" . htmlspecialchars($row['profissional']) . "</td>
                        <td>" . htmlspecialchars($dataAgendamento) . "</td>
                        <td>R$ " . number_format($row['valor'], 2, ',', '.') . "</td>
                        <td>" . htmlspecialchars($dataCadastro) . "</td>
                      </tr>";
              }
            } else {
              echo "<tr><td colspan='6'>Nenhum agendamento cadastrado.</td></tr>";
            }

            $conn->close();
            ?>
          </tbody>
        </div>
          </table>
        </div>
      </section>
    </div>
  </div>
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
