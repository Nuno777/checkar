<?php
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
                $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM numbers WHERE value = :value");
                $stmt->execute(['value' => $number]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result['count'] > 0) {
                    // Número já existe na base de dados
                    $existingNumbers[] = $number;
                    continue;
                }

                // Prepara e executa a inserção na base de dados
                $stmt = $pdo->prepare("INSERT INTO numbers (value) VALUES (:value)");
                if (!$stmt->execute(['value' => $number])) {
                    $success = false;
                    break;
                }
            }

            if ($success) {
                // Redireciona para index.php com mensagem de sucesso
                header('Location: index.php?message=success');
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
    <title>Add Numbers</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1 class="mt-5">Add Numbers</h1>

        <?php if ($message): ?>
            <div class="alert alert-danger"><?= $message ?></div>
        <?php endif; ?>

        <form method="post" action="add_numbers.php">
            <div class="form-group">
                <label for="numbers">Numbers (separated by comma '67834681,3721893721')</label>
                <textarea class="form-control" id="numbers" name="numbers" rows="10" ></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="index.php" class="btn btn-secondary">Back</a>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
