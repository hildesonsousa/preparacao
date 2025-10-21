<?php
session_start();
// Verifica se o usuário está logado
if (!isset($_SESSION['login_nome'])) {
    header("Location: index.html");
    exit();
}
//Armazena o nome do usuario
$nome_usuario = htmlspecialchars($_SESSION['login_nome']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Produtos</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container text-center">
        <!--Exibe o nome do usuário-->
        <h1>Seja bem-vindo <span class="text-info"><?php echo $nome_usuario; ?></span></h1>
        <hr>
        <!--Menu principal-->
        <ul class="justify-content-center list-unstyled">
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
                <a class="nav-link" href="exibirestoque.php">Exibir estoque</a>
            </li>
            <li class="nav-item">
                <a class="nav-link link-danger" href="sairdosistema.php">Sair do sistema</a>
            </li>
        </ul>
        </ul>
        <!--FIm do Menu principal-->
        <hr>
    </div>
    <script src="assets/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>
