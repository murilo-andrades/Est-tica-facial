<?php
require '../../restricted/verificar_login.php'; // Inclua o script de verificação
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Visualizar Profissionais</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="/src/style.css">
  <style>
    .profile-photo {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
    }
  </style>
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
      <div class="table-content">
        <h1>Profissionais Cadastrados</h1>
        <div class="table-scroll">
        <table class="table-profissional">
          <thead>
            <tr>
              <th>Foto</th>
              <th>ID</th>
              <th>Nome</th>
              <th>Telefone</th>
              <th>E-mail</th>
              <th>Ações</th>
            </tr>
          </thead>

          <tbody id="profissionais-tbody"> 
          <?php
          include '../../config/id_db.php';
            $sql = "SELECT id, nome, telefone, email, foto FROM PROFISSIONAL";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $fotoPath = !empty($row['foto']) ? $row['foto'] : '/src/fotos/default.jpg';
                    $fotoPath = htmlspecialchars($fotoPath);
                    echo "<tr data-id='{$row['id']}'>
                            <td>
                                <img src='https://espaconatalialetis.com.br{$fotoPath}' 
                                    alt='Foto de {$row['nome']}' 
                                    class='profile-photo' />
                            </td>
                            <td>{$row['id']}</td>
                            <td>{$row['nome']}</td>
                            <td>{$row['telefone']}</td>
                            <td>{$row['email']}</td>
                            <td id='td-btn'>
                                <button class='btn-edit' onclick='openEditModal({$row['id']})'>Editar</button>
                                <button class='btn-delete' onclick='deleteProfissional({$row['id']})'>Excluir</button>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>Nenhum profissional cadastrado.</td></tr>";
            }
          ?>

          </tbody>
        </table>
        </div>
        </div>
      </section>
      

    </div>

    <!-- Modal de Edição -->
    <div id="edit-modal" class="modal" style="display: none;">
      <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Editar Profissional</h2>
        <form id="edit-form">
          <input type="hidden" id="edit-id">
          <div class="form-group">
            <label for="edit-nome">Nome:</label>
            <input type="text" id="edit-nome" name="nome" required>
          </div>
          <div class="form-group">
            <label for="edit-telefone">Telefone:</label>
            <input type="text" id="edit-telefone" name="telefone" required>
          </div>
          <div class="form-group">
            <label for="edit-email">E-mail:</label>
            <input type="email" id="edit-email" name="email" required>
          </div>
          <div class="form-group">
            <label for="edit-sexo">Sexo:</label>
            <select id="edit-sexo" name="sexo" required>
              <option value="M">Masculino</option>
              <option value="F">Feminino</option>
            </select>
          </div>
          <div class="form-group">
            <label for="edit-data-nascimento">Data de Nascimento:</label>
            <input type="date" id="edit-data-nascimento" name="data_nascimento" required>
          </div>
          <div class="form-group">
            <label for="edit-titular">Titular:</label>
            <input type="text" id="edit-titular" name="titular" required>
          </div>
          <div class="form-group">
            <label for="edit-cpf">CPF:</label>
            <input type="text" id="edit-cpf" name="cpf" required>
          </div>
          <div class="form-group">
            <label for="edit-banco">Banco:</label>
            <input type="text" id="edit-banco" name="banco" required>
          </div>
          <div class="form-group">
            <label for="edit-tipo">Tipo de Conta:</label>
            <select id="edit-tipo" name="tipo" required>
              <option value="Corrente">Corrente</option>
              <option value="Poupança">Poupança</option>
            </select>
          </div>
          <div class="form-group">
            <label for="edit-agencia">Agência:</label>
            <input type="text" id="edit-agencia" name="agencia" required>
          </div>
          <div class="form-group">
            <label for="edit-numero-conta">Número da Conta:</label>
            <input type="text" id="edit-numero-conta" name="numero_conta" required>
          </div>
          <div class="form-group">
            <label for="edit-foto">Foto do Serviço:</label>
            <input type="file" id="edit-foto" name="foto" accept="image/*">
          </div>

          <div class="button-container">
              <button type="button" onclick="saveEdit()">Salvar</button>
            </div>
        </form>
      </div>
    </div>
  </div>
  <script src="/pages/profissional/profissional.js"></script>
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
