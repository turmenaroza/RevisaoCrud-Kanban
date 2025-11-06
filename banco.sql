CREATE DATABASE Tarefas;
USE Tarefas;


CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE
);


CREATE TABLE tarefas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    descricao TEXT NOT NULL,
    nome_setor VARCHAR(100) NOT NULL,
    prioridade ENUM('baixa', 'média', 'alta') NOT NULL,
    data_cadastro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('a fazer', 'fazendo', 'pronto') NOT NULL DEFAULT 'a fazer',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

INSERT INTO usuarios (nome, email) VALUES
('Julia Roza', 'julia_roza@gmail.com'),
('Maria Luiza', 'maria.luiza@gmail.com'),
('Victoria Fernanda', 'Victoria.fer@gmail.com');

INSERT INTO tarefas (usuario_id, descricao, nome_setor, prioridade, status) VALUES
(1, 'Revisar relatórios financeiros do último trimestre', 'Financeiro', 'alta', 'a fazer'),
(1, 'Atualizar planilha de controle de despesas', 'Financeiro', 'média', 'fazendo'),
(2, 'Organizar treinamento de integração para novos funcionários', 'RH', 'baixa', 'a fazer'),
(2, 'Responder solicitações de férias pendentes', 'RH', 'média', 'pronto'),
(3, 'Corrigir falha no sistema de login do portal interno', 'TI', 'alta', 'fazendo'),
(3, 'Testar novo módulo de cadastro de clientes', 'Comercial', 'média', 'a fazer');