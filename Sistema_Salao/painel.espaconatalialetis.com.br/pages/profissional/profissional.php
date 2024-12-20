<?php
require '../../restricted/verificar_login.php'; // Inclua o script de verificação
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastrar Profissional</title>
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

      <!-- Área de Conteúdo -->
      <section class="content">
        <h1>Cadastrar Profissional</h1>
        <!-- Atualizado: Formulário agora suporta upload de arquivos -->
        <form action="/config/processa_profissional.php" method="POST" class="form-container" enctype="multipart/form-data">
          <!-- Nome -->
          <div class="form-group">
            <label for="nome">Nome Completo:</label>
            <input type="text" id="nome" name="nome" required>
          </div>

          <!-- Foto -->
          <div class="form-group">
            <label for="foto">Foto do Profissional:</label>
            <input type="file" id="foto" name="foto" accept="image/*" required>
          </div>

          <!-- Telefone -->
          <div class="form-group">
            <label for="telefone">Telefone:</label>
            <input type="text" id="telefone" name="telefone" required>
          </div>

          <!-- E-mail -->
          <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>
          </div>

          <!-- Sexo -->
          <div class="form-group">
            <label for="sexo">Sexo:</label>
            <select id="sexo" name="sexo" required>
              <option value="M">Masculino</option>
              <option value="F">Feminino</option>
            </select>
          </div>

          <!-- Data de Nascimento -->
          <div class="form-group">
            <label for="data_nascimento">Data de Nascimento:</label>
            <input type="date" id="data_nascimento" name="data_nascimento" required>
          </div>

          <!-- Conta Bancária -->
          <h2>Informações Bancárias</h2>
          <div class="form-group">
            <label for="titular">Titular:</label>
            <input type="text" id="titular" name="titular" required>
          </div>
          <div class="form-group">
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" required>
          </div>
          <div class="form-group">
            <label for="banco">Banco:</label>
            <input type="text" id="banco" name="banco" required>
          </div>
          <div class="form-group">
            <label for="tipo">Tipo de Conta:</label>
            <select id="tipo" name="tipo" required>
              <option value="Corrente">Corrente</option>
              <option value="Poupança">Poupança</option>
            </select>
          </div>
          <div class="form-group">
            <label for="agencia">Agência:</label>
            <input type="text" id="agencia" name="agencia" required>
          </div>
          <div class="form-group">
            <label for="numero_conta">Número da Conta:</label>
            <input type="text" id="numero_conta" name="numero_conta" required>
          </div>

          <!-- Botão de Enviar -->
          <div class="button-container">
            <button type="submit">Cadastrar Profissional</button>
          </div>
        </form>
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
