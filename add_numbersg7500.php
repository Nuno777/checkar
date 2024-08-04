<?php
session_start();

// Verifica se o usuário não está autenticado e redireciona para a página de login
if (!isset($_SESSION['authenticated'])) {
    header('Location: login.php');
    exit;
}

include 'connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $numbers = isset($_POST['numbers']) ? $_POST['numbers'] : '';

    // Verifica se a entrada não está vazia
    if (!empty($numbers)) {
        $numberArray = array_map('trim', explode(',', $numbers));
        $numberArray = array_filter($numberArray); // Remove entradas vazias

        if (!empty($numberArray)) {
            $success = true;
            $existingNumbers = [];

            foreach ($numberArray as $number) {
                // Verifica se o número já existe na base de dados
                $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM g7500 WHERE value = ?");
                $stmt->bind_param('s', $number);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();

                if ($result['count'] > 0) {
                    // Número já existe na base de dados
                    $existingNumbers[] = $number;
                    continue;
                }

                // Prepara e executa a inserção na base de dados
                $stmt = $conn->prepare("INSERT INTO g7500 (value) VALUES (?)");
                $stmt->bind_param('s', $number);
                if (!$stmt->execute()) {
                    $success = false;
                    break;
                }
            }

            if ($success) {
                // Redireciona para index.php com mensagem de sucesso
                header('Location: g7500.php?message=success');
                exit();
            } else {
                if (!empty($existingNumbers)) {
                    $message = 'Os seguintes números já existem na base de dados: ' . implode(', ', $existingNumbers);
                } else {
                    $message = 'Erro ao adicionar números. Tente novamente.';
                }
            }
        } else {
            $message = 'Por favor, adicione pelo menos um número.';
        }
    } else {
        $message = 'Por favor, preencha o campo com números.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Numbers G7500</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-light bg-light justify-content-between">
        <a href="/" class="navbar-brand">
            <?php
            // Verifica se o usuário está logado e exibe seu nome
            if (isset($_SESSION['authenticated'])) {
                echo $_SESSION['username']; // Supondo que 'username' seja o campo correto da sessão
            } else {
                echo "Guest";
            }
            ?>
        </a>

        <!-- Formulário para Logout -->
        <form class="form-inline" action="logout.php" method="POST">
            <button class="btn btn-outline-danger my-2 my-sm-0" type="submit">Logout</button>
        </form>
    </nav>

    <div class="container">
        <h1 class="mt-5">Add Numbers G7500</h1>

        <?php if ($message) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post" action="add_numbersg7500.php">
        
            <div class="form-group">
                <label for="numbers">8G2152673810F2, 8G2330753274F2
                    <br>
                    compara esses numeros, e depois da me 10 numeros parecido a esses separado por virgulas</label>
                <textarea class="form-control" id="numbers" name="numbers" rows="10"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="g7500.php" class="btn btn-secondary">Back</a>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>