<?php
// Inicia a sessão para armazenar o nome do usuário após o login
session_start();

// 1. Configuração do Banco de Dados (Baseado nos outros arquivos)
include_once 'conexao.php';
$table_login = "login"; // Nome da tabela de login

// 2. Coleta dos Dados do Formulário via POST (de index.html)
$login_input = $_POST['login'] ?? '';
$senha_input = $_POST['senha'] ?? '';

// 3. Conexão com o Banco de Dados
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Exibe erro de conexão e interrompe o script
    die("Erro de Conexão com o Banco de Dados: " . $e->getMessage());
}

// 4. Preparação da Query SQL para verificar login e senha
// A busca é feita por email OU CPF, e a senha deve corresponder
$sql = "SELECT login_nome FROM $table_login WHERE (login_email = :login_input OR login_cpf = :login_input) AND login_senha = :senha_input";

try {
    $stmt = $conn->prepare($sql);

    // 5. Bind dos Parâmetros
    $stmt->bindParam(':login_input', $login_input);
    $stmt->bindParam(':senha_input', $senha_input);
    
    // 6. Execução da Query
    $stmt->execute();
    
    // 7. Busca o resultado
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Sucesso: Credenciais válidas
        
        // Armazena o nome do usuário na sessão para ser exibido em home.php
        $_SESSION['login_nome'] = $usuario['login_nome'];
        
        // Redireciona para home.php
        header("Location: home.php");
        exit();

    } else {
        // Falha: Credenciais inválidas
        $mensagem_erro = "Erro: E-mail/CPF ou senha inválidos. Tente novamente.";
        
        // Exibe mensagem de erro e link para voltar ao login
        echo "<!DOCTYPE html><html lang='pt-br'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>Erro de Login</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css' rel='stylesheet'></head><body><div class='container mt-5 text-center'><div class='alert alert-danger' role='alert'>$mensagem_erro</div><a href='index.html' class='btn btn-primary'>Voltar ao Login</a></div></body></html>";
    }

} catch (PDOException $e) {
    // Erro na execução da query SQL
    $mensagem_erro_db = "Erro interno do sistema ao verificar login: " . $e->getMessage();
    
    echo "<!DOCTYPE html><html lang='pt-br'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>Erro de Sistema</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css' rel='stylesheet'></head><body><div class='container mt-5 text-center'><div class='alert alert-danger' role='alert'>$mensagem_erro_db</div><a href='index.html' class='btn btn-warning'>Voltar ao Login</a></div></body></html>";
}

// 8. Fecha a conexão
$conn = null;
?>