<?php
session_start();

// Verifica se o usuário está autenticado e é administrador (type = 1)
if (!isset($_SESSION['authenticated']) || $_SESSION['type'] != 1) {
    header('Location: login.php');
    exit;
}

include '../connect.php';

$message = '';

// Definições para paginação
$limit = 14; // Número de itens por página
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Página atual

// Validar página para evitar valores negativos ou não numéricos
$page = max(1, $page);

// Calcula o offset para a query baseado na página atual
$offset = ($page - 1) * $limit;

try {
    // Query para obter os números confirmados da base de dados com paginação e limite, ordenados por ID DESC
    $stmt = $conn->prepare("SELECT id, value, confirm_check FROM numbers WHERE confirm_check = 1 ORDER BY id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $numbers = $result->fetch_all(MYSQLI_ASSOC);

    // Conta o total de números confirmados na base de dados
    $resultTotal = $conn->query("SELECT COUNT(*) as total FROM numbers WHERE confirm_check = 1");
    $totalRows = $resultTotal->fetch_assoc()['total'];

    // Calcula o número total de páginas
    $totalPages = ceil($totalRows / $limit);
} catch (mysqli_sql_exception $e) {
    // Tratamento de erros de banco de dados
    echo "Erro: " . $e->getMessage();
    die();
}

$message = isset($_GET['message']) && $_GET['message'] == 'success' ? 'Números confirmados exibidos com sucesso!' : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmed Numbers</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-light bg-light justify-content-between">
        <a class="navbar-brand">
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
        <h1 class="mt-5">Confirmed Numbers</h1>

        <?php if ($message) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <a href="/private/index.php" class="btn btn-secondary">Back</a>
        <ul class="list-group">
            <?php foreach ($numbers as $number) : ?>
                <li class="list-group-item">
                    <a target="_blank" href="https://support.hp.com/us-en/warrantyresult/studio-x70-kit-series/2101900247/model/2101738675?sku=83Z52AA&serialnumber=<?= urlencode($number['value']) ?>">
                        <?= htmlspecialchars($number['value']) ?>
                    </a>
                    <span class="badge badge-success ml-2">Confirmed</span>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Paginação -->
        <nav aria-label="Paginação">
            <ul class="pagination">
                <?php if ($page > 1) : ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= ($page - 1) ?>" aria-label="Anterior">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages) : ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= ($page + 1) ?>" aria-label="Próxima">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

    </div>

    <!-- Bootstrap JS e dependências -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>