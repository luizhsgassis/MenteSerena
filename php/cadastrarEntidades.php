<?php
session_start();

include('../config.php');
include('functions.php');

// Verifica se o usuário é administrador ou professor
if ($_SESSION['UsuarioNivel'] == 'aluno') {
    header("Location: /MenteSerena-master/index.php");
    exit;
}

$erro_cadastro = '';
$sucesso_cadastro = '';

// Obtém o tipo de cadastro a ser exibido
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'pacientes';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cpf = trim($_POST["cpf"]);
    $nome = trim($_POST["nome"]);
    $dataNascimento = trim($_POST["data_nascimento"]);
    $genero = trim($_POST["genero"]);
    $email = trim($_POST["email"]);
    $telefone = trim($_POST["telefone"]);

    // Validações comuns
    if (!validateCPF($cpf)) {
        $erro_cadastro = "CPF inválido. Deve conter exatamente 11 dígitos.";
    } elseif (empty($nome)) {
        $erro_cadastro = "Por favor, preencha o nome.";
    } elseif (empty($dataNascimento) || !validateDate($dataNascimento)) {
        $erro_cadastro = "Por favor, preencha uma data de nascimento válida.";
    } elseif (empty($genero)) {
        $erro_cadastro = "Por favor, selecione o gênero.";
    } elseif (empty($email) || !validateEmail($email)) {
        $erro_cadastro = "E-mail inválido.";
    } elseif (!validateTelefone($telefone)) {
        $erro_cadastro = "Telefone inválido. Deve conter exatamente 11 dígitos.";
    } else {
        switch ($tipo) {
            case 'pacientes':
                $estadoCivil = trim($_POST["estado_civil"]);
                $contatoEmergencia = trim($_POST["contato_emergencia"]);
                $endereco = trim($_POST["endereco"]);
                $escolaridade = trim($_POST["escolaridade"]);
                $ocupacao = trim($_POST["ocupacao"]);
                $necessidadeEspecial = trim($_POST["necessidade_especial"]);

                if (empty($estadoCivil)) {
                    $erro_cadastro = "Por favor, selecione o estado civil.";
                } elseif (empty($contatoEmergencia)) {
                    $erro_cadastro = "Por favor, preencha o contato de emergência.";
                } elseif (empty($endereco)) {
                    $erro_cadastro = "Por favor, preencha o endereço.";
                } elseif (empty($escolaridade)) {
                    $erro_cadastro = "Por favor, preencha a escolaridade.";
                } elseif (empty($ocupacao)) {
                    $erro_cadastro = "Por favor, preencha a ocupação.";
                } else {
                    $query = "INSERT INTO Pacientes (cpf, nome, data_nascimento, genero, estado_civil, email, telefone, contato_emergencia, endereco, escolaridade, ocupacao, necessidade_especial) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ssssssssssss", $cpf, $nome, $dataNascimento, $genero, $estadoCivil, $email, $telefone, $contatoEmergencia, $endereco, $escolaridade, $ocupacao, $necessidadeEspecial);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $sucesso_cadastro = "Paciente cadastrado com sucesso!";
                    } else {
                        $erro_cadastro = "Erro ao cadastrar paciente: " . mysqli_error($conn);
                    }
                }
                break;

            case 'professores':
                $dataContratacao = trim($_POST["data_contratacao"]);
                $formacao = trim($_POST["formacao"]);
                $especialidade = trim($_POST["especialidade"]);

                if (empty($dataContratacao) || !validateDate($dataContratacao)) {
                    $erro_cadastro = "Por favor, preencha uma data de contratação válida.";
                } elseif (empty($formacao)) {
                    $erro_cadastro = "Por favor, preencha a formação.";
                } else {
                    $loginPlaceholder1 = substr($nome, 0, 3);
                    $loginPlaceholder2 = substr($telefone, -3);
                    $loginTemporario = $loginPlaceholder1 . $loginPlaceholder2;

                    $senhaPlaceholder1 = substr($cpf, 0, 3);
                    $senhaPlaceholder2 = substr($dataNascimento, -2);
                    $senhaTemporaria = $senhaPlaceholder1 . $senhaPlaceholder2;
                    $senhaHash = password_hash($senhaTemporaria, PASSWORD_DEFAULT);

                    // Trata o campo especialidade como NULL se estiver vazio
                    $especialidade = !empty($especialidade) ? $especialidade : NULL;

                    $query = "INSERT INTO Usuarios (cpf, nome, data_nascimento, genero, data_contratacao, formacao, especialidade, email, telefone, login, senha, tipo_usuario, ativo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'professor', 1)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "sssssssssss", $cpf, $nome, $dataNascimento, $genero, $dataContratacao, $formacao, $especialidade, $email, $telefone, $loginTemporario, $senhaHash);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $sucesso_cadastro = "Professor cadastrado com sucesso!";
                    } else {
                        $erro_cadastro = "Erro ao cadastrar professor: " . mysqli_error($conn);
                    }
                }
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastrar <?php echo ucfirst($tipo); ?></title>
  <link rel="stylesheet" href="../estilo.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/mainContent.css">
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validação do CPF
        document.getElementById('cpf').addEventListener('blur', function() {
            var cpf = this.value;
            var cpfError = document.getElementById('cpfError');
            if (cpf.length !== 11 || !/^\d+$/.test(cpf)) {
                cpfError.textContent = 'CPF inválido. Deve conter exatamente 11 dígitos.';
            } else {
                cpfError.textContent = '';
            }
        });

        // Validação da data de nascimento
        document.getElementById('data_nascimento').addEventListener('blur', function() {
            var dataNascimento = this.value;
            var dataNascimentoError = document.getElementById('dataNascimentoError');
            if (!isValidDate(dataNascimento) || isFutureDate(dataNascimento)) {
                dataNascimentoError.textContent = 'Por favor, preencha uma data de nascimento válida.';
            } else {
                dataNascimentoError.textContent = '';
            }
        });

        // Validação da data de contratação
        var dataContratacao = document.getElementById('data_contratacao');
        if (dataContratacao) {
            dataContratacao.addEventListener('blur', function() {
                var dataContratacaoError = document.getElementById('dataContratacaoError');
                if (!isValidDate(this.value)) {
                    dataContratacaoError.textContent = 'Por favor, preencha uma data de contratação válida.';
                } else {
                    dataContratacaoError.textContent = '';
                }
            });
        }

        // Função para validar a data
        function isValidDate(dateString) {
            var regEx = /^\d{4}-\d{2}-\d{2}$/;
            if (!dateString.match(regEx)) return false;  // Formato inválido
            var d = new Date(dateString);
            var dNum = d.getTime();
            if (!dNum && dNum !== 0) return false; // Data inválida
            return d.toISOString().slice(0, 10) === dateString;
        }

        // Função para verificar se a data é futura
        function isFutureDate(dateString) {
            var today = new Date();
            var inputDate = new Date(dateString);
            return inputDate > today;
        }
    });
  </script>
</head>
<body>
  <div class="body_section">
    <!-- Inclui o conteúdo de sidebar.php -->
    <?php include('sidebar.php'); ?>
    <main>
      <div class="main_title"><h2>Cadastrar <?php echo ucfirst($tipo); ?></h2></div>
      <div class="content">
        <?php if (!empty($erro_cadastro)): ?>
          <div class="error"><?php echo $erro_cadastro; ?></div>
        <?php endif; ?>
        <?php if (!empty($sucesso_cadastro)): ?>
          <div class="success"><?php echo $sucesso_cadastro; ?></div>
        <?php endif; ?>
        <form class="main_form" action="cadastrarEntidades.php?tipo=<?php echo $tipo; ?>" method="post">
          <?php
          switch ($tipo) {
              case 'pacientes':
                  ?>
                  <div class="form_group">
                    <div class="form_input">
                      <label for="cpf">CPF:</label>
                      <input type="text" name="cpf" id="cpf" maxlength="11" required>
                      <span id="cpfError" class="error"></span>
                    </div>
                    <div class="form_input">
                      <label for="nome">Nome:</label>
                      <input type="text" name="nome" id="nome" required>
                    </div>
                    <div class="form_input">
                      <label for="data_nascimento">Data de Nascimento:</label>
                      <input type="date" name="data_nascimento" id="data_nascimento" required>
                      <span id="dataNascimentoError" class="error"></span>
                    </div>
                    <div class="form_input">
                      <label for="genero">Gênero:</label>
                      <select name="genero" id="genero" required>
                        <option value="">Selecione</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Feminino">Feminino</option>
                        <option value="Outro">Outro</option>
                      </select>
                    </div>
                    <div class="form_input">
                      <label for="estado_civil">Estado Civil:</label>
                      <select name="estado_civil" id="estado_civil" required>
                        <option value="">Selecione</option>
                        <option value="Solteiro">Solteiro</option>
                        <option value="Casado">Casado</option>
                        <option value="Divorciado">Divorciado</option>
                        <option value="Viúvo">Viúvo</option>
                      </select>
                    </div>
                    <div class="form_input">
                      <label for="email">Email:</label>
                      <input type="email" name="email" id="email" required>
                    </div>
                    <div class="form_input">
                      <label for="telefone">Telefone:</label>
                      <input type="text" name="telefone" id="telefone" maxlength="11" required>
                    </div>
                    <div class="form_input">
                      <label for="contato_emergencia">Contato de Emergência:</label>
                      <input type="text" name="contato_emergencia" id="contato_emergencia" required>
                    </div>
                    <div class="form_input">
                      <label for="endereco">Endereço:</label>
                      <input type="text" name="endereco" id="endereco" required>
                    </div>
                    <div class="form_input">
                      <label for="escolaridade">Escolaridade:</label>
                      <input type="text" name="escolaridade" id="escolaridade" required>
                    </div>
                    <div class="form_input">
                      <label for="ocupacao">Ocupação:</label>
                      <input type="text" name="ocupacao" id="ocupacao" required>
                    </div>
                    <div class="form_input">
                      <label for="necessidade_especial">Necessidade Especial:</label>
                      <input type="text" name="necessidade_especial" id="necessidade_especial">
                    </div>
                  </div>
                  <?php
                  break;

              case 'professores':
                  ?>
                  <div class="form_group">
                    <div class="form_input">
                      <label for="cpf">CPF:</label>
                      <input type="text" name="cpf" id="cpf" maxlength="11" required>
                      <span id="cpfError" class="error"></span>
                    </div>
                    <div class="form_input">
                      <label for="nome">Nome:</label>
                      <input type="text" name="nome" id="nome" required>
                    </div>
                    <div class="form_input">
                      <label for="data_nascimento">Data de Nascimento:</label>
                      <input type="date" name="data_nascimento" id="data_nascimento" required>
                      <span id="dataNascimentoError" class="error"></span>
                    </div>
                    <div class="form_input">
                      <label for="genero">Gênero:</label>
                      <select name="genero" id="genero" required>
                        <option value="">Selecione</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Feminino">Feminino</option>
                        <option value="Outro">Outro</option>
                      </select>
                    </div>
                    <div class="form_input">
                      <label for="data_contratacao">Data de Contratação:</label>
                      <input type="date" name="data_contratacao" id="data_contratacao" required>
                      <span id="dataContratacaoError" class="error"></span>
                    </div>
                    <div class="form_input">
                      <label for="formacao">Formação:</label>
                      <input type="text" name="formacao" id="formacao" required>
                    </div>
                    <div class="form_input">
                      <label for="especialidade">Especialidade:</label>
                      <input type="text" name="especialidade" id="especialidade">
                    </div>
                    <div class="form_input">
                      <label for="email">Email:</label>
                      <input type="email" name="email" id="email" required>
                    </div>
                    <div class="form_input">
                      <label for="telefone">Telefone:</label>
                      <input type="text" name="telefone" id="telefone" maxlength="11" required>
                    </div>
                  </div>
                  <?php
                  break;

              case 'alunos':
                  ?>
                  <div class="form_group">
                    <div class="form_input">
                      <label for="cpf">CPF:</label>
                      <input type="text" name="cpf" id="cpf" maxlength="11" required>
                      <span id="cpfError" class="error"></span>
                    </div>
                    <div class="form_input">
                      <label for="nome">Nome:</label>
                      <input type="text" name="nome" id="nome" required>
                    </div>
                    <div class="form_input">
                      <label for="data_nascimento">Data de Nascimento:</label>
                      <input type="date" name="data_nascimento" id="data_nascimento" required>
                      <span id="dataNascimentoError" class="error"></span>
                    </div>
                    <div class="form_input">
                      <label for="genero">Gênero:</label>
                      <select name="genero" id="genero" required>
                        <option value="">Selecione</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Feminino">Feminino</option>
                        <option value="Outro">Outro</option>
                      </select>
                    </div>
                    <div class="form_input">
                      <label for="email">Email:</label>
                      <input type="email" name="email" id="email" required>
                    </div>
                    <div class="form_input">
                      <label for="telefone">Telefone:</label>
                      <input type="text" name="telefone" id="telefone" maxlength="11" required>
                    </div>
                    <div class="form_input">
                      <label for="matricula">Matrícula:</label>
                      <input type="text" name="matricula" id="matricula" required>
                    </div>
                    <div class="form_input">
                      <label for="curso">Curso:</label>
                      <input type="text" name="curso" id="curso" required>
                    </div>
                    <div class="form_input">
                      <label for="ano_ingresso">Ano de Ingresso:</label>
                      <input type="text" name="ano_ingresso" id="ano_ingresso" maxlength="4" required>
                    </div>
                  </div>
                  <?php
                  break;
          }
          ?>
          <div class="form_group full_width">
            <button class="botao_cadastro text_button" type="submit" name="botao" value="Cadastrar">Cadastrar</button>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>
</html>