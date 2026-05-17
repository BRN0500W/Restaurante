CREATE DATABASE IF NOT EXISTS restaurante;
USE restaurante;

CREATE TABLE IF NOT EXISTS pratos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    imagem VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_cliente VARCHAR(100),
    usuario_id INT DEFAULT NULL,
    prato_id INT,
    quantidade INT,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prato_id) REFERENCES pratos(id)
);

CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Senha admin: admin123
INSERT INTO admin (usuario, email, senha) VALUES
('admin', 'admin@email.com', '$2y$10$wH8Q7u6LwYkzYj5Z1QmE.eE9y5RzG6p1Hk1K9gR9ZlKkzYpGQ9v9K')
ON DUPLICATE KEY UPDATE usuario=usuario;

-- =====================
-- PRATOS
-- =====================
INSERT INTO pratos (nome, descricao, preco, imagem) VALUES
('Hambúrguer Artesanal','Pão brioche, carne 180g e cheddar derretido',29.90,'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&q=80'),
('Lasanha Bolonhesa','Lasanha tradicional com carne moída e queijo gratinado',34.90,'https://images.unsplash.com/photo-1574894709920-11b28e7367e3?w=600&q=80'),
('Salada Caesar','Alface americana, frango grelhado, croutons e molho caesar',27.50,'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=600&q=80'),
('Pizza Margherita','Molho de tomate, mussarela fresca e manjericão',39.90,'https://images.unsplash.com/photo-1604068549290-dea0e4a305ca?w=600&q=80'),
('Pizza Calabresa','Molho de tomate, mussarela e calabresa fatiada com cebola roxa',38.90,'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600&q=80'),
('Pizza Pepperoni','Generosa camada de pepperoni sobre molho e mussarela',43.90,'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=600&q=80'),
('Pizza Quatro Queijos','Mussarela, parmesão, gorgonzola e catupiry derretidos',44.90,'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=600&q=80'),
('Pizza Frango com Catupiry','Frango desfiado temperado com catupiry cremoso',42.90,'https://images.unsplash.com/photo-1571407970349-bc81e7e96d47?w=600&q=80'),
('Pizza Portuguesa','Presunto, ovos, cebola, azeitona preta e mussarela',41.90,'https://images.unsplash.com/photo-1593560708920-61dd98c46a4e?w=600&q=80'),
('Pizza Napolitana','Molho de tomate, mussarela, tomate cereja, parmesão e manjericão fresco',40.90,'https://images.unsplash.com/photo-1555072956-7758afb20e8f?w=600&q=80'),
('Pizza Rúcula com Parmesão','Base de mussarela, rúcula fresca, lascas de parmesão e tomate seco',46.90,'https://images.unsplash.com/photo-1506354666786-959d6d497f1a?w=600&q=80'),
('Pizza Atum','Atum ao molho de tomate, cebola, azeitona e mussarela',39.90,'https://images.unsplash.com/photo-1590947132387-155cc02f3212?w=600&q=80'),
('Pizza Vegana','Molho de tomate, abobrinha, pimentão, berinjela, champignon e azeitona',41.90,'https://images.unsplash.com/photo-1459411621453-7b03977f4bfc?w=600&q=80'),
('Pizza Frango com Bacon','Frango grelhado, bacon crocante, catupiry e cebola caramelizada',45.90,'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=600&q=80'),
('Pizza Camarão','Camarões salteados no alho, mussarela e molho de tomate especial',54.90,'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=600&q=80'),
('Pizza Strogonoff','Frango ao strogonoff com mussarela e batata palha',47.90,'https://images.unsplash.com/photo-1588315029754-2dd089d39a1a?w=600&q=80'),
('Pizza de Chocolate','Nutella, morango fresco, banana e granulado de chocolate',37.90,'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=600&q=80');
