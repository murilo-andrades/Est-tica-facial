function openEditModal(id) {
  // Realiza a requisição para obter os dados do profissional
  fetch(`/config/get_profissional.php?id=${id}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Erro na requisição: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        const profissional = data.data;

        // Preenche os campos do modal com os dados recebidos
        document.getElementById("edit-id").value = profissional.id;
        document.getElementById("edit-nome").value = profissional.nome;
        document.getElementById("edit-telefone").value = profissional.telefone;
        document.getElementById("edit-email").value = profissional.email;
        document.getElementById("edit-sexo").value = profissional.sexo;
        document.getElementById("edit-data-nascimento").value = profissional.dataNascimento;
        document.getElementById("edit-titular").value = profissional.contaBancaria_titular;
        document.getElementById("edit-cpf").value = profissional.contaBancaria_cpf;
        document.getElementById("edit-banco").value = profissional.contaBancaria_banco;
        document.getElementById("edit-tipo").value = profissional.contaBancaria_tipo;
        document.getElementById("edit-agencia").value = profissional.contaBancaria_agencia;
        document.getElementById("edit-numero-conta").value = profissional.contaBancaria_numero;

        // Exibe o modal
        document.getElementById("edit-modal").style.display = "flex";
      } else {
        alert(data.message || "Erro ao buscar informações do profissional.");
      }
    })
    .catch((error) => {
      console.error("Erro na requisição:", error);
      alert("Erro ao processar a requisição.");
    });
}


function closeEditModal() {
  document.getElementById("edit-modal").style.display = "none";
}

function saveEdit() {
  const id = document.getElementById("edit-id").value;
  const nome = document.getElementById("edit-nome").value;
  const telefone = document.getElementById("edit-telefone").value;
  const email = document.getElementById("edit-email").value;
  const sexo = document.getElementById("edit-sexo").value;
  const dataNascimento = document.getElementById("edit-data-nascimento").value;
  const titular = document.getElementById("edit-titular").value;
  const cpf = document.getElementById("edit-cpf").value;
  const banco = document.getElementById("edit-banco").value;
  const tipo = document.getElementById("edit-tipo").value;
  const agencia = document.getElementById("edit-agencia").value;
  const numeroConta = document.getElementById("edit-numero-conta").value;
  const foto = document.getElementById("edit-foto").files[0];

  // FormData para enviar dados e arquivos
  const formData = new FormData();
  formData.append("id", id);
  formData.append("nome", nome);
  formData.append("telefone", telefone);
  formData.append("email", email);
  formData.append("sexo", sexo);
  formData.append("data_nascimento", dataNascimento);
  formData.append("titular", titular);
  formData.append("cpf", cpf);
  formData.append("banco", banco);
  formData.append("tipo", tipo);
  formData.append("agencia", agencia);
  formData.append("numero_conta", numeroConta);
  if (foto) {
    formData.append("foto", foto);
  }

  fetch(`../../config/editar_profissional.php`, {
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
        alert("Profissional atualizado com sucesso!");
        window.location.reload();
      } else {
        alert(data.message || "Erro ao atualizar profissional.");
      }
    })
    .catch((error) => {
      console.error("Erro na requisição:", error);
      alert("Erro ao processar a requisição.");
    });
}


function deleteProfissional(id) {
  if (confirm("Tem certeza que deseja excluir este profissional?")) {
    fetch(`/config/excluir_profissional.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ id }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Profissional excluído com sucesso!");
          window.location.reload();
        } else {
          alert("Erro ao excluir profissional.");
        }
      });
  }
}
