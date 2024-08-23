<?php
session_start();

include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Consulta para obter o usuário pelo nome de usuário
    $sql = "SELECT * FROM users WHERE username=?";

    // Preparando a consulta com um statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    // Obtendo o resultado da consulta
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_password = $row['pass']; // Senha armazenada no banco (hash sha512)
        $verify_status = $row['verify']; // Status de verificação do usuário

        // Aplicando hash SHA-512 para a senha fornecida pelo usuário
        $hashed_password = hash('sha512', $password);

        // Comparando as senhas
        if ($hashed_password === $stored_password) {
            if ($verify_status == 1) {
                // Senha correta e usuário verificado, logado com sucesso
                $_SESSION['username'] = $username;
                $_SESSION['authenticated'] = true; // Define a sessão como autenticada
                header("Location: index.php"); // Redireciona para a página de dashboard após login
                exit();
            } else {
                // Usuário não verificado
                $_SESSION['errors'] = array("Account not verified. Please verify your account.");
                header("Location: login.php"); // Redireciona de volta para a página de login
                exit();
            }
        } else {
            // Senha incorreta
            $_SESSION['errors'] = array("Incorrect password. Try again.");
            header("Location: login.php"); // Redireciona de volta para a página de login
            exit();
        }
    } else {
        // Usuário não encontrado
        $_SESSION['errors'] = array("User not found.");
        header("Location: login.php"); // Redireciona de volta para a página de login
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos CSS personalizados -->
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-form {
            max-width: 350px;
            margin: 0 auto;
            margin-top: 100px;
            background: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
            display: none;
            /* Initially hidden */
        }

        .maintenance-message {
            max-width: 1200px;
            text-align: center;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
        }

        .maintenance-message h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .maintenance-message p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .maintenance-message .spinner-border {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Message de manutenção -->
        <div class="maintenance-message">
            <h1>We'll be back soon!</h1>
            <p>We're currently performing some maintenance. Please check back later.</p>
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p>Thank you for your patience.</p>
            <input type="password" id="unlock-code" class="form-control mt-4" placeholder="Enter code">
            <button id="unlock-button" class="btn btn-primary mt-2">Unlock</button>
        </div>

        <!-- Formulário de login -->
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="login-form">
                    <h2 class="text-center mb-4">Sign In</h2>

                    <?php if (isset($_SESSION['errors'])) : ?>
                        <div class="alert alert-danger">
                            <?php foreach ($_SESSION['errors'] as $error) : ?>
                                <?= htmlspecialchars($error) ?><br>
                            <?php endforeach; ?>
                        </div>
                        <?php unset($_SESSION['errors']); ?>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS e dependências -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Custom JavaScript -->
    <script src="script.js"></script>
</body>

</html>