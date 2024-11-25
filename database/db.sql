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

CREATE TABLE AssociacaoPacientesAlunos (
    id_associacao INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT,
    id_aluno INT,
    FOREIGN KEY (id_paciente) REFERENCES Pacientes(id_paciente),
    FOREIGN KEY (id_aluno) REFERENCES Usuarios(id_usuario)
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
VALUES ('00000000000', 'Administrador', '1980-01-01', 'Masculino', '2023-01-01', 'Administração', 'administrador', NULL, 'admin@example.com', '00000000000', 'admin', '$2y$10$BbYVMgh.KH1NN.h4fWbAYOeX5OJ0.RA1AdXnJFg.CQk9JFSnTDJdS', 1);

-- Inserir usuário professor
INSERT INTO Usuarios (cpf, nome, data_nascimento, genero, data_contratacao, formacao, tipo_usuario, especialidade, email, telefone, login, senha, ativo)
VALUES ('11111111111', 'Professor', '1985-05-15', 'Masculino', '2023-01-01', 'Educação', 'professor', 'Matemática', 'professor@example.com', '11111111111', 'professor', '$2y$10$yyjbrso/cnAV0HDcwoCXUePsF3AVtibCSwCNViZVt28BTHbLx3Qrm', 1),
('88888888888', 'Professor 1', '1980-01-01', 'Masculino', '2023-01-01', 'Educação', 'professor', 'História', 'professor1@example.com', '8888888888', 'professor1', '$2y$10$yyjbrso/cnAV0HDcwoCXUePsF3AVtibCSwCNViZVt28BTHbLx3Qrm', 1),
('99999999999', 'Professor 2', '1981-02-02', 'Feminino', '2023-01-01', 'Educação', 'professor', 'Geografia', 'professor2@example.com', '9999999999', 'professor2', '$2y$10$yyjbrso/cnAV0HDcwoCXUePsF3AVtibCSwCNViZVt28BTHbLx3Qrm', 1),
('10101010101', 'Professor 3', '1982-03-03', 'Outro', '2023-01-01', 'Educação', 'professor', 'Física', 'professor3@example.com', '1010101010', 'professor3', '$2y$10$yyjbrso/cnAV0HDcwoCXUePsF3AVtibCSwCNViZVt28BTHbLx3Qrm', 1);

-- Inserir usuário aluno
INSERT INTO Usuarios (cpf, nome, data_nascimento, genero, data_contratacao, formacao, tipo_usuario, especialidade, email, telefone, login, senha, ativo)
VALUES ('22222222222', 'Aluno', '1995-10-20', 'Feminino', '2023-01-01', 'Psicologia', 'aluno', NULL, 'aluno@example.com', '22222222222', 'aluno', '$2y$10$DEs3QMHFwok6qkIlMC2yD.d271UaZnfDUstFg.PsxwCHdvmL06u1q', 1),
('33333333333', 'Aluno 1', '1996-01-01', 'Masculino', '2023-01-01', 'Psicologia', 'aluno', NULL, 'aluno1@example.com', '3333333333', 'aluno1', '$2y$10$DEs3QMHFwok6qkIlMC2yD.d271UaZnfDUstFg.PsxwCHdvmL06u1q', 1),
('44444444444', 'Aluno 2', '1997-02-02', 'Feminino', '2023-01-01', 'Psicologia', 'aluno', NULL, 'aluno2@example.com', '4444444444', 'aluno2', '$2y$10$DEs3QMHFwok6qkIlMC2yD.d271UaZnfDUstFg.PsxwCHdvmL06u1q', 1),
('55555555555', 'Aluno 3', '1998-03-03', 'Outro', '2023-01-01', 'Psicologia', 'aluno', NULL, 'aluno3@example.com', '5555555555', 'aluno3', '$2y$10$DEs3QMHFwok6qkIlMC2yD.d271UaZnfDUstFg.PsxwCHdvmL06u1q', 1),
('66666666666', 'Aluno 4', '1999-04-04', 'Masculino', '2023-01-01', 'Psicologia', 'aluno', NULL, 'aluno4@example.com', '6666666666', 'aluno4', '$2y$10$DEs3QMHFwok6qkIlMC2yD.d271UaZnfDUstFg.PsxwCHdvmL06u1q', 1),
('77777777777', 'Aluno 5', '2000-05-05', 'Feminino', '2023-01-01', 'Psicologia', 'aluno', NULL, 'aluno5@example.com', '7777777777', 'aluno5', '$2y$10$DEs3QMHFwok6qkIlMC2yD.d271UaZnfDUstFg.PsxwCHdvmL06u1q', 1);

-- Inserir 10 pacientes
INSERT INTO Pacientes (cpf, nome, data_nascimento, genero, estado_civil, email, telefone, contato_emergencia, endereco, escolaridade, ocupacao, necessidade_especial)
VALUES 
('12345678901', 'João Silva', '1990-01-01', 'Masculino', 'Solteiro(a)', 'joao.silva@example.com', '1234567890', 'Maria Silva', 'Rua A, 123', 'Ensino Médio', 'Engenheiro', NULL),
('23456789012', 'Ana Souza', '1985-02-02', 'Feminino', 'Casado(a)', 'ana.souza@example.com', '2345678901', 'Carlos Souza', 'Rua B, 456', 'Ensino Superior', 'Professora', NULL),
('34567890123', 'Marcos Oliveira', '1975-03-03', 'Outro', 'Divorciado(a)', 'marcos.oliveira@example.com', '3456789012', 'Fernanda Oliveira', 'Rua C, 789', 'Ensino Médio', 'Artista', NULL),
('45678901234', 'Lucas Pereira', '2000-04-04', 'Masculino', 'Solteiro(a)', 'lucas.pereira@example.com', '4567890123', 'Juliana Pereira', 'Rua D, 101', 'Ensino Superior', 'Estudante', NULL),
('56789012345', 'Maria Fernandes', '1995-05-05', 'Feminino', 'Casado(a)', 'maria.fernandes@example.com', '5678901234', 'Pedro Fernandes', 'Rua E, 202', 'Ensino Médio', 'Advogada', NULL),
('67890123456', 'Paulo Lima', '1980-06-06', 'Outro', 'Viúvo(a)', 'paulo.lima@example.com', '6789012345', 'Clara Lima', 'Rua F, 303', 'Ensino Superior', 'Empresário', NULL),
('78901234567', 'Carlos Santos', '1992-07-07', 'Masculino', 'Solteiro(a)', 'carlos.santos@example.com', '7890123456', 'Rita Santos', 'Rua G, 404', 'Ensino Médio', 'Atleta', NULL),
('89012345678', 'Fernanda Costa', '1988-08-08', 'Feminino', 'Casado(a)', 'fernanda.costa@example.com', '8901234567', 'João Costa', 'Rua H, 505', 'Ensino Superior', 'Músico', NULL),
('90123456789', 'Roberto Almeida', '1978-09-09', 'Outro', 'Divorciado(a)', 'roberto.almeida@example.com', '9012345678', 'Patrícia Almeida', 'Rua I, 606', 'Ensino Médio', 'Chefe de cozinha', NULL),
('01234567890', 'Juliana Rodrigues', '1998-10-10', 'Masculino', 'Solteiro(a)', 'juliana.rodrigues@example.com', '0123456789', 'Ricardo Rodrigues', 'Rua J, 707', 'Ensino Superior', 'Engenheira de software', NULL);

-- Inserir prontuários para cada paciente
INSERT INTO Prontuarios (id_paciente, id_usuario, data_abertura, historico_familiar, historico_social, consideracoes_finais)
VALUES 
((SELECT id_paciente FROM Pacientes WHERE cpf = '12345678901'), (SELECT id_usuario FROM Usuarios WHERE cpf = '22222222222'), '2023-01-01', 'Pai com histórico de hipertensão, mãe saudável.', 'Trabalha como engenheiro, vive em área urbana.', 'Paciente apresenta bom estado de saúde geral.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '23456789012'), (SELECT id_usuario FROM Usuarios WHERE cpf = '33333333333'), '2023-02-15', 'Histórico de diabetes na família.', 'Professora, vive em área rural.', 'Paciente precisa monitorar níveis de glicose regularmente.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '34567890123'), (SELECT id_usuario FROM Usuarios WHERE cpf = '44444444444'), '2023-03-10', 'Histórico de doenças cardíacas.', 'Artista, vive em área urbana.', 'Paciente deve evitar estresse e fazer check-ups regulares.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '45678901234'), (SELECT id_usuario FROM Usuarios WHERE cpf = '55555555555'), '2023-04-22', 'Mãe com histórico de câncer de mama.', 'Estudante universitário, vive em área urbana.', 'Paciente deve fazer exames preventivos regularmente.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '56789012345'), (SELECT id_usuario FROM Usuarios WHERE cpf = '66666666666'), '2023-05-05', 'Histórico de depressão na família.', 'Advogada, vive em área urbana.', 'Paciente está em acompanhamento psicológico.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '67890123456'), (SELECT id_usuario FROM Usuarios WHERE cpf = '77777777777'), '2023-06-18', 'Pai com histórico de alcoolismo.', 'Empresária, vive em área urbana.', 'Paciente deve evitar consumo de álcool e fazer acompanhamento regular.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '78901234567'), (SELECT id_usuario FROM Usuarios WHERE cpf = '22222222222'), '2023-07-07', 'Histórico de obesidade na família.', 'Atleta, vive em área urbana.', 'Paciente deve manter dieta balanceada e exercícios regulares.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '89012345678'), (SELECT id_usuario FROM Usuarios WHERE cpf = '33333333333'), '2023-08-25', 'Histórico de asma na família.', 'Músico, vive em área urbana.', 'Paciente deve evitar ambientes com poeira e fumaça.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '90123456789'), (SELECT id_usuario FROM Usuarios WHERE cpf = '44444444444'), '2023-09-12', 'Histórico de hipertensão e diabetes.', 'Chefe de cozinha, vive em área urbana.', 'Paciente deve monitorar pressão arterial e níveis de glicose.'),
((SELECT id_paciente FROM Pacientes WHERE cpf = '01234567890'), (SELECT id_usuario FROM Usuarios WHERE cpf = '55555555555'), '2023-10-30', 'Histórico de doenças renais.', 'Engenheiro de software, vive em área urbana.', 'Paciente deve fazer exames regulares para monitorar função renal.');

-- Associar pacientes com alunos
INSERT INTO AssociacaoPacientesAlunos (id_paciente, id_aluno)
VALUES 
((SELECT id_paciente FROM Pacientes WHERE cpf = '12345678901'), (SELECT id_usuario FROM Usuarios WHERE cpf = '22222222222')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '23456789012'), (SELECT id_usuario FROM Usuarios WHERE cpf = '33333333333')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '34567890123'), (SELECT id_usuario FROM Usuarios WHERE cpf = '44444444444')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '45678901234'), (SELECT id_usuario FROM Usuarios WHERE cpf = '55555555555')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '56789012345'), (SELECT id_usuario FROM Usuarios WHERE cpf = '66666666666')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '67890123456'), (SELECT id_usuario FROM Usuarios WHERE cpf = '77777777777')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '78901234567'), (SELECT id_usuario FROM Usuarios WHERE cpf = '22222222222')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '89012345678'), (SELECT id_usuario FROM Usuarios WHERE cpf = '33333333333')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '90123456789'), (SELECT id_usuario FROM Usuarios WHERE cpf = '44444444444')),
((SELECT id_paciente FROM Pacientes WHERE cpf = '01234567890'), (SELECT id_usuario FROM Usuarios WHERE cpf = '55555555555'));