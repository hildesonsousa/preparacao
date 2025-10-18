<?php

// 1. Configuração do Banco de Dados
$servername = "localhost";
$username = "root"; // Mude para o seu usuário do MySQL
$password = "";   // Mude para sua senha do MySQL
$dbname = "saep_preparacao"; // Mude para o nome do seu banco de dados
$table_name2 = "produtos"; // Nome da tabela que armazenará os produtos

// 2. Conexão com o Banco de Dados
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    // Configura o modo de erro do PDO para lançar exceções
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de Conexão: " . $e->getMessage());
}

// 3. Verifica se o formulário foi submetido (se o botão 'cadastrar' foi clicado)
if (isset($_POST['cadastrar'])) {
    
    // 4. Coleta dos Dados do Formulário via POST
    // Usamos o operador de coalescência nula (??) para evitar erros caso algum campo não esteja presente, 
    // embora o HTML use 'required' para a maioria.
    $prodcod     = $_POST['prodcod']     ?? '';
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
    
    // 5. Preparação da Query SQL
    // Usamos prepared statements para segurança (prevenção de SQL Injection)
    $sql = "INSERT INTO $table_name2 (
                codigo_produto, nome_produto, quantidade, peso_volume_comp, 
                unidade_medida, dimensoes, data_validade, data_aquisicao, 
                cor, aplicacao, apresentacao, outras_informacoes
            ) VALUES (
                :prodcod, :prodnome, :prodqt, :prodmed, 
                :produnmed, :proddim, :prodvalid, :prodaquisicao, 
                :prodcor, :prodapli, :prodapre, :prodout
            )";

    try {
        $stmt = $conn->prepare($sql);

        // 6. Bind dos Parâmetros
        $stmt->bindParam(':prodcod', $prodcod);
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

        // 7. Execução da Query
        $stmt->execute();
        
        //após inserir o produto exibir a mensagem e redirecionar para o menu
        //Podemos trocar por uma página a linha abaixo
        echo "<!DOCTYPE html><html lang='pt-br'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>Sucesso</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css' rel='stylesheet'></head><body><div class='container mt-5'><div class='alert alert-success' role='alert'>Produto cadastrado com sucesso!</div><a href='cadastrarproduto.html' class='btn btn-primary'>Voltar ao Cadastro</a></div></body></html>";
        header("refresh:2;url=home.html");
    } catch (PDOException $e) {
        // Em caso de erro na execução da query
        //Podemos trocar por uma página a linha abaixo
        echo "<!DOCTYPE html><html lang='pt-br'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>Erro</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css' rel='stylesheet'></head><body><div class='container mt-5'><div class='alert alert-danger' role='alert'>Erro ao cadastrar o produto: " . $e->getMessage() . "</div><a href='cadastrarproduto.html' class='btn btn-warning'>Tentar Novamente</a></div></body></html>";
    }
} else {
    // Caso a página seja acessada diretamente sem submissão de formulário
    header("Location: cadastrarproduto.html");
    exit();
}

// 8. Fecha a conexão
$conn = null;
?>