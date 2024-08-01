<?php
session_start();

// Verifica se o usuário não está autenticado e redireciona para a página de login
if (!isset($_SESSION['authenticated'])) {
    header('Location: login.php');
    exit;
}

include 'connect.php';

$message = '';

// Verifica se a ação de atualização ou exclusão foi solicitada
if (isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);
    $currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;

    // Verificar se a ação é válida (confirm, not_confirm, delete)
    if ($action === 'confirm') {
        $stmt = $conn->prepare("UPDATE numbers SET confirm_check = 1, not_check = 0 WHERE id = ?");
    } elseif ($action === 'not_confirm') {
        $stmt = $conn->prepare("UPDATE numbers SET not_check = 1, confirm_check = 0 WHERE id = ?");
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM numbers WHERE id = ?");
    }

    // Executar a atualização ou exclusão
    $stmt->bind_param('i', $id);
    $stmt->execute();

    // Redirecionar de volta para a página principal com uma mensagem de sucesso e a página correta
    header('Location: studiox70.php?page=' . $currentPage . '&message=success');
    exit;
}


// Definições para paginação
$limit = 14; // Número de itens por página
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Página atual

// Validar página para evitar valores negativos ou não numéricos
$page = max(1, $page);

// Calcula o offset para a query baseado na página atual
$offset = ($page - 1) * $limit;

try {
    // Query para obter os números da base de dados com paginação e limite, ordenados por ID DESC
    $stmt = $conn->prepare("SELECT id, value, confirm_check, not_check FROM numbers ORDER BY id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $numbers = $result->fetch_all(MYSQLI_ASSOC);

    // Conta o total de números na base de dados
    $resultTotal = $conn->query("SELECT COUNT(*) as total FROM numbers");
    $totalRows = $resultTotal->fetch_assoc()['total'];

    // Calcula o número total de páginas
    $totalPages = ceil($totalRows / $limit);
} catch (mysqli_sql_exception $e) {
    // Tratamento de erros de banco de dados
    echo "Erro: " . $e->getMessage();
    die();
}

$message = isset($_GET['message']) && $_GET['message'] == 'success' ? 'Operação realizada com sucesso!' : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Numbers Studio X70</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-light bg-light justify-content-between">
        <a href="index.php" class="navbar-brand">
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
        <h1 class="mt-5">Numbers Studio X70</h1>

        <?php if ($message) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <a href="add_numbers.php" class="btn btn-primary mb-3">Add Numbers</a>
        <a href="confirm_check.php" class="btn btn-success mb-3">Valid Numbers</a>
        <ul class="list-group">
            <?php foreach ($numbers as $number) : ?>
                <li class="list-group-item">
                    <a target="_blank" href="https://support.hp.com/us-en/warrantyresult/studio-x70-kit-series/2101900247/model/2101738675?sku=83Z52AA&serialnumber=<?= urlencode($number['value']) ?>">
                        <?= htmlspecialchars($number['value']) ?>
                    </a>

                    <?php if ($number['confirm_check'] == 1) : ?>
                        <span class="badge badge-success ml-2">Valid</span>
                        <a href="studiox70.php?action=not_confirm&id=<?= $number['id'] ?>&page=<?= $page ?>" class="btn btn-sm btn-warning ml-2">Change to Not Valid</a>
                    <?php elseif ($number['not_check'] == 1) : ?>
                        <span class="badge badge-danger ml-2">Not Valid</span>
                        <a href="studiox70.php?action=confirm&id=<?= $number['id'] ?>&page=<?= $page ?>" class="btn btn-sm btn-warning ml-2">Change to Valid</a>
                    <?php elseif ($number['confirm_check'] == 2) : ?>
                        <span class="badge badge-secondary ml-2">Used</span>
                    <?php elseif ($number['confirm_check'] == 3) : ?>
                        <span class="badge badge-info ml-2">Factory warranty</span>
                    <?php else : ?>
                        <a href="studiox70.php?action=confirm&id=<?= $number['id'] ?>&page=<?= $page ?>" class="btn btn-sm btn-success ml-2">Valid</a>
                        <a href="studiox70.php?action=not_confirm&id=<?= $number['id'] ?>&page=<?= $page ?>" class="btn btn-sm btn-danger ml-2">Not Valid</a>
                    <?php endif; ?>

                    <?php if (strpos($number['value'], '8G2') !== 0) : ?>
                        <a href="studiox70.php?action=delete&id=<?= $number['id'] ?>&page=<?= $page ?>" class="btn btn-sm btn-dark ml-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                            </svg>
                        </a>
                    <?php endif; ?>
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