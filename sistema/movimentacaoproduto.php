<?php
// Inicia a sessão (necessário caso você implemente controle de acesso)
session_start();

// 1. Configuração do Banco de Dados
$servername = "localhost";
$username = "root"; // Mude para o seu usuário do MySQL
$password = "";   // Mude para sua senha do MySQL
$dbname = "saep_preparacao"; // Nome do banco de dados
$table_name2 = "produtos"; // Nome da tabela de produtos
$table_movimentacoes = "movimentacoes"; // Nome da tabela de movimentações (histórico)

// Variáveis de estado
$mensagem = '';
$produtos = [];

// 2. Conexão com o Banco de Dados
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de Conexão: " . $e->getMessage());
}

// 3. Processamento do Formulário de Movimentação (POST)
if (isset($_POST['movimentar'])) {
    
    $prodcod = $_POST['prodcod'] ?? '';
    $tipo_movimentacao = $_POST['tipo_movimentacao'] ?? '';
    $quantidade_movimentar = intval($_POST['quantidade_movimentar'] ?? 0); // Garante que é um inteiro
    
    // Validação básica
    if (empty($prodcod) || empty($tipo_movimentacao) || $quantidade_movimentar <= 0) {
        $mensagem = "<div class='alert alert-danger' role='alert'>Erro: Preencha todos os campos corretamente e a quantidade deve ser maior que zero.</div>";
        goto busca_produtos; // Pula para a busca de produtos para reexibir a tabela
    }

    try {
        // INICIA A TRANSAÇÃO para garantir que as duas operações (UPDATE e INSERT) funcionem
        $conn->beginTransaction(); 

        // 3.1. Busca a quantidade atual para validação
        $sql_select = "SELECT quantidade, nome_produto FROM $table_name2 WHERE codigo_produto = :prodcod";
        $stmt_select = $conn->prepare($sql_select);
        $stmt_select->bindParam(':prodcod', $prodcod);
        $stmt_select->execute();
        $produto_atual = $stmt_select->fetch(PDO::FETCH_ASSOC);

        if (!$produto_atual) {
            $mensagem = "<div class='alert alert-danger' role='alert'>Erro: Produto com código $prodcod não encontrado.</div>";
            $conn->rollBack(); // Desfaz a transação (embora não tenha feito nada ainda)
            goto busca_produtos;
        }

        $quantidade_atual = $produto_atual['quantidade'];
        $nome_produto = $produto_atual['nome_produto'];

        $sql_update = "";
        $nova_quantidade = 0;

        if ($tipo_movimentacao == 'Entrada') {
            // Aumenta a quantidade (SOMA)
            $sql_update = "UPDATE $table_name2 SET quantidade = quantidade + :quantidade_movimentar WHERE codigo_produto = :prodcod";
            $nova_quantidade = $quantidade_atual + $quantidade_movimentar;
            $mensagem_sucesso = "Entrada de $quantidade_movimentar unidades de $nome_produto registrada com sucesso! Novo estoque: $nova_quantidade.";
        } elseif ($tipo_movimentacao == 'Saída') {
            
            // Valida se há estoque suficiente
            if ($quantidade_movimentar > $quantidade_atual) {
                $mensagem = "<div class='alert alert-danger' role='alert'>Erro: Estoque insuficiente! Não é possível retirar $quantidade_movimentar unidades. Estoque atual: $quantidade_atual.</div>";
                $conn->rollBack();
                goto busca_produtos;
            }

            // Diminui a quantidade (SUBTRAÇÃO)
            $sql_update = "UPDATE $table_name2 SET quantidade = quantidade - :quantidade_movimentar WHERE codigo_produto = :prodcod";
            $nova_quantidade = $quantidade_atual - $quantidade_movimentar;
            $mensagem_sucesso = "Saída de $quantidade_movimentar unidades de $nome_produto registrada com sucesso! Novo estoque: $nova_quantidade.";

        } else {
            $mensagem = "<div class='alert alert-danger' role='alert'>Erro: Tipo de movimentação inválido.</div>";
            $conn->rollBack();
            goto busca_produtos;
        }

        // 3.2. Executa a query de atualização na tabela 'produtos'
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bindParam(':quantidade_movimentar', $quantidade_movimentar, PDO::PARAM_INT);
        $stmt_update->bindParam(':prodcod', $prodcod);
        $stmt_update->execute();
        
        // 3.3. INSERE O REGISTRO DE MOVIMENTAÇÃO NA TABELA 'movimentacoes'
        $sql_insert_mov = "INSERT INTO $table_movimentacoes (codigo_produto, tipo_movimentacao, quantidade) VALUES (:prodcod, :tipo_movimentacao, :quantidade_movimentar)";
        $stmt_insert = $conn->prepare($sql_insert_mov);
        
        $stmt_insert->bindParam(':prodcod', $prodcod);
        $stmt_insert->bindParam(':tipo_movimentacao', $tipo_movimentacao);
        $stmt_insert->bindParam(':quantidade_movimentar', $quantidade_movimentar, PDO::PARAM_INT);
        $stmt_insert->execute();

        // 3.4. CONFIRMA A TRANSAÇÃO
        $conn->commit();
        
        $mensagem = "<div class='alert alert-success' role='alert'>$mensagem_sucesso</div>";

    } catch (PDOException $e) {
        // Em caso de erro, desfaz todas as operações no banco
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $mensagem = "<div class='alert alert-danger' role='alert'>Erro ao movimentar produto (e transação desfeita): " . $e->getMessage() . "</div>";
    }
}

// 4. Busca os dados dos produtos (para a tabela e o dropdown)
busca_produtos:
try {
    // Busca apenas as informações essenciais
    $sql_busca = "SELECT codigo_produto, nome_produto, quantidade FROM $table_name2 ORDER BY nome_produto";
    $stmt_busca = $conn->query($sql_busca);
    $produtos = $stmt_busca->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem .= "<div class='alert alert-danger' role='alert'>Erro ao carregar lista de produtos: " . $e->getMessage() . "</div>";
}

// 5. Fecha a conexão
$conn = null;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimentação de Estoque</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        .table-fixed-header thead th {
            position: sticky;
            top: 0;
            background: #f8f9fa;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="container">
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
                    <a class="nav-link disabled" href="movimentacaoproduto.php">Movimentação de Produto</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="exibirestoque.php">Exibir estoque</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link link-danger" href="sairdosistema.php">Sair do sistema</a>
                </li>
            </ul>
            </ul>
            <hr>
        </div>
        <div class="row p-2 text-center">
            <h2>Movimentação de Estoque</h2>
            <?php echo $mensagem; // Exibe mensagens de status ?>
        </div>
        
        <div class="card p-4 mb-4">
            <h3 class="card-title">Nova Movimentação</h3>
            <form action="movimentacaoproduto.php" method="post">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="prodcod" class="form-label">Produto</label>
                        <select id="prodcod" name="prodcod" class="form-select" required>
                            <option value="">Selecione o Produto</option>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?php echo htmlspecialchars($produto['codigo_produto']); ?>">
                                    <?php echo htmlspecialchars($produto['nome_produto']); ?> (Cód: <?php echo htmlspecialchars($produto['codigo_produto']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="tipo_movimentacao" class="form-label">Tipo</label>
                        <select id="tipo_movimentacao" name="tipo_movimentacao" class="form-select" required>
                            <option value="">Escolher...</option>
                            <option value="Entrada">Entrada (Adicionar)</option>
                            <option value="Saída">Saída (Retirar)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="quantidade_movimentar" class="form-label">Quantidade</label>
                        <input type="number" name="quantidade_movimentar" id="quantidade_movimentar" class="form-control" required min="1" placeholder="Qtd.">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <input type="submit" name="movimentar" id="movimentar" value="Confirmar" class="btn btn-primary w-100">
                    </div>
                </div>
            </form>
        </div>

        <h2 class="mt-5">Estoque Atual dos Produtos</h2>
        <div style="max-height: 400px; overflow-y: auto;">
            <table class="table table-striped table-bordered table-fixed-header">
                    <tr class="table-dark">
                        <th>Cód. Produto</th>
                        <th>Nome do Produto</th>
                        <th>Quantidade em Estoque</th>
                    </tr>
                <tbody>
                    <?php if (count($produtos) > 0): ?>
                        <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($produto['codigo_produto']); ?></td>
                                <td><?php echo htmlspecialchars($produto['nome_produto']); ?></td>
                                <td><span class="badge bg-<?php echo ($produto['quantidade'] > 100) ? 'success' : (($produto['quantidade'] > 10) ? 'warning' : 'danger'); ?>"><?php echo htmlspecialchars($produto['quantidade']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">Nenhum produto encontrado no estoque.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
<script src="assets/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</html>