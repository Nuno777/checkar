<?php
session_start();

// Limpa todas as variáveis de sessão
$_SESSION = array();

// Destroi a sessão
session_destroy();

// Redireciona para a página de login ou página inicial
header('Location: login.php');
exit;
?>
