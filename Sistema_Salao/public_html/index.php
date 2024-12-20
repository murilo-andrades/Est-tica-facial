<?php
require 'config/id_db.php';


// Data e hora atuais
$diaAtual = date('l'); // Exemplo: "Monday", "Tuesday", etc.
$horaAtual = date('H:i:s');

// Traduz o dia para português
$dias = [
    'Sunday' => 'Domingo',
    'Monday' => 'Segunda-feira',
    'Tuesday' => 'Terça-feira',
    'Wednesday' => 'Quarta-feira',
    'Thursday' => 'Quinta-feira',
    'Friday' => 'Sexta-feira',
    'Saturday' => 'Sábado',
];
$diaEmPortugues = $dias[$diaAtual];

// Verifica se está no horário de funcionamento
$stmt = $conn->prepare("SELECT status, inicio, fim FROM HORARIO_FUNCIONAMENTO WHERE dia = ?");
$stmt->bind_param("s", $diaEmPortugues);
$stmt->execute();
$result = $stmt->get_result();

$statusSalao = "FECHADO"; // Valor padrão

if ($result->num_rows > 0) {
    $horario = $result->fetch_assoc();
    if (
        $horario['status'] === 'ABERTO' &&
        $horaAtual >= $horario['inicio'] &&
        $horaAtual <= $horario['fim']
    ) {
        $statusSalao = "ABERTO";
    }
}

// Busca os dados de configuração do salão
$configSql = "SELECT nome_salao, endereco_cidade, endereco_pais, logo_salao, foto_salao, telefone FROM CONFIGURACAO LIMIT 1";
$configResult = $conn->query($configSql);

$config = [
    'nome_salao' => 'Nome do Salão',
    'telefone' => 'telefone',
    'endereco_cidade' => 'Cidade',
    'endereco_pais' => 'País',
    'logo_salao' => '/src/img/default-logo.png', // Logo padrão
    'foto_salao' => '/src/img/salao.png' // Foto padrão
];

if ($configResult->num_rows > 0) {
    $config = $configResult->fetch_assoc();
}

$fotoSalaoPath = !empty($config['foto_salao']) ? htmlspecialchars($config['foto_salao']) : '/src/img/salao.png';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/src/css/servicos.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <title>Serviços -
    <?= htmlspecialchars($config['nome_salao']); ?>
  </title>
</head>

<body>
  <div class="container">
    <!-- Cabeçalho -->
    <div class="head" style="background-image: url('<?= $fotoSalaoPath ?>');">
      <!-- Logo do Salão -->
      <?php if (!empty($config['logo_salao'])): ?>
      <img src="<?= htmlspecialchars($config['logo_salao']); ?>" alt="Logo do Salão" class="logo-salao">
      <?php endif; ?>

      <!-- Status -->
      <a href="" class="status <?= $statusSalao === 'FECHADO' ? 'closed' : 'open' ?>">
        <?= $statusSalao ?>
      </a>

      <!-- Nome do Salão -->
      <h1 id="nome-salao">
        <?= htmlspecialchars($config['nome_salao']); ?>
      </h1>
      <!-- Endereço -->
      <p>
        <?= htmlspecialchars($config['endereco_cidade']) . ', ' . htmlspecialchars($config['endereco_pais']); ?>
        <?php if ($statusSalao === 'ABERTO'): ?>
        • <span id="distance"></span>
        <?php endif; ?>
      </p>
    </div>

    <div class="actions">
      <div class="left-actions">
        <!-- Botão de Ligar -->
        <?php if (!empty($config['telefone'])): ?>
        <a href="tel:<?= htmlspecialchars($config['telefone']); ?>" class="action-item">
          <i class="fas fa-phone"></i>
          Ligar
        </a>
        <?php endif; ?>

        <!-- Botão de WhatsApp -->
        <?php if (!empty($config['telefone'])): ?>
            <?php
            // Remove caracteres não numéricos do telefone e adiciona o prefixo +55
            $telefoneWhatsApp = '+55' . preg_replace('/[^0-9]/', '', $config['telefone']);
            ?>
            <a href="https://wa.me/<?= $telefoneWhatsApp; ?>" target="_blank" rel="noopener noreferrer" class="action-item">
                <i class="fab fa-whatsapp"></i>
                WhatsApp
            </a>
        <?php endif; ?>


        <!-- Botão de Visitar -->
        <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($config['endereco_cidade'] . ', ' . $config['endereco_pais']); ?>" class="action-item" target="_blank" rel="noopener noreferrer">
          <i class="fas fa-map-marker-alt"></i>
          Visitar
        </a>

        <!-- Botão de Compartilhar -->
        <a href="#" class="action-item" onclick="shareWebsite(event)">
          <i class="fas fa-share-alt"></i>
          Compartilhar
        </a>
      </div>
    </div>


    <script>
      function shareWebsite(event) {
        event.preventDefault(); // Previne o comportamento padrão do link

        const url = window.location.href; // URL atual do site
        const title = document.title; // Título da página

        if (navigator.share) {
          navigator.share({
            title: title,
            text: "Confira este salão incrível!",
            url: url,
          }).then(() => {
            console.log("Compartilhado com sucesso!");
          }).catch((error) => {
            console.error("Erro ao compartilhar:", error);
          });
        } else {
          alert(`Copie este link para compartilhar: ${url}`);
        }
      }
    </script>


    <!-- Serviços -->
    <div class="services-section">
      <div class="search-bar">
        <?php
        // Consulta para contar os serviços cadastrados
        $sqlCount = "SELECT COUNT(*) AS total FROM SERVICO";
        $resultCount = $conn->query($sqlCount);
        $totalServicos = $resultCount->fetch_assoc()['total'];
        ?>
        <h1>Serviços (<?=$totalServicos?>)</h1>
        <input type="text" id="search-service" placeholder="Pesquise um serviço..." onkeyup="filterServices()">
      </div>

      <div id="services-list">
    <?php
    // Consulta os serviços cadastrados apenas com status ATIVO
    $sql = "SELECT id, titulo, preco, duracao, descricao, foto FROM SERVICO WHERE status = 'ATIVO'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $fotoPath = !empty($row['foto']) ? "https://espaconatalialetis.com.br" . htmlspecialchars($row['foto']) : "https://espaconatalialetis.com.br/src/img/servicos/default.jpg";
            echo "
              <div class='service-item' data-title='{$row['titulo']}'>
                <img src='{$fotoPath}' alt='{$row['titulo']}' class='service-photo'>
                <div class='service-info'>
                  <h2>{$row['titulo']}</h2>
                  <p>R$ " . number_format($row['preco'], 2, ',', '.') . " • {$row['duracao']} min</p>
                  <p>{$row['descricao']}</p>
                </div>
                <div class='service-action'>
                  <a href='agendar.php?id={$row['id']}' class='btn-agendar'>Agendar</a>
                </div>
              </div>";
        }
    } else {
        echo "<p>Nenhum serviço disponível no momento.</p>";
    }
    ?>
</div>

    </div>
  </div>

  <script src="/src/js/servicos.js"></script>
</body>

</html>