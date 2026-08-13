<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário já possui um token de sessão válido
if (isset($_SESSION['token']) && $_SESSION['token'] !== '' && isset($_SESSION['usuario'])) {
    header('Location: pages/dashboard.php');
    exit;
}

// Caso contrário, redireciona para a tela de login
header('Location: login.php');
exit;
