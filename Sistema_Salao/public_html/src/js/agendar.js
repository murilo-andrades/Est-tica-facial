let selectedProfessional = null;
let selectedDate = null;
let selectedTime = null;

/** Exibe mensagens no modal */
function showMessage(message, type = "error") {
  const modalMessage = document.getElementById("modalMessage");
  modalMessage.style.color = type === "success" ? "green" : "red";
  modalMessage.textContent = message;
}

/** Oculta mensagens no modal */
function clearMessage() {
  const modalMessage = document.getElementById("modalMessage");
  modalMessage.textContent = "";
}

/** Seleciona um profissional e carrega as datas disponíveis */
function selectProfessional(professionalId) {
  selectedProfessional = professionalId;
  document.getElementById("selectedProfessional").value = professionalId;

  const dateSelector = document.getElementById("date-selector");
  const timeSection = document.getElementById("time-section");
  const confirmSection = document.getElementById("confirm-section");

  // Reseta estados visuais
  if (dateSelector)
    dateSelector.innerHTML = "<p>Carregando datas disponíveis...</p>";
  if (timeSection) timeSection.style.display = "none";
  if (confirmSection) confirmSection.style.display = "none";

  // Faz a requisição para carregar as datas
  fetch(`/config/get_available_dates.php?professionalId=${professionalId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.dates.length > 0) {
        if (dateSelector) {
          dateSelector.innerHTML = ""; // Limpa as datas antigas

          data.dates.forEach((date) => {
            const btn = document.createElement("button");
            btn.className = "date-btn";
            btn.textContent = date.display;
            btn.onclick = () => selectDate(date.value);
            dateSelector.appendChild(btn);
          });
        }

        const dateSection = document.getElementById("date-section");
        if (dateSection) dateSection.style.display = "block";
      } else if (dateSelector) {
        dateSelector.innerHTML = "<p>Nenhuma data disponível.</p>";
      }
    })
    .catch((error) => {
      console.error("Erro ao buscar datas disponíveis:", error);
      showMessage("Erro ao carregar datas. Tente novamente.");
    });
}

/** Seleciona uma data e carrega os horários disponíveis */
function selectDate(date) {
  selectedDate = date;
  document.getElementById("selectedDate").value = date;

  const timeSelector = document.getElementById("time-selector");
  if (timeSelector)
    timeSelector.innerHTML = "<p>Carregando horários disponíveis...</p>";

  fetch(
    `/config/get_available_times.php?professionalId=${selectedProfessional}&date=${date}`
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.times.length > 0) {
        if (timeSelector) {
          timeSelector.innerHTML = ""; // Limpa os horários antigos

          data.times.forEach((time) => {
            const btn = document.createElement("button");
            btn.className = "time-btn";
            btn.textContent = time;
            btn.onclick = () => selectTime(time);
            timeSelector.appendChild(btn);
          });

          document.getElementById("time-section").style.display = "block";
        }
      } else {
        timeSelector.innerHTML = "<p>Nenhum horário disponível.</p>";
      }
    })
    .catch((error) => {
      console.error("Erro ao buscar horários disponíveis:", error);
      showMessage("Erro ao carregar horários. Tente novamente.");
    });
}

/** Seleciona um horário */
function selectTime(time) {
  selectedTime = time;
  document.getElementById("selectedTime").value = time;
  document.getElementById("confirm-section").style.display = "block";
}

/** Abre o modal */
function openModal() {
  clearMessage(); // Limpa mensagens anteriores
  document.getElementById("loginModal").style.display = "block";
}

/** Fecha o modal */
function closeModal() {
  document.getElementById("loginModal").style.display = "none";
}

/** Alterna para o formulário de cadastro */
function showRegisterForm() {
  document.getElementById("loginForm").style.display = "none";
  document.getElementById("registerForm").style.display = "block";
  document.getElementById("modalTitle").textContent = "Cadastre-se";
}

/** Alterna para o formulário de login */
function showLoginForm() {
  document.getElementById("loginForm").style.display = "block";
  document.getElementById("registerForm").style.display = "none";
  document.getElementById("modalTitle").textContent = "Login Necessário";
}

/** Processa o login via AJAX */
function processLogin(event) {
  event.preventDefault(); // Evita o recarregamento da página

  const email = document.getElementById("email").value;
  const senha = document.getElementById("senha").value;

  fetch("/config/processa_login.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ email, senha }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showMessage("Login realizado com sucesso!", "success");
        setTimeout(() => {
          closeModal();
          window.location.reload(); // Atualiza a página para carregar a sessão
        }, 1000);
      } else {
        showMessage(data.message || "Erro ao realizar login.");
      }
    })
    .catch((error) => {
      console.error("Erro ao processar login:", error);
      showMessage("Erro inesperado ao realizar login. Tente novamente.");
    });
}

/** Processa o cadastro via AJAX */
function processRegister(event) {
  event.preventDefault(); // Evita o recarregamento da página

  const nome = document.getElementById("registerName").value;
  const email = document.getElementById("registerEmail").value;
  const senha = document.getElementById("registerPassword").value;
  const telefone = document.getElementById("registerPhone").value;

  fetch("/config/processa_cadastro.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ nome, email, senha, telefone }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showMessage("Cadastro realizado com sucesso!", "success");
        setTimeout(() => {
          closeModal();
          window.location.reload(); // Atualiza a página para carregar a sessão
        }, 1000);
      } else {
        showMessage(data.message || "Erro ao realizar cadastro.");
      }
    })
    .catch((error) => {
      console.error("Erro ao processar cadastro:", error);
      showMessage("Erro inesperado ao realizar cadastro. Tente novamente.");
    });
}

// Fecha o modal ao clicar fora dele
window.onclick = function (event) {
  const modal = document.getElementById("loginModal");
  if (event.target === modal) {
    closeModal();
  }
};

//ENVIO DOS DADOS PARA GERAR O LINK
function initializePaymentLinkButton() {
  const formData = new FormData(document.getElementById("paymentForm"));

  fetch("pagar.me/create_link.php", {
      method: "POST",
      body: formData,
  })
      .then((response) => response.json())
      .then((data) => {
          if (data.success) {
              // Abre o link em uma nova aba
              window.open(data.paymentLink, "_blank");

              // Verifica se o orderCode foi retornado
              if (data.orderCode) {
                  // Chama a função para abrir o modal e verificar o pagamento
                  showPaymentModal(data.orderCode);
              } else {
                  console.error("Erro: orderCode não retornado pela API.");
              }
          } else {
              console.error("Erro ao criar link de pagamento:", data.error);
              alert("Erro ao criar o link de pagamento. Tente novamente.");
          }
      })
      .catch((error) => {
          console.error("Erro na requisição:", error);
          alert("Erro inesperado. Tente novamente.");
      });
}


// ANALISAR SE PAGAMENTO FOI FEITO
function showPaymentModal(orderCode) {
  const modal = document.getElementById("paymentModal");
  if (!modal) {
      console.error("Erro: Modal 'paymentModal' não encontrado no DOM.");
      return;
  }

  // Exibe o modal
  modal.style.display = "flex";

  // Inicia a verificação periódica do pagamento
  const interval = setInterval(() => {
      fetch(`../../config/check_status_paid.php?order_code=${orderCode}`)
          .then((response) => response.json())
          .then((data) => {
              if (data.status === "paid") {
                  clearInterval(interval); // Para a verificação
                  document.getElementById("modalContent").innerHTML = `
                      <h3>Reserva Confirmada!</h3>
                      <p><h3>Data:</h3> ${data.date}</p>
                      <p><h3>Hora:</h3> ${data.time}</p>
                      <p>Enviamos um e-mail de confirmação com os detalhes do seu agendamento.</p>
                      <p><strong>Importante:</strong> Verifique sua caixa de entrada, e se não encontrar o e-mail, por favor, cheque sua pasta de <em>Spam</em> ou <em>Lixo Eletrônico</em>.</p>
                      <p>Obrigado por escolher nosso salão!</p>
                      <button type="button" class="btn-confirm" onclick="window.location.href='index.php'">Sair</button>

                  `;
              } else {
                  console.log("Pagamento ainda pendente. Status atual:", data.status);
              }
          })
          .catch((error) => console.error("Erro ao verificar o status do pagamento:", error));
  }, 5000); // Verifica a cada 5 segundos
}




document.addEventListener("DOMContentLoaded", () => {
// Event listener para os botões de datas
const dateSelector = document.querySelector(".date-selector");
if (dateSelector) {
  dateSelector.addEventListener("click", (event) => {
      if (event.target.classList.contains("date-btn")) {
          // Log no console para verificar o botão clicado
          console.log("Data clicada:", event.target.textContent);

          // Remove a classe "active" de todos os botões de data
          dateSelector.querySelectorAll(".date-btn").forEach((btn) => {
              btn.classList.remove("active");
              btn.style.backgroundColor = ""; // Reseta o fundo
          });

          // Adiciona a classe "active" ao botão clicado
          event.target.classList.add("active");
          event.target.style.backgroundColor = "#E91E63"; // Define o fundo vermelho para o botão ativo
      }
  });
}

// Event listener para os botões de horários
const timeSelector = document.querySelector(".time-selector");
if (timeSelector) {
timeSelector.addEventListener("click", (event) => {
if (event.target.classList.contains("time-btn")) {
  // Log no console para verificar o botão clicado
  console.log("Horário clicado:", event.target.textContent);

  // Remove a classe "active" de todos os botões de horário
  timeSelector.querySelectorAll(".time-btn").forEach((btn) => {
      btn.classList.remove("active");
      btn.style.backgroundColor = ""; // Reseta o fundo
  });

  // Adiciona a classe "active" ao botão clicado
  event.target.classList.add("active");
  event.target.style.backgroundColor = "#E91E63"; // Define o fundo vermelho para o botão ativo
}
});
}
});