<?php
require 'restricted/verificar_login.php'; // Inclua o script de verificação
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="/src/style.css">
  <title>Painel</title>
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
      $configData = json_decode(file_get_contents("https://painel.espaconatalialetis.com.br/config/get_configuracao.php"), true);
      $config = $configData['config'] ?? [];
      $logoPath = !empty($config['logo_salao']) 
                  ? "https://espaconatalialetis.com.br/" . ltrim($config['logo_salao'], '/')
                  : '';
      ?>
      <div class="logo-container">
        <a href="/painel.php">
          <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo do Salão">
        </a>
      </div>
      <nav>
        <ul>
          <li><a href="/pages/agendamentos/agendamento.php"><i class="fa-solid fa-calendar-days"></i> Agendamentos</a></li>
          <li><a href="/pages/clientes/clientes.php"><i class="fa-solid fa-users"></i> Clientes</a></li>
          <li><a href="/pages/edit_config/edit_config.php"><i class="fa-solid fa-store"></i> Salão</a></li>
          <li><a href="/pages/horario/horario.php"><i class="fa-regular fa-clock"></i> Horários</a></li>
          <li class="group-title"><i class="fa-solid fa-user-check"></i> Profissionais</li>
          <li><a href="/pages/profissional/profissional.php"><i class="fa-solid fa-plus"></i> Incluir Profissionais</a></li>
          <li><a href="/pages/profissional/visualizar_profissional.php"><i class="fa-solid fa-eye"></i> Visualizar Profissionais</a></li>
          <li class="group-title"><i class="fa-solid fa-briefcase"></i> Serviços</li>
          <li><a href="/pages/servico/servicos.php"><i class="fa-solid fa-plus"></i> Incluir Serviços</a></li>
          <li><a href="/pages/servico/visualizar_servico.php"><i class="fa-solid fa-eye"></i> Visualizar Serviços</a></li>
          <li><a href="/config/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Sair</a></li>
        </ul>
      </nav>
    </aside>

    <!-- Conteúdo Principal -->
    <div class="main-content">
      <header class="header">
        <div class="user-profile">
          <span>Bem-vindo ao painel <?= htmlspecialchars($userNome) ?></span>
        </div>
        <span><?= htmlspecialchars($config['nome_salao']) ?></span>
      </header>
      <section class="content">
        <h1>Bem-vindo ao Painel Administrativo</h1>
        <p style="text-align: center;">Selecione uma opção no menu para começar.</p>
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
