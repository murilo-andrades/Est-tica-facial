<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Usuário não está logado, redireciona para a página de login
    header("Location: /index.html");
    exit();
}

// Define variáveis para uso em outras páginas
$userId = $_SESSION['user_id'];
$userNome = $_SESSION['user_nome'];
$userSobrenome = $_SESSION['user_sobrenome'];
