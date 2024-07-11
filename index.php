<?php
include 'connect.php';

$message = '';

// Verifica se a ação de atualização foi solicitada
if (isset($_GET['action'], $_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);

    // Verificar se a ação é válida (confirm ou not_confirm)
    if ($action === 'confirm') {
        $stmt = $conn->prepare("UPDATE numbers SET confirm_check = 1, not_check = 0 WHERE id = ?");
    } elseif ($action === 'not_confirm') {
        $stmt = $conn->prepare("UPDATE numbers SET not_check = 1, confirm_check = 0 WHERE id = ?");
    }

    // Executar a atualização
    $stmt->bind_param('i', $id);
    $stmt->execute();

    // Redirecionar de volta para a página principal com uma mensagem de sucesso
    header('Location: index.php?message=success');
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
    // Query para obter os números da base de dados com paginação e limite
    $stmt = $conn->prepare("SELECT id, value, confirm_check, not_check FROM numbers LIMIT ? OFFSET ?");
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

$message = isset($_GET['message']) && $_GET['message'] == 'success' ? 'Números adicionados com sucesso!' : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Numbers</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1 class="mt-5">Numbers</h1>

        <?php if ($message) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <a href="add_numbers.php" class="btn btn-success mb-3">Add Numbers</a>
        <ul class="list-group">
            <?php foreach (array_slice($numbers, 0, $limit) as $number) : ?>
                <li class="list-group-item">
                    <a target="_blank" href="https://support.hp.com/us-en/warrantyresult/studio-x70-kit-series/2101900247/model/2101738675?sku=83Z52AA&serialnumber=<?= urlencode($number['value']) ?>">
                        <?= htmlspecialchars($number['value']) ?>
                    </a>

                    <?php if ($number['confirm_check'] == 1) : ?>
                        <span class="badge badge-success ml-2">Confirmed</span>
                    <?php elseif ($number['not_check'] == 1) : ?>
                        <span class="badge badge-danger ml-2">Not Confirmed</span>
                    <?php else : ?>
                        <a href="index.php?action=confirm&id=<?= $number['id'] ?>" class="btn btn-sm btn-success ml-2">Confirmed</a>
                        <a href="index.php?action=not_confirm&id=<?= $number['id'] ?>" class="btn btn-sm btn-danger ml-2">Not Confirmed</a>
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