CREATE DATABASE MenteSerena;
USE MenteSerena;

CREATE TABLE Pacientes (
    id_paciente INT AUTO_INCREMENT PRIMARY KEY,
    cpf VARCHAR(11) NOT NULL,
    nome VARCHAR(50) NOT NULL,
    data_nascimento DATE NOT NULL,
    genero ENUM('Masculino', 'Feminino', 'Outro') NOT NULL,
    estado_civil ENUM('Solteiro(a)', 'Casado(a)', 'Divorciado(a)', 'Viúvo(a)') NOT NULL,
    email VARCHAR(50) NOT NULL,
    telefone VARCHAR(11) NOT NULL,
    contato_emergencia VARCHAR(50) NOT NULL,
    endereco VARCHAR(50) NOT NULL,
    escolaridade VARCHAR(20) NOT NULL,
    ocupacao VARCHAR(50) NOT NULL,
    necessidade_especial VARCHAR(20)
);

CREATE TABLE Usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    cpf VARCHAR(11) NOT NULL,
    nome VARCHAR(50) NOT NULL,
    data_nascimento DATE NOT NULL,
    genero ENUM('Masculino', 'Feminino', 'Outro') NOT NULL,
    data_contratacao DATE NOT NULL,
    formacao VARCHAR(50) NOT NULL,
    tipo_usuario ENUM('aluno', 'professor', 'administrador') NOT NULL,
    especialidade VARCHAR(20),
    email VARCHAR(50) NOT NULL,
    telefone VARCHAR(11) NOT NULL,
    login VARCHAR(20) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    ativo BOOLEAN NOT NULL
);

CREATE TABLE Prontuarios (
    id_prontuario INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT,
    id_usuario INT,
    data_abertura DATE NOT NULL,
    historico_familiar TEXT,
    historico_social TEXT,
    consideracoes_finais TEXT,
    FOREIGN KEY (id_paciente) REFERENCES Pacientes(id_paciente),
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
);

CREATE TABLE Sessoes (
    id_sessao INT AUTO_INCREMENT PRIMARY KEY,
    id_prontuario INT,
    id_paciente INT,
    id_usuario INT,
    data DATE NOT NULL,
    registro_sessao TEXT,
    anotacoes TEXT,
    rascunho BOOLEAN NOT NULL DEFAULT 1,
    FOREIGN KEY (id_prontuario) REFERENCES Prontuarios(id_prontuario),
    FOREIGN KEY (id_paciente) REFERENCES Pacientes(id_paciente),
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
);

CREATE TABLE ArquivosDigitalizados (
    id_arquivo INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT,
    id_usuario INT,
    id_sessao INT,
    tipo_documento VARCHAR(20) NOT NULL,
    data_upload DATE NOT NULL,
    arquivo LONGBLOB,
    FOREIGN KEY (id_paciente) REFERENCES Pacientes(id_paciente),
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario),
    FOREIGN KEY (id_sessao) REFERENCES Sessoes(id_sessao)
);

CREATE TABLE Avisos (
    id_aviso INT AUTO_INCREMENT PRIMARY KEY,
    id_sessao INT,
    id_usuario INT,
    mensagem TEXT NOT NULL,
    data DATE NOT NULL,
    status ENUM('pendente', 'resolvido') NOT NULL,
    FOREIGN KEY (id_sessao) REFERENCES Sessoes(id_sessao),
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
);

-- Inserir usuário administrador
INSERT INTO Usuarios (cpf, nome, data_nascimento, genero, data_contratacao, formacao, tipo_usuario, especialidade, email, telefone, login, senha, ativo)
VALUES ('00000000000', 'Administrador', '1980-01-01', 'Masculino', '2023-01-01', 'Administração', 'administrador', NULL, 'admin@example.com', '0000000000', 'admin', '$2y$10$BbYVMgh.KH1NN.h4fWbAYOeX5OJ0.RA1AdXnJFg.CQk9JFSnTDJdS', 1);

-- Inserir usuário professor
INSERT INTO Usuarios (cpf, nome, data_nascimento, genero, data_contratacao, formacao, tipo_usuario, especialidade, email, telefone, login, senha, ativo)
VALUES ('11111111111', 'Professor', '1985-05-15', 'Masculino', '2023-01-01', 'Educação', 'professor', 'Matemática', 'professor@example.com', '1111111111', 'professor', '$2y$10$yyjbrso/cnAV0HDcwoCXUePsF3AVtibCSwCNViZVt28BTHbLx3Qrm', 1);

-- Inserir usuário aluno
INSERT INTO Usuarios (cpf, nome, data_nascimento, genero, data_contratacao, formacao, tipo_usuario, especialidade, email, telefone, login, senha, ativo)
VALUES ('22222222222', 'Aluno', '1995-10-20', 'Feminino', '2023-01-01', 'Psicologia', 'aluno', NULL, 'aluno@example.com', '2222222222', 'aluno', '$2y$10$DEs3QMHFwok6qkIlMC2yD.d271UaZnfDUstFg.PsxwCHdvmL06u1q', 1);