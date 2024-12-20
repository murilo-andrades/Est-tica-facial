<?php
require '../../restricted/verificar_login.php'; // Inclua o script de verificação
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Visualizar Serviços</title>
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
      <div class="table-content">
        <h1>Serviços Cadastrados</h1>
        <div class="table-scroll">
        <table class="table-profissional">
          <thead>
            <tr>
              <th>Foto</th>
              <th>Serviço</th>
              <th>Preço</th>
              <th>Duração</th>
              <th>Descrição</th>
              <th>Status</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody id="servicos-tbody">
            <?php
            require '../../config/id_db.php'; // Inclui o arquivo com a conexão ao banco

            // Consulta os serviços cadastrados
            $sql = "SELECT id, titulo, preco, duracao, descricao, status, foto FROM SERVICO";

            $result = $conn->query($sql);

            // Verifica se existem registros
            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                // Define o caminho da foto do serviço
                $fotoPath = !empty($row['foto']) ? $row['foto'] : '/src/fotos/default.jpg'; 
                $fotoPath = htmlspecialchars($fotoPath);
            
                // Define o texto para o botão de status
                $statusText = $row['status'] === 'ATIVO' ? 'Inativar' : 'Ativar';
            
                // Exibe a linha da tabela com os dados do serviço
                echo "<tr data-id='{$row['id']}'>
                        <td><img src='https://espaconatalialetis.com.br{$fotoPath}' alt='Foto de {$row['titulo']}' class='profile-photo' /></td>
                        <td>{$row['titulo']}</td>
                        <td>R$ {$row['preco']}</td>
                        <td>{$row['duracao']} min</td>
                        <td>{$row['descricao']}</td>
                        <td class='status-cell'>{$row['status']}</td>
                        <td class='td-btn'>
                          <button class='btn-edit' onclick='openEditModal({$row['id']})'>Editar</button>
                          <button class='btn-delete' onclick='deleteServico({$row['id']})'>Excluir</button>
                          <button class='btn-status' onclick='toggleStatus({$row['id']}, this)'>{$statusText}</button>
                        </td>
                      </tr>";
            }
            } else {
              echo "<tr><td colspan='6'>Nenhum serviço cadastrado.</td></tr>";
            }

            $conn->close();
            ?>

            <div id="edit-modal" class="modal" style="display: none;">
              <div class="modal-content">
                <span class="close" onclick="closeEditModal()">&times;</span>
                <h2>Editar Serviço</h2>
                <form id="edit-form">
                  <!-- ID oculto -->
                  <input type="hidden" id="edit-id">

                  <!-- Título -->
                  <div class="form-group">
                    <label for="edit-titulo">Título:</label>
                    <input type="text" id="edit-titulo" name="titulo" required>
                  </div>

                  <!-- Preço -->
                  <div class="form-group">
                    <label for="edit-preco">Preço:</label>
                    <input type="number" id="edit-preco" name="preco" step="0.01" required>
                  </div>

                  <!-- Duração -->
                  <div class="form-group">
                    <label for="edit-duracao">Duração (em minutos):</label>
                    <input type="number" id="edit-duracao" name="duracao" required>
                  </div>

                  <!-- Descrição -->
                  <div class="form-group">
                    <label for="edit-descricao">Descrição:</label>
                    <textarea id="edit-descricao" name="descricao" rows="4" required></textarea>
                  </div>

                  <!-- Foto -->
                  <div class="form-group">
                    <label for="edit-foto">Foto do Serviço:</label>
                    <input type="file" id="edit-foto" name="foto" accept="image/*">
                  </div>

                  <!-- Botão para salvar -->
                  <div class="button-container">
                    <button type="button" onclick="saveEdit()">Salvar</button>
                  </div>
                </form>
              </div>
            </div>
          </tbody>
        </table>
        </div>
        </div>
      </section>
    </div>
  </div>

  <script src="/pages/servico/servicos.js"></script>

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
