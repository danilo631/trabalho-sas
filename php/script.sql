-- SQLBook: Code
-- 1. Criar o banco de dados
CREATE DATABASE IF NOT EXISTS rh_corporativo;
USE rh_corporativo;

-- 2. Criar a tabela funcionarios
CREATE TABLE IF NOT EXISTS funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    rg VARCHAR(12) NOT NULL UNIQUE,
    data_nascimento DATE NOT NULL,
    cargo VARCHAR(255),
    salario DECIMAL(10, 2),
    data_admissao DATE,
    afastamento BOOLEAN DEFAULT 0,
    setor VARCHAR(100),
    email VARCHAR(255) UNIQUE,
    telefone VARCHAR(15),
    ferias BOOLEAN DEFAULT 0
);

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,                                            
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    setor VARCHAR(100) AFTER role,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


INSERT INTO usuarios (username, password, setor, role) VALUES (
    'admin',
    'admin',
    'RH',
    'admin'
);

INSERT INTO usuarios (username, password, setor, role) VALUES (
    'zezao',
    '123',
    'administrativo',
    '123'
);

INSERT INTO usuarios (username, password, setor, role) VALUES (
    'maria',
    '123',
    'Auditoria',
    '123'
);

-- 3. Criar a tabela folha_pagamento
CREATE TABLE IF NOT EXISTS folha_pagamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    funcionario_id INT NOT NULL,
    mes_referencia DATE NOT NULL,
    salario_base DECIMAL(10,2) NOT NULL,
    horas_extras DECIMAL(6,2) DEFAULT 0,
    faltas DECIMAL(6,2) DEFAULT 0,
    descontos DECIMAL(10,2) DEFAULT 0,
    total_liquido DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id)
);

-- 4. Criar a tabela afastamentos
CREATE TABLE IF NOT EXISTS afastamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    funcionario_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    descricao TEXT,
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id)
);

-- 5. Criar a tabela ferias
CREATE TABLE IF NOT EXISTS ferias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    funcionario_id INT NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id) ON DELETE CASCADE
);

-- 6. Criar gatilho após INSERT em ferias
DELIMITER $$

CREATE TRIGGER after_insert_ferias
AFTER INSERT ON ferias
FOR EACH ROW
BEGIN
    IF CURDATE() BETWEEN NEW.data_inicio AND NEW.data_fim THEN
        UPDATE funcionarios
        SET ferias = TRUE
        WHERE id = NEW.funcionario_id;
    END IF;
END$$

DELIMITER ;

-- 7. Criar gatilho após DELETE em ferias
DELIMITER $$

CREATE TRIGGER after_delete_ferias
AFTER DELETE ON ferias
FOR EACH ROW
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM ferias
        WHERE funcionario_id = OLD.funcionario_id
          AND CURDATE() BETWEEN data_inicio AND data_fim
    ) THEN
        UPDATE funcionarios
        SET ferias = FALSE
        WHERE id = OLD.funcionario_id;
    END IF;
END$$

DELIMITER ;

-- 8. Criar gatilho após UPDATE em ferias
DELIMITER $$

CREATE TRIGGER after_update_ferias
AFTER UPDATE ON ferias
FOR EACH ROW
BEGIN
    IF CURDATE() BETWEEN NEW.data_inicio AND NEW.data_fim THEN
        UPDATE funcionarios
        SET ferias = TRUE
        WHERE id = NEW.funcionario_id;
    ELSE
        IF NOT EXISTS (
            SELECT 1 FROM ferias
            WHERE funcionario_id = NEW.funcionario_id
              AND CURDATE() BETWEEN data_inicio AND data_fim
        ) THEN
            UPDATE funcionarios
            SET ferias = FALSE
            WHERE id = NEW.funcionario_id;
        END IF;
    END IF;
END$$

DELIMITER ;

-- 9. Consulta exemplo: contar funcionários em férias
-- (executar separadamente quando quiser ver o resultado)
-- SELECT COUNT(*) AS funcionarios_em_ferias
-- FROM funcionarios
-- WHERE ferias = TRUE;

-- ou (mais preciso, sem depender do campo `ferias`)
-- SELECT COUNT(DISTINCT funcionario_id) AS funcionarios_em_ferias
-- FROM ferias
-- WHERE CURDATE() BETWEEN data_inicio AND data_fim;
