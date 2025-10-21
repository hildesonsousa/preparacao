/*Informações do Banco de dados*/
/*
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "saep_preparacao";
$table1_name = "login";
$table2_name = "produtos";
$table3_name = "movimentacoes"
*/

/*Criando o Schema*/
CREATE SCHEMA `saep_preparacao` ;

/*Criando a tabela de login*/
CREATE TABLE `saep_preparacao`.`login` (
  `login_id` INT NOT NULL,
  `login_nome` VARCHAR(45) NOT NULL,
  `login_email` VARCHAR(255) NOT NULL,
  `login_senha` VARCHAR(45) NOT NULL,
  `login_cpf` VARCHAR(11) NOT NULL,
  PRIMARY KEY (`login_id`));

/*Criando a tabela para armazenar os produtos*/
CREATE TABLE `saep_preparacao`.`produtos` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_produto VARCHAR(50) UNIQUE NOT NULL,
    nome_produto VARCHAR(255) NOT NULL,
    quantidade INT NOT NULL,
    peso_volume_comp VARCHAR(50),
    unidade_medida VARCHAR(10),
    dimensoes VARCHAR(100),
    data_validade DATE,
    data_aquisicao DATE,
    cor VARCHAR(50),
    aplicacao VARCHAR(50),
    apresentacao VARCHAR(50),
    outras_informacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


/*Populando a tabela login*/
INSERT INTO `saep_preparacao`.`login`
    (`login_id`, `login_nome`, `login_email`, `login_senha`, `login_cpf`)
VALUES
    (1, 'Hildeson Sousa', 'hilsou@exemplo.com', 'senha123', '12345678901'),
    (2, 'Cristiano Guedes', 'crisgue@exemplo.com', '123senha', '23456789012'),
    (3, 'Amanda Codognato', 'amacodog@exemplo.com', 'senha321', '34567890123');


/*Populando a tabela produtos*/
INSERT INTO `saep_preparacao`.`produtos` (
    codigo_produto,
    nome_produto,
    quantidade,
    peso_volume_comp,
    unidade_medida,
    dimensoes,
    data_validade,
    data_aquisicao,
    cor,
    aplicacao,
    apresentacao,
    outras_informacoes
)
VALUES
    (
        'RACAO001',
        'Ração Premium para Cães Adultos - Sabor Carne',
        150,
        '15',
        'KG',
        '60x40x15',
        '2026-10-01',
        '2024-03-10',
        'Marrom',
        'Alimentação para Cães',
        'Saco',
        'Fórmula com ômega 3 e 6. Indicado para raças médias e grandes.'
    ),
    (
        'AREIA005',
        'Areia Sanitária Ultra Absorvente para Gatos',
        500,
        '4',
        'KG',
        '30x20x10',
        '2028-12-31',
        '2024-03-15',
        'Branca',
        'Higiene para Gatos',
        'Pacote',
        'Com fragrância suave e excelente formação de torrões.'
    ),
    (
        'BRINQ010',
        'Bola Maciça de Borracha para Cães - Tamanho M',
        80,
        '150',
        'G',
        '7',
        NULL, -- Não tem data de validade
        '2024-03-20',
        'Azul',
        'Brinquedo / Entretenimento',
        'Unidade',
        'Material atóxico e resistente a mordidas.'
    ),
    (
        'SHAMPOO02',
        'Shampoo Neutro para Filhotes - 500ml',
        120,
        '500',
        'ML',
        '20x8x4',
        '2027-05-20',
        '2024-03-25',
        'Transparente',
        'Higiene e Banho',
        'Frasco com Válvula',
        'Hipoalergênico. Sem sal. Ideal para peles sensíveis.'
    ),
    (
        'OSSO003',
        'Osso Palito Comestível de Couro Bovino',
        300,
        '20',
        'G',
        '15',
        '2025-12-01',
        '2024-03-30',
        'Bege',
        'Petisco / Mastigação',
        'Embalagem com 10 unidades',
        'Auxilia na limpeza dos dentes e fortalece a mandíbula.'
    );



 /*Criar tabela para armazenar as movimetações*/  
CREATE TABLE `saep_preparacao`.`movimentacoes` (
  `mov_id` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo_produto` VARCHAR(50) NOT NULL, -- Coluna para ligar a movimentação ao produto
  `tipo_movimentacao` VARCHAR(10) NOT NULL,  -- Tipo de movimentação: 'Entrada' ou 'Saída'
  `quantidade` INT NOT NULL, -- Quantidade de itens que entraram ou saíram
  `data_movimentacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,-- Data e hora do registro da movimentação
CONSTRAINT `fk_movimentacoes_produtos`
    FOREIGN KEY (`codigo_produto`) -- Adiciona uma chave estrangeira para garantir que o código do produto seja válido
    REFERENCES `produtos` (`codigo_produto`) 
    ON DELETE RESTRICT -- ON DELETE RESTRICT: Impede a exclusão de um produto enquanto houver movimentações registradas para ele.
    ON UPDATE CASCADE
);
/*
--Opcional Inserir movimentações no banco para testar
  INSERT INTO `movimentacoes` (`codigo_produto`, `tipo_movimentacao`, `quantidade`) 
  VALUES ('RACAO001', 'Entrada', 50);
  INSERT INTO `movimentacoes` (`codigo_produto`, `tipo_movimentacao`, `quantidade`) 
  VALUES ('RACAO001', 'Saída', 90);
*/

    /*Exibir todos os produtos*/
    SELECT * FROM `saep_preparacao`.`produto`;

   /*Exibir todos os login*/
    SELECT * FROM `saep_preparacao`.`login`;

    /*Exibir todas as movimentações*/
    SELECT * FROM `saep_preparacao`.`movimentacoes`;


    /*Comando para adicionar campo em tabela*/
    /*
    ALTER TABLE `saep_preparacao`.`produtos`
    ADD COLUMN preco DECIMAL(10, 2);
    */