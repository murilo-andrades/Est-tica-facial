<?php
require '../../restricted/verificar_login.php';
require '../../config/id_db.php'; // Inclui o arquivo com a conexão ao banco

// Inicializa os valores padrão como vazios
$config = [
    "nome_salao" => "Nome do Salão", // Nome padrão caso o banco esteja vazio
    "foto_salao" => "",
    "logo_salao" => "",
    "email" => "",
    "telefone" => "",
    "endereco_cidade" => "",
    "endereco_uf" => "",
    "endereco_cep" => "",
    "endereco_numero" => "",
    "endereco_pais" => ""
];

// Consulta os dados da tabela CONFIGURACAO
$sql = "SELECT * FROM CONFIGURACAO LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $config = $result->fetch_assoc(); // Preenche com os dados do banco
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Configuração</title>
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
        <h1>Editar Salão</h1>
        <form action="/config/processa_configuracao.php" method="POST" class="form-container" enctype="multipart/form-data">
          <!-- Nome do Salão -->
          <div class="form-group">
            <label for="nome_salao">Nome do Salão:</label>
            <input type="text" id="nome_salao" name="nome_salao" value="<?= htmlspecialchars($config['nome_salao']); ?>" required>
          </div>

          <!-- Logo do Salão -->
          <div class="form-group">
            <label for="logo_salao">Logo do Salão:</label>
            <input type="file" id="logo_salao" name="logo_salao" accept="image/*">
            <p>Logo Salão Atual: </p>
            <?php if (!empty($config['logo_salao'])): ?>
            <img src="<?= htmlspecialchars('https://espaconatalialetis.com.br/src/fotos/' . basename($config['logo_salao'])); ?>" alt="Logo" style="height: 150px; border-radius: 10px;">
            <?php endif; ?>
          </div>


          <!-- Foto do Salão -->
          <div class="form-group">
            <label for="foto_salao">Capa Salão:</label>
            <input type="file" id="foto_salao" name="foto_salao" accept="image/*">
            <p>Capa Salão Atual:</p>
            <?php if (!empty($config['foto_salao'])): ?>
            <img src="<?= htmlspecialchars('https://espaconatalialetis.com.br/src/fotos/' . basename($config['foto_salao'])); ?>" alt="Capa" style="height: 150px; border-radius: 10px;">

            <?php endif; ?>
          </div>

          <!-- E-mail -->
          <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($config['email']); ?>" required>
          </div>

          <!-- Telefone -->
          <div class="form-group">
            <label for="telefone">Telefone:</label>
            <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($config['telefone']); ?>" required>
          </div>

          <!-- Endereço -->
          <h2>Endereço</h2>
          <div class="form-group">
            <label for="endereco_cidade">Rua, Bairro e Cidade:</label>
            <input type="text" id="endereco_cidade" name="endereco_cidade" value="<?= htmlspecialchars($config['endereco_cidade']); ?>" required>
          </div>
          <div class="form-group">
            <label for="endereco_uf">UF:</label>
            <input type="text" id="endereco_uf" name="endereco_uf" value="<?= htmlspecialchars($config['endereco_uf']); ?>" maxlength="2" required>
          </div>
          <div class="form-group">
            <label for="endereco_cep">CEP:</label>
            <input type="text" id="endereco_cep" name="endereco_cep" value="<?= htmlspecialchars($config['endereco_cep']); ?>" required>
          </div>
          <div class="form-group">
            <label for="endereco_numero">Número:</label>
            <input type="text" id="endereco_numero" name="endereco_numero" value="<?= htmlspecialchars($config['endereco_numero']); ?>" required>
          </div>
          <div class="form-group">
            <label for="endereco_pais">País:</label>
            <input type="text" id="endereco_pais" name="endereco_pais" value="<?= htmlspecialchars($config['endereco_pais']); ?>" required>
          </div>

          <!-- Botão Salvar -->
          <div class="button-container">
            <button type="submit">Salvar Configuração</button>
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
