<?php
// 1. Configuração do Banco de Dados
$servername = "localhost"; // Localhost
$username = "root";       // Usuário MySQL
$password = "";           // Senha MySQL
$dbname = "saep_preparacao"; // Nome do banco de dados
$table_name2  = "produtos"; // Tabela de produtos
$table_name3= "movimentacoes"; // Tabela assumida para histórico de movimentação

// Variáveis de estado
$mensagem = '';
$estoque_consolidado = [];

// 2. Conexão com o Banco de Dados
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password); // Conexão com PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Define modo de erro
} catch (PDOException $e) {
    die("Erro de Conexão: " . $e->getMessage()); // Erro de conexão
}

// 3. Query para Consolidar os Dados
// Esta query junta o estoque atual (tabela produtos) com o total de entradas e saídas (tabela movimentacoes).
$sql_consolidado = "
    SELECT 
        p.codigo_produto,
        p.nome_produto,
        p.quantidade AS estoque_atual,
        COALESCE(SUM(CASE WHEN m.tipo_movimentacao = 'Entrada' THEN m.quantidade ELSE 0 END), 0) AS total_entradas,
        COALESCE(SUM(CASE WHEN m.tipo_movimentacao = 'Saída' THEN m.quantidade ELSE 0 END), 0) AS total_saidas
    FROM 
        $table_name2 p
    LEFT JOIN 
        $table_name3 m ON p.codigo_produto = m.codigo_produto
    GROUP BY
        p.codigo_produto, p.nome_produto, p.quantidade
    ORDER BY
        p.nome_produto
";

try {
    $stmt = $conn->query($sql_consolidado);
    $estoque_consolidado = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Se a tabela 'movimentacoes' não existir, informa ao usuário e carrega apenas o estoque atual
    if (strpos($e->getMessage(), "Base table or view not found") !== false) {
        $mensagem = "<div class='alert alert-warning' role='alert'>Aviso: A tabela **$table_name3** (histórico) não foi encontrada no banco. Exibindo apenas o estoque atual da tabela `produtos`. Por favor, crie a tabela de histórico de movimentações.</div>";
        
        // Carrega apenas o estoque atual para não deixar a tela vazia
        $sql_fallback = "SELECT codigo_produto, nome_produto, quantidade AS estoque_atual FROM $table_name2 ORDER BY nome_produto";
        $stmt_fallback = $conn->query($sql_fallback);
        $estoque_consolidado = $stmt_fallback->fetchAll(PDO::FETCH_ASSOC);

        // Adiciona colunas vazias para manter a estrutura da tabela
        foreach ($estoque_consolidado as &$produto) {
            $produto['total_entradas'] = 'N/A';
            $produto['total_saidas'] = 'N/A';
        }
        unset($produto); // Importante para que a referência não seja mantida
        
    } else {
        $mensagem = "<div class='alert alert-danger' role='alert'>Erro ao gerar relatório de estoque: " . $e->getMessage() . "</div>";
    }
}

// 4. Fecha a conexão
$conn = null;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Estoque - Relatório</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">

<!--Menu principal-->
        <div class="menuprincipal">
            <hr>
            <ul class="nav justify-content-center">
                <li class="nav-item">
                    <a class="nav-link" href="cadastrarproduto.html">Cadastrar Produto</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="editarproduto.php">Editar Produto</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="movimentacaoproduto.php">Movimentação de Produto</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" href="exibirestoque.php">Exibir estoque</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link link-danger" href="sairdosistema.php">Sair do sistema</a>
                </li>
            </ul>
            </ul>
            <hr>
        </div>
        <!--FIm do Menu principal-->

        <div class="row p-2 text-center">
            <h2>Relatório de Estoque</h2>
            <?php echo $mensagem; // Exibe mensagens de status ?>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">Cód. Produto</th>
                        <th class="text-center">Nome do Produto</th>
                        <th class="text-center">Primeiro Cadastro</th>
                        <th class="text-center">Total de Entradas</th>
                        <th class="text-center">Total de Saídas</th>
                        <th class="text-center">Estoque Atual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($estoque_consolidado) > 0): ?>
                        <?php foreach ($estoque_consolidado as $produto): ?>
                            <tr>
                                <td class="text-center"><?php echo htmlspecialchars($produto['codigo_produto']); ?></td>
                                <td><?php echo htmlspecialchars($produto['nome_produto']); ?></td>
                                <td class="text-center text-info fw-bold"><?php echo htmlspecialchars($produto['total_saidas']-$produto['total_entradas']); ?></td>
                                <td class="text-center text-success fw-bold"><?php echo htmlspecialchars($produto['total_entradas']); ?></td>
                                <td class="text-center text-danger fw-bold"><?php echo htmlspecialchars($produto['total_saidas']); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?php echo ($produto['estoque_atual'] > 100) ? 'success' : (($produto['estoque_atual'] > 10) ? 'warning' : 'danger'); ?> fs-6">
                                        <?php echo htmlspecialchars($produto['estoque_atual']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Nenhum produto cadastrado para exibir.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="row p-2">
            <a href="movimentacaoproduto.php" class="btn btn-primary">Fazer Nova Movimentação</a>
        </div>
        <div class="row p-2">
            <a href="cadastrarproduto.php" class="btn btn-success">Cadastrar Novo produto</a>
        </div>
        <div class="row p-2">
            <a href="editarproduto.php" class="btn btn-warning">Editar produto</a>
        </div>
    </div>
</body>
<script src="assets/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</html>
