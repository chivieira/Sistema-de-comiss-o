-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS south_db;
USE south_db;

-- Tabela de Funcionários
CREATE TABLE IF NOT EXISTS Funcionario (
    id_funcionario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    matricula VARCHAR(50) UNIQUE NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE
);

-- Tabela de Vendas / Valores
CREATE TABLE IF NOT EXISTS Valor (
    id_valor INT AUTO_INCREMENT PRIMARY KEY,
    id_funcionario INT NOT NULL,
    total_vendas DECIMAL(10, 2) NOT NULL,
    mes INT NOT NULL,
    ano INT NOT NULL,
    percentual_comissao DECIMAL(5, 2) NOT NULL,
    FOREIGN KEY (id_funcionario) REFERENCES Funcionario(id_funcionario)
);

-- Dados de exemplo
INSERT INTO Funcionario (nome, matricula, ativo) VALUES
('Carlos Silva', '12345', TRUE),
('Ana Souza', '67890', TRUE),
('João Pereira', '11223', FALSE);

INSERT INTO Valor (id_funcionario, total_vendas, mes, ano, percentual_comissao) VALUES
(1, 5000.00, 1, 2025, 5.0),
(1, 4500.00, 2, 2025, 5.0),
(2, 8000.00, 1, 2025, 7.0),
(2, 7000.00, 2, 2025, 7.0),
(2, 10000.00, 3, 2025, 7.0),
(3, 3000.00, 1, 2025, 4.0);