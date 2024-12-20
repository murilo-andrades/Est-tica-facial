function openEditModal(id) {
  // Seleciona a linha da tabela pelo ID
  const row = document.querySelector(`tr[data-id="${id}"]`);
  if (!row) {
    console.error("Linha não encontrada para o ID:", id);
    return;
  }

  const titulo = row.children[1].textContent;
  const preco = row.children[2].textContent.replace("R$ ", "").trim();
  const duracao = row.children[3].textContent.replace(" min", "").trim();
  const descricao = row.children[4]?.textContent || ""; // Adiciona descrição, assumindo que é a 5ª coluna

  // Preenche os campos do modal
  const editIdField = document.getElementById("edit-id");
  if (!editIdField) {
    console.error("Elemento 'edit-id' não encontrado no DOM.");
    return;
  }

  editIdField.value = id;
  document.getElementById("edit-titulo").value = titulo;
  document.getElementById("edit-preco").value = preco;
  document.getElementById("edit-duracao").value = duracao;
  document.getElementById("edit-descricao").value = descricao;

  // Mostra o modal
  document.getElementById("edit-modal").style.display = "flex";
}


function closeEditModal() {
  document.getElementById("edit-modal").style.display = "none";
}

function saveEdit() {
  const id = document.getElementById("edit-id").value;
  const titulo = document.getElementById("edit-titulo").value;
  const preco = document.getElementById("edit-preco").value;
  const duracao = document.getElementById("edit-duracao").value;
  const descricao = document.getElementById("edit-descricao").value;
  const foto = document.getElementById("edit-foto").files[0]; // Captura o arquivo da foto

  // Cria o formulário para envio de dados com foto
  const formData = new FormData();
  formData.append("id", id);
  formData.append("titulo", titulo);
  formData.append("preco", preco);
  formData.append("duracao", duracao);
  formData.append("descricao", descricao);
  if (foto) {
    formData.append("foto", foto);
  }

  fetch(`/config/editar_servico.php`, {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Erro na requisição: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        alert("Serviço atualizado com sucesso!");
        window.location.reload();
      } else {
        alert(data.message || "Erro ao atualizar serviço.");
      }
    })
    .catch((error) => {
      console.error("Erro na requisição:", error);
      alert("Erro ao processar a requisição.");
    });
}

function deleteServico(id) {
  if (confirm("Tem certeza que deseja excluir este serviço?")) {
    fetch(`/config/excluir_servico.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ id }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Serviço excluído com sucesso!");
          window.location.reload();
        } else {
          alert("Erro ao excluir serviço.");
        }
      });
  }
}

// Função para alternar o status do serviço
function toggleStatus(servicoId, button) {
  const row = button.closest('tr');
  const statusCell = row.querySelector('.status-cell');

  // Enviar a requisição via AJAX para alterar o status
  fetch('/config/toggle_status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: servicoId })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Alterna o status na interface
      const newStatus = data.newStatus;
      statusCell.textContent = newStatus;
      button.textContent = newStatus === 'ATIVO' ? 'Inativar' : 'Ativar';
    } else {
      alert('Erro ao alterar o status. Tente novamente.');
    }
  })
  .catch(error => {
    console.error('Erro:', error);
    alert('Erro ao processar a solicitação.');
  });
}