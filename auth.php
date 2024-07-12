<?php
session_start();
require_once 'connect.php';

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Verifica se os campos não estão vazios
    if (empty($username)) {
        $_SESSION['errors']['username'] = 'Empty username';
    }
    if (empty($password)) {
        $_SESSION['errors']['password'] = 'Empty password';
    }

    // Se não houver erros até agora, tenta realizar a autenticação
    if (empty($_SESSION['errors'])) {
        $username = mysqli_real_escape_string($conn, $username);
        $password = hash('sha512', $password); // Aplicando hash na senha

        $query = "SELECT * FROM users WHERE username='$username'";
        $result = mysqli_query($conn, $query);

        if ($result && $result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if ($user['pass'] == $password) {
                // Verifica o tipo de usuário
                if ($user['type'] != 1) {
                    $_SESSION['errors']['auth'] = 'Permission denied.';
                } else {
                    // Login bem-sucedido
                    $_SESSION['authenticated'] = true;
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['type'] = $user['type'];

                    $_SESSION["message"] = array(
                        "content" => "The username <b>" . htmlspecialchars($username) . "</b> has successfully logged in!",
                        "type" => "success",
                    );

                    // Redireciona para o dashboard
                    header('Location: index.php');
                    exit;
                }
            } else {
                $_SESSION['errors']['auth'] = 'Incorrect username/password';
            }
        } else {
            $_SESSION['errors']['auth'] = 'Incorrect username/password';
        }
    }
}

// Se houver erros, redireciona de volta para a página de login
header('Location: login.php');
exit;
?>
