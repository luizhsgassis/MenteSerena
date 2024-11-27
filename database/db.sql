CREATE DATABASE MenteSerena;
USE MenteSerena;

CREATE TABLE Pacientes (
    id_paciente INT AUTO_INCREMENT PRIMARY KEY,
    cpf VARCHAR(14) NOT NULL,
    nome VARCHAR(50) NOT NULL,
    data_nascimento DATE NOT NULL,
    genero ENUM('Masculino', 'Feminino', 'Outro') NOT NULL,
    estado_civil ENUM('Solteiro(a)', 'Casado(a)', 'Divorciado(a)', 'Viúvo(a)') NOT NULL,
    email VARCHAR(50) NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    contato_emergencia VARCHAR(50) NOT NULL,
    endereco VARCHAR(50) NOT NULL,
    escolaridade VARCHAR(20) NOT NULL,
    ocupacao VARCHAR(50) NOT NULL,
    necessidade_especial VARCHAR(20)
);

CREATE TABLE Usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    cpf VARCHAR(14) NOT NULL,
    nome VARCHAR(50) NOT NULL,
    data_nascimento DATE NOT NULL,
    genero ENUM('Masculino', 'Feminino', 'Outro') NOT NULL,
    data_contratacao DATE NOT NULL,
    formacao VARCHAR(50) NOT NULL,
    tipo_usuario ENUM('aluno', 'professor', 'administrador') NOT NULL,
    especialidade VARCHAR(20),
    email VARCHAR(50) NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    login VARCHAR(20) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    ativo BOOLEAN NOT NULL
);

CREATE TABLE Professores (
    id_professor INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
);

CREATE TABLE AssociacaoPacientesAlunos (
    id_associacao INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT,
    id_aluno INT,
    FOREIGN KEY (id_paciente) REFERENCES Pacientes(id_paciente),
    FOREIGN KEY (id_aluno) REFERENCES Usuarios(id_usuario)
);

CREATE TABLE AssociacaoAlunosProfessores (
    id_associacao INT AUTO_INCREMENT PRIMARY KEY,
    id_aluno INT,
    id_professor INT,
    FOREIGN KEY (id_aluno) REFERENCES Usuarios(id_usuario),
    FOREIGN KEY (id_professor) REFERENCES Professores(id_professor)
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
    id_professor INT,
    data DATE NOT NULL,
    registro_sessao TEXT,
    anotacoes TEXT,
    rascunho BOOLEAN NOT NULL DEFAULT 1,
    FOREIGN KEY (id_prontuario) REFERENCES Prontuarios(id_prontuario),
    FOREIGN KEY (id_paciente) REFERENCES Pacientes(id_paciente),
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario),
    FOREIGN KEY (id_professor) REFERENCES Professores(id_professor)
);

CREATE TABLE ArquivosDigitalizados (
    id_arquivo INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT,
    id_usuario INT,
    id_sessao INT,
    tipo_documento VARCHAR(20) NOT NULL,
    data_upload DATE NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
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
VALUES ('000.000.000-00', 'Administrador', '1980-01-01', 'Masculino', '2023-01-01', 'Administração', 'administrador', NULL, 'admin@example.com', '(00) 00000-0000', 'admin', '$2y$10$BbYVMgh.KH1NN.h4fWbAYOeX5OJ0.RA1AdXnJFg.CQk9JFSnTDJdS', 1);

-- Inserir usuário professor
INSERT INTO Usuarios (cpf, nome, data_nascimento, genero, data_contratacao, formacao, tipo_usuario, especialidade, email, telefone, login, senha, ativo)
VALUES ('111.111.111-11', 'Professor', '1985-05-15', 'Masculino', '2023-01-01', 'Educação', 'professor', NULL, 'professor@example.com', '(11) 11111-1111', 'professor', '$2y$10$yyjbrso/cnAV0HDcwoCXUePsF3AVtibCSwCNViZVt28BTHbLx3Qrm', 1);

-- Inserir professores na tabela Professores
INSERT INTO Professores (id_usuario)
VALUES 
((SELECT id_usuario FROM Usuarios WHERE cpf = '111.111.111-11'));

-- Inserir usuário aluno
INSERT INTO Usuarios (cpf, nome, data_nascimento, genero, data_contratacao, formacao, tipo_usuario, especialidade, email, telefone, login, senha, ativo)
VALUES ('222.222.222-22', 'Aluno', '1995-10-20', 'Masculino', '2023-01-01', 'Psicologia', 'aluno', NULL, 'aluno@example.com', '(22) 22222-2222', 'aluno', '$2y$10$DEs3QMHFwok6qkIlMC2yD.d271UaZnfDUstFg.PsxwCHdvmL06u1q', 1),
('333.333.333-33', 'Bruno Lima', '1996-11-11', 'Masculino', '2023-01-01', 'Psicologia', 'aluno', NULL, 'bruno.lima@example.com', '(33) 33333-3333', 'bruno', '$2y$10$DEs3QMHFwok6qkIlMC2yD.d271UaZnfDUstFg.PsxwCHdvmL06u1q', 1),
('444.444.444-44', 'Carla Souza', '1997-12-12', 'Feminino', '2023-01-01', 'Psicologia', 'aluno', NULL, 'carla.souza@example.com', '(44) 44444-4444', 'carla', '$2y$10$DEs3QMHFwok6qkIlMC2yD.d271UaZnfDUstFg.PsxwCHdvmL06u1q', 1),
('555.555.555-55', 'Diego Oliveira', '1998-01-13', 'Outro', '2023-01-01', 'Psicologia', 'aluno', NULL, 'diego.oliveira@example.com', '(55) 55555-5555', 'diego', '$2y$10$DEs3QMHFwok6qkIlMC2yD.d271UaZnfDUstFg.PsxwCHdvmL06u1q', 1),
('666.666.666-66', 'Elisa Fernandes', '1999-02-14', 'Masculino', '2023-01-01', 'Psicologia', 'aluno', NULL, 'elisa.fernandes@example.com', '(66) 66666-6666', 'elisa', '$2y$10$DEs3QMHFwok6qkIlMC2yD.d271UaZnfDUstFg.PsxwCHdvmL06u1q', 1),
('777.777.777-77', 'Felipe Costa', '2000-03-15', 'Feminino', '2023-01-01', 'Psicologia', 'aluno', NULL, 'felipe.costa@example.com', '(77) 77777-7777', 'felipe', '$2y$10$DEs3QMHFwok6qkIlMC2yD.d271UaZnfDUstFg.PsxwCHdvmL06u1q', 1);

-- Inserir 10 pacientes
INSERT INTO Pacientes (cpf, nome, data_nascimento, genero, estado_civil, email, telefone, contato_emergencia, endereco, escolaridade, ocupacao, necessidade_especial)
VALUES 
('123.456.789-01', 'João Silva', '1990-01-01', 'Masculino', 'Solteiro(a)', 'joao.silva@example.com', '(12) 34567-8901', 'Maria Silva', 'Rua A, 123', 'Ensino Médio', 'Engenheiro', NULL),
('234.567.890-12', 'Ana Souza', '1985-02-02', 'Feminino', 'Casado(a)', 'ana.souza@example.com', '(23) 45678-9012', 'Carlos Souza', 'Rua B, 456', 'Ensino Superior', 'Professora', NULL),
('345.678.901-23', 'Marcos Oliveira', '1975-03-03', 'Outro', 'Divorciado(a)', 'marcos.oliveira@example.com', '(34) 56789-0123', 'Fernanda Oliveira', 'Rua C, 789', 'Ensino Médio', 'Artista', NULL),
('456.789.012-34', 'Lucas Pereira', '2000-04-04', 'Masculino', 'Solteiro(a)', 'lucas.pereira@example.com', '(45) 67890-1234', 'Juliana Pereira', 'Rua D, 101', 'Ensino Superior', 'Estudante', NULL),
('567.890.123-45', 'Maria Fernandes', '1995-05-05', 'Feminino', 'Casado(a)', 'maria.fernandes@example.com', '(56) 78901-2345', 'Pedro Fernandes', 'Rua E, 202', 'Ensino Médio', 'Advogada', NULL),
('678.901.234-56', 'Paulo Lima', '1980-06-06', 'Outro', 'Viúvo(a)', 'paulo.lima@example.com', '(67) 89012-3456', 'Clara Lima', 'Rua F, 303', 'Ensino Superior', 'Empresário', NULL),
('789.012.345-67', 'Carlos Santos', '1992-07-07', 'Masculino', 'Solteiro(a)', 'carlos.santos@example.com', '(78) 90123-4567', 'Rita Santos', 'Rua G, 404', 'Ensino Médio', 'Atleta', NULL),
('890.123.456-78', 'Fernanda Costa', '1988-08-08', 'Feminino', 'Casado(a)', 'fernanda.costa@example.com', '(89) 01234-5678', 'João Costa', 'Rua H, 505', 'Ensino Superior', 'Músico', NULL),
('901.234.567-89', 'Roberto Almeida', '1978-09-09', 'Outro', 'Divorciado(a)', 'roberto.almeida@example.com', '(90) 12345-6789', 'Patrícia Almeida', 'Rua I, 606', 'Ensino Médio', 'Chefe de cozinha', NULL),
('012.345.678-90', 'Juliana Rodrigues', '1998-10-10', 'Masculino', 'Solteiro(a)', 'juliana.rodrigues@example.com', '(01) 23456-7890', 'Ricardo Rodrigues', 'Rua J, 707', 'Ensino Superior', 'Engenheira de software', NULL);

-- Inserir prontuários para cada paciente
INSERT INTO Prontuarios (id_paciente, id_usuario, data_abertura, historico_familiar, historico_social, consideracoes_finais)
VALUES 
((SELECT id_paciente FROM Pacientes WHERE cpf = '123.456.789-01'), (SELECT id_usuario FROM Usuarios WHERE cpf = '222.222.222-22'), '2023-01-01', 'Pai com histórico de hipertensão, mãe saudável.', 'Trabalha como engenheiro, vive em área urbana.', 'Paciente apresenta bom estado de saúde geral.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '234.567.890-12'), (SELECT id_usuario FROM Usuarios WHERE cpf = '333.333.333-33'), '2023-02-15', 'Histórico de diabetes na família.', 'Professora, vive em área rural.', 'Paciente precisa monitorar níveis de glicose regularmente.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '345.678.901-23'), (SELECT id_usuario FROM Usuarios WHERE cpf = '444.444.444-44'), '2023-03-10', 'Histórico de doenças cardíacas.', 'Artista, vive em área urbana.', 'Paciente deve evitar estresse e fazer check-ups regulares.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '456.789.012-34'), (SELECT id_usuario FROM Usuarios WHERE cpf = '555.555.555-55'), '2023-04-22', 'Mãe com histórico de câncer de mama.', 'Estudante universitário, vive em área urbana.', 'Paciente deve fazer exames preventivos regularmente.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '567.890.123-45'), (SELECT id_usuario FROM Usuarios WHERE cpf = '666.666.666-66'), '2023-05-05', 'Histórico de depressão na família.', 'Advogada, vive em área urbana.', 'Paciente está em acompanhamento psicológico.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '678.901.234-56'), (SELECT id_usuario FROM Usuarios WHERE cpf = '777.777.777-77'), '2023-06-18', 'Pai com histórico de alcoolismo.', 'Empresária, vive em área urbana.', 'Paciente deve evitar consumo de álcool e fazer acompanhamento regular.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '789.012.345-67'), (SELECT id_usuario FROM Usuarios WHERE cpf = '222.222.222-22'), '2023-07-07', 'Histórico de obesidade na família.', 'Atleta, vive em área urbana.', 'Paciente deve manter dieta balanceada e exercícios regulares.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '890.123.456-78'), (SELECT id_usuario FROM Usuarios WHERE cpf = '333.333.333-33'), '2023-08-25', 'Histórico de asma na família.', 'Músico, vive em área urbana.', 'Paciente deve evitar ambientes com poeira e fumaça.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '901.234.567-89'), (SELECT id_usuario FROM Usuarios WHERE cpf = '444.444.444-44'), '2023-09-12', 'Histórico de hipertensão e diabetes.', 'Chefe de cozinha, vive em área urbana.', 'Paciente deve monitorar pressão arterial e níveis de glicose.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '012.345.678-90'), (SELECT id_usuario FROM Usuarios WHERE cpf = '555.555.555-55'), '2023-10-30', 'Histórico de doenças renais.', 'Engenheiro de software, vive em área urbana.', 'Paciente deve fazer exames regulares para monitorar função renal.');

-- Associar pacientes com alunos
INSERT INTO AssociacaoPacientesAlunos (id_paciente, id_aluno)
VALUES 
((SELECT id_paciente FROM Pacientes WHERE cpf = '123.456.789-01'), (SELECT id_usuario FROM Usuarios WHERE cpf = '222.222.222-22')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '234.567.890-12'), (SELECT id_usuario FROM Usuarios WHERE cpf = '333.333.333-33')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '345.678.901-23'), (SELECT id_usuario FROM Usuarios WHERE cpf = '444.444.444-44')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '456.789.012-34'), (SELECT id_usuario FROM Usuarios WHERE cpf = '555.555.555-55')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '567.890.123-45'), (SELECT id_usuario FROM Usuarios WHERE cpf = '666.666.666-66')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '678.901.234-56'), (SELECT id_usuario FROM Usuarios WHERE cpf = '777.777.777-77')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '789.012.345-67'), (SELECT id_usuario FROM Usuarios WHERE cpf = '222.222.222-22')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '890.123.456-78'), (SELECT id_usuario FROM Usuarios WHERE cpf = '333.333.333-33')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '901.234.567-89'), (SELECT id_usuario FROM Usuarios WHERE cpf = '444.444.444-44')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '012.345.678-90'), (SELECT id_usuario FROM Usuarios WHERE cpf = '555.555.555-55'));