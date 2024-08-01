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
    header('Location: index.php?page=' . $currentPage . '&message=success');
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
    <title>Numbers</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="geral.css">
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
        <br>
        <div class="card-deck">
            <div class="card">
                <a href="studiox70.php">
                    <div class="card-body">
                        <h5 class="card-title">Studio X70</h5>
                        <p class="card-text">Go To Page</p>
                    </div>
                </a>
            </div>
            <div class="card">
                <a href="studioE70.php">
                    <div class="card-body">
                        <h5 class="card-title">Studio E70</h5>
                        <p class="card-text">Go To Page</p>
                    </div>
                </a>
            </div>
            <div class="card">
                <a href="hpz4.php">
                    <div class="card-body">
                        <h5 class="card-title">HP Z4</h5>
                        <p class="card-text">Go To Page</p>
                    </div>
                </a>
            </div>
        </div>


    </div>

    <!-- Bootstrap JS e dependências -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>