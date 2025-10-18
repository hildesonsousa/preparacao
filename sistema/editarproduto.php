<?php

// 1. Configuração do Banco de Dados (Reaproveitado do cadastrarproduto.php)
$servername = "localhost";
$username = "root"; // Mude para o seu usuário do MySQL
$password = "";   // Mude para sua senha do MySQL
$dbname = "saep_preparacao"; // Nome do banco de dados
$table_name2 = "produtos"; // Nome da tabela que armazenará os produtos

// Variáveis para armazenar os dados do produto para exibição
$produto = null;
$mensagem = '';

// 2. Conexão com o Banco de Dados
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de Conexão: " . $e->getMessage());
}

// 3. Verifica se o formulário de ATUALIZAÇÃO foi submetido (método POST)
if (isset($_POST['atualizar'])) {
    
    // Coleta dos Dados do Formulário para atualização (incluindo o código_produto)
    $prodcod     = $_POST['prodcod']     ?? ''; // Chave para o WHERE
    $prodnome    = $_POST['prodnome']    ?? '';
    $prodqt      = $_POST['prodqt']      ?? '';
    $prodmed     = $_POST['prodmed']     ?? '';
    $produnmed   = $_POST['produnmed']   ?? '';
    $proddim     = $_POST['proddim']     ?? '';
    $prodvalid   = $_POST['prodvalid']   ?? '';
    $prodaquisicao = $_POST['prodaquisicao'] ?? '';
    $prodcor     = $_POST['prodcor']     ?? '';
    $prodapli    = $_POST['prodapli']    ?? '';
    $prodapre    = $_POST['prodapre']    ?? '';
    $prodout     = $_POST['prodout']     ?? '';
    
    // Preparação da Query SQL de UPDATE
    $sql = "UPDATE $table_name2 SET 
                nome_produto = :prodnome, 
                quantidade = :prodqt, 
                peso_volume_comp = :prodmed, 
                unidade_medida = :produnmed, 
                dimensoes = :proddim, 
                data_validade = :prodvalid, 
                data_aquisicao = :prodaquisicao, 
                cor = :prodcor, 
                aplicacao = :prodapli, 
                apresentacao = :prodapre, 
                outras_informacoes = :prodout
            WHERE codigo_produto = :prodcod";

    try {
        $stmt = $conn->prepare($sql);

        // Bind dos Parâmetros
        $stmt->bindParam(':prodnome', $prodnome);
        $stmt->bindParam(':prodqt', $prodqt);
        $stmt->bindParam(':prodmed', $prodmed);
        $stmt->bindParam(':produnmed', $produnmed);
        $stmt->bindParam(':proddim', $proddim);
        $stmt->bindParam(':prodvalid', $prodvalid);
        $stmt->bindParam(':prodaquisicao', $prodaquisicao);
        $stmt->bindParam(':prodcor', $prodcor);
        $stmt->bindParam(':prodapli', $prodapli);
        $stmt->bindParam(':prodapre', $prodapre);
        $stmt->bindParam(':prodout', $prodout);
        $stmt->bindParam(':prodcod', $prodcod); // WHERE clause
        
        // Execução da Query
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $mensagem = "<div class='alert alert-success' role='alert'>Produto **$prodcod** atualizado com sucesso!</div>";
        } else {
            $mensagem = "<div class='alert alert-warning' role='alert'>Nenhum dado alterado no produto **$prodcod** (Pode ser que os dados fossem os mesmos).</div>";
        }

        // Após a atualização, recarrega os dados do produto atualizado (se o código estiver presente)
        if (!empty($prodcod)) {
             $codigo_busca = $prodcod;
             goto busca_produto; // Vai para a lógica de busca para reexibir o formulário com os dados atualizados
        }

    } catch (PDOException $e) {
        $mensagem = "<div class='alert alert-danger' role='alert'>Erro ao atualizar o produto: " . $e->getMessage() . "</div>";
    }

} 

// 4. Verifica se uma BUSCA foi solicitada (método GET)
if (isset($_GET['codigo_busca']) && !empty($_GET['codigo_busca'])) {
    
    busca_produto: // Label para o goto após a atualização
    $codigo_busca = $_GET['codigo_busca'];
    
    // Query para buscar o produto
    $sql_busca = "SELECT * FROM $table_name2 WHERE codigo_produto = :codigo_busca";

    try {
        $stmt_busca = $conn->prepare($sql_busca);
        $stmt_busca->bindParam(':codigo_busca', $codigo_busca);
        $stmt_busca->execute();
        
        $produto = $stmt_busca->fetch(PDO::FETCH_ASSOC);

        if (!$produto) {
            $mensagem .= "<div class='alert alert-danger' role='alert'>Produto com código **$codigo_busca** não encontrado!</div>";
        }

    } catch (PDOException $e) {
        $mensagem .= "<div class='alert alert-danger' role='alert'>Erro ao buscar o produto: " . $e->getMessage() . "</div>";
    }
}
// 5. Fecha a conexão
$conn = null;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
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
                    <a class="nav-link disabled" href="editarproduto.php">Editar Produto</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="movimentacaoproduto.php">Movimentação de Produto</a>
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
        <!--FIm do Menu principal-->

        <form action="editarproduto.php" method="get" class="mb-4">
            <div class="row p-2 text-center">
                <h2>Buscar Produto</h2>
                <?php echo $mensagem; // Exibe mensagens de status (sucesso/erro da atualização ou busca) ?>
            </div>
            <div class="row p-2 align-items-end">
                <div class="col-4">
                    Código do produto para editar:
                    <input type="text" name="codigo_busca" id="codigo_busca" placeholder="Digite o código" class="form-control" required value="<?php echo htmlspecialchars($_GET['codigo_busca'] ?? ''); ?>">
                </div>
                <div class="col-auto">
                    <input type="submit" value="Buscar Produto" class="btn btn-primary">
                </div>
                <div class="col-auto">
                    <a href="editarproduto.php" class="btn btn-secondary">Nova Busca</a>
                </div>
            </div>
        </form>

        <?php if ($produto): ?>
        <form action="editarproduto.php" id="editarproduto" name="editarproduto" method="post">
            <div class="row p-2 text-center">
                <h2>Dados do Produto: <?php echo htmlspecialchars($produto['nome_produto']); ?></h2>
            </div>
            
            <input type="hidden" name="prodcod" value="<?php echo htmlspecialchars($produto['codigo_produto']); ?>">

            <div class="row p-2">
                <div class="col-3">
                    Código do produto (Não editável):<input type="text" value="<?php echo htmlspecialchars($produto['codigo_produto']); ?>" class="form-control" disabled>
                </div>
                <div class="col">
                    Nome do produto:<input type="text" name="prodnome" id="prodnome" placeholder="Digite o nome do produto" class="form-control" required value="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                </div>
            </div>
            <div class="row p-2">
                <div class="col">
                    Quant. de produto:<input type="number" name="prodqt" id="prodqt" placeholder="Digite a quantidade" class="form-control" required value="<?php echo htmlspecialchars($produto['quantidade']); ?>">
                </div>
                <div class="col">
                    Peso, Volume, Comprimento:<input type="text" name="prodmed" id="prodmed" placeholder="Ex: 500" class="form-control" required value="<?php echo htmlspecialchars($produto['peso_volume_comp']); ?>">
                </div>
                <div class="col">
                    Unidade de medida
                    <select id="produnmed" name="produnmed" class="form-select" required>
                        <?php $unidade_atual = $produto['unidade_medida']; ?>
                        <option value="g" <?php echo ($unidade_atual == 'g') ? 'selected' : ''; ?>>(g)Gramas</option>
                        <option value="Kg" <?php echo ($unidade_atual == 'Kg') ? 'selected' : ''; ?>>(Kg)Kilogramas</option>
                        <option value="T" <?php echo ($unidade_atual == 'T') ? 'selected' : ''; ?>>(T)Toneladas</option>
                        <option value="L" <?php echo ($unidade_atual == 'L') ? 'selected' : ''; ?>>(L)Litros</option>
                        <option value="ml" <?php echo ($unidade_atual == 'ml') ? 'selected' : ''; ?>>(ml)Mililitros</option>
                        <option value="M" <?php echo ($unidade_atual == 'M') ? 'selected' : ''; ?>>(M)Metros</option>
                        <option value="cm" <?php echo ($unidade_atual == 'cm') ? 'selected' : ''; ?>>(cm)Centimetros</option>
                    </select>
                </div>
                <div class="col">
                    Dimensões do produto(cm):
                    <input type="text" name="proddim" id="proddim" placeholder="comp. x largura x altura" class="form-control" required value="<?php echo htmlspecialchars($produto['dimensoes']); ?>">
                </div>
            </div>    
                <div class="row p-2">
                <div class="col-2">
                    Data de validade:<input type="date" name="prodvalid" id="prodvalid" class="form-control" value="<?php echo htmlspecialchars($produto['data_validade']); ?>">
                </div>
                <div class="col-2">
                    Data de aquisição:<input type="date" name="prodaquisicao" id="prodaquisicao" class="form-control" required value="<?php echo htmlspecialchars($produto['data_aquisicao']); ?>">
                </div>
                <div class="col-2">
                    Cor:
                    <select id="prodcor" name="prodcor" class="form-select" required>
                        <?php $cor_atual = $produto['cor']; ?>
                        <option value="Branco" <?php echo ($cor_atual == 'Branco') ? 'selected' : ''; ?>>Branco</option>
                        <option value="Preto" <?php echo ($cor_atual == 'Preto') ? 'selected' : ''; ?>>Preto</option>
                        <option value="Cinza" <?php echo ($cor_atual == 'Cinza') ? 'selected' : ''; ?>>Cinza</option>
                        <option value="Bege" <?php echo ($cor_atual == 'Bege') ? 'selected' : ''; ?>>Bege</option>
                        <option value="Vermelho" <?php echo ($cor_atual == 'Vermelho') ? 'selected' : ''; ?>>Vermelho</option>
                        <option value="Amarelo" <?php echo ($cor_atual == 'Amarelo') ? 'selected' : ''; ?>>Amarelo</option>
                        <option value="Azul" <?php echo ($cor_atual == 'Azul') ? 'selected' : ''; ?>>Azul</option>
                        <option value="Verde" <?php echo ($cor_atual == 'Verde') ? 'selected' : ''; ?>>Verde</option>
                        <option value="Laranja" <?php echo ($cor_atual == 'Laranja') ? 'selected' : ''; ?>>Laranja</option>
                        <option value="Roxo" <?php echo ($cor_atual == 'Roxo') ? 'selected' : ''; ?>>Roxo</option>
                        <option value="Outros" <?php echo ($cor_atual == 'Outros') ? 'selected' : ''; ?>>Outros</option>
                    </select>
                </div>
                <div class="col-3">
                    Aplicação do produto:
                    <select id="prodapli" name="prodapli" class="form-select" required>
                        <?php $apli_atual = $produto['aplicacao']; ?>
                        <option value="<?php echo htmlspecialchars($apli_atual); ?>" selected><?php echo htmlspecialchars($apli_atual); ?></option>
                        <option>Alimentação</option>
                        <option>Higiene</option>
                        <option>Brinquedo</option>
                        <option>Medicamento</option>
                        <option>Acessório</option>
                        <option>Outro</option>
                    </select>  
                </div>

                 <div class="col">
                    Apresentação do produto:
                    <select id="prodapre" name="prodapre" class="form-select" required>
                        <?php $apre_atual = $produto['apresentacao']; ?>
                        <option value="Caixa" <?php echo ($apre_atual == 'Caixa') ? 'selected' : ''; ?>>Caixa</option>
                        <option value="Pacote" <?php echo ($apre_atual == 'Pacote') ? 'selected' : ''; ?>>Pacote</option>
                        <option value="Lata" <?php echo ($apre_atual == 'Lata') ? 'selected' : ''; ?>>Lata</option>
                        <option value="Unidade" <?php echo ($apre_atual == 'Unidade') ? 'selected' : ''; ?>>Unidade</option>
                        <option value="Bisnaga" <?php echo ($apre_atual == 'Bisnaga') ? 'selected' : ''; ?>>Bisnaga</option>
                        <option value="Frasco com Válvula" <?php echo ($apre_atual == 'Frasco com Válvula') ? 'selected' : ''; ?>>Frasco com Válvula</option>
                        <option value="Embalagem com 10 unidades" <?php echo ($apre_atual == 'Embalagem com 10 unidades') ? 'selected' : ''; ?>>Embalagem com 10 unidades</option>
                        <option value="Saco" <?php echo ($apre_atual == 'Saco') ? 'selected' : ''; ?>>Saco</option>
                        <option value="Outros" <?php echo ($apre_atual == 'Outros') ? 'selected' : ''; ?>>Outros</option>
                    </select>
                </div>
                
            </div>
            <div class="row p-2">
                <div class="col">
                    Outras Informações:
                    <textarea class="form-control" id="prodout" name="prodout" placeholder="Digite aqui mais descrições sobre o produto"><?php echo htmlspecialchars($produto['outras_informacoes']); ?></textarea>
                </div>

            </div>
            <div class="row p-2">
                <input type="submit" name="atualizar" id="atualizar" value="Editar Produto" class="btn btn-warning">
                <input type="reset" name="cancelar" id="cancelar" value="Desfazer Alterações" class="btn btn-secondary mt-2">
                <a href="editarproduto.php" class="btn btn-danger mt-2">Cancelar e Voltar</a> 
            </div>

        </form>
        <?php endif; ?>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</html>