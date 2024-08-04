<?php
session_start();

// Verifica se o usuário não está autenticado e redireciona para a página de login
if (!isset($_SESSION['authenticated'])) {
    header('Location: login.php');
    exit;
}

include 'connect.php';

// Definições para paginação
$limit = 14; // Número de itens por página
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Página atual

// Validar página para evitar valores negativos ou não numéricos
$page = max(1, $page);

// Calcula o offset para a query baseado na página atual
$offset = ($page - 1) * $limit;

try {
    // Query para obter os números confirmados da base de dados com paginação e limite, ordenados por ID DESC
    $stmt = $conn->prepare("SELECT id, value, confirm_check FROM numbers WHERE confirm_check = 1 OR confirm_check = 2 OR confirm_check = 3 ORDER BY id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $numbers = $result->fetch_all(MYSQLI_ASSOC);

    // Conta o total de números confirmados na base de dados
    $resultTotal = $conn->query("SELECT COUNT(*) as total FROM numbers WHERE confirm_check = 1 OR confirm_check = 2 OR confirm_check = 3");
    $totalRows = $resultTotal->fetch_assoc()['total'];

    // Calcula o número total de páginas
    $totalPages = ceil($totalRows / $limit);
} catch (mysqli_sql_exception $e) {
    // Tratamento de erros de banco de dados
    echo "Erro: " . $e->getMessage();
    die();
}

$message = isset($_GET['message']) && $_GET['message'] == 'success' ? 'Número marcado como usado com sucesso!' : '';

// Processamento da alteração de status via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $number_id = $_POST['number_id'];
    $new_status = $_POST['new_status'];

    try {
        // Atualiza o status do número para o novo status
        $update_stmt = $conn->prepare("UPDATE numbers SET confirm_check = ? WHERE id = ?");
        $update_stmt->bind_param('ii', $new_status, $number_id);
        $update_stmt->execute();

        // Retorna uma resposta JSON indicando sucesso
        echo json_encode(['success' => true, 'new_status' => $new_status]);
        exit;
    } catch (mysqli_sql_exception $e) {
        // Retorna uma resposta JSON indicando erro
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmed Numbers Studio X70</title>
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
        <h1 class="mt-5">Valid Numbers Studio X70</h1>

        <?php if ($message) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <a href="studiox70.php" class="btn btn-secondary">Back</a>
        <ul class="list-group">
            <?php foreach ($numbers as $number) : ?>
                <li class="list-group-item">
                    <a target="_blank" href="https://support.hp.com/us-en/warrantyresult/studio-x70-kit-series/2101900247/model/2101738675?sku=83Z52AA&serialnumber=<?= urlencode($number['value']) ?>">
                        <?= htmlspecialchars($number['value']) ?>
                    </a>
                    <span class="badge 
                    <?php 
                        if ($number['confirm_check'] == 1) echo 'badge-success';
                        elseif ($number['confirm_check'] == 2) echo 'badge-secondary';
                        elseif ($number['confirm_check'] == 3) echo 'badge-info';
                    ?> ml-2">
                    <?php 
                        if ($number['confirm_check'] == 1) echo 'Valid';
                        elseif ($number['confirm_check'] == 2) echo 'Used';
                        elseif ($number['confirm_check'] == 3) echo 'Factory warranty';
                    ?>
                    </span>
                    <?php if ($number['confirm_check'] == 1) : ?>
                        <button type="button" class="btn btn-warning btn-sm float-right" onclick="markAsUsed(<?= $number['id'] ?>, this)">Mark as Used</button>
                        <button type="button" class="btn btn-primary btn-sm float-right mr-2" onclick="Factorywarranty(<?= $number['id'] ?>, this)">Mark as Factory warranty</button>
                    <?php elseif ($number['confirm_check'] == 3) : ?>
                        <button type="button" class="btn btn-warning btn-sm float-right" onclick="markAsUsed(<?= $number['id'] ?>, this)">Mark as Used</button>
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
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- Script AJAX para marcar número como usado -->
    <script>
        function markAsUsed(id, button) {
            $.post('', { change_status: true, number_id: id, new_status: 2 }, function(response) {
                let data = JSON.parse(response);
                if (data.success) {
                    updateBadge(button, 'badge-secondary', 'Used');
                } else {
                    alert('Erro ao marcar como usado: ' + data.error);
                }
            });
        }

        function Factorywarranty(id, button) {
            $.post('', { change_status: true, number_id: id, new_status: 3 }, function(response) {
                let data = JSON.parse(response);
                if (data.success) {
                    updateBadge(button, 'badge-info', 'Factory warranty');
                } else {
                    alert('Erro ao marcar como wanted: ' + data.error);
                }
            });
        }

        function updateBadge(button, newClass, newText) {
            let badge = button.parentNode.querySelector('.badge');
            badge.className = 'badge ' + newClass + ' ml-2';
            badge.textContent = newText;
            let siblingButtons = button.parentNode.querySelectorAll('.btn');
            siblingButtons.forEach(btn => btn.remove());
        }
    </script>
</body>

</html>
