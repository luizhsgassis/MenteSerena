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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cpf = trim($_POST["cpf"]);
    $nome = trim($_POST["nome"]);
    $dataNascimento = trim($_POST["data_nascimento"]);
    $genero = trim($_POST["genero"]);
    $dataContratacao = trim($_POST["data_contratacao"]);
    $formacao = trim($_POST["formacao"]);
    $especialidade = trim($_POST["especialidade"]);
    $email = trim($_POST["email"]);
    $telefone = trim($_POST["telefone"]);

    // Validações
    if (!validateCPF($cpf)) {
        $erro_cadastro = "CPF inválido. Deve conter exatamente 11 dígitos.";
    } elseif (empty($nome)) {
        $erro_cadastro = "Por favor, preencha o nome.";
    } elseif (empty($dataNascimento) || !validateDate($dataNascimento)) {
        $erro_cadastro = "Por favor, preencha uma data de nascimento válida.";
    } elseif (empty($genero)) {
        $erro_cadastro = "Por favor, selecione o gênero.";
    } elseif (empty($dataContratacao) || !validateDate($dataContratacao)) {
        $erro_cadastro = "Por favor, preencha uma data de contratação válida.";
    } elseif (empty($formacao)) {
        $erro_cadastro = "Por favor, preencha a formação.";
    } elseif (empty($email) || !validateEmail($email)) {
        $erro_cadastro = "E-mail inválido.";
    } elseif (!validateTelefone($telefone)) {
        $erro_cadastro = "Telefone inválido. Deve conter exatamente 11 dígitos.";
    } else {
        $loginPlaceholder1 = substr($nome, 0, 3);
        $loginPlaceholder2 = substr($telefone, -3);
        $loginTemporario = $loginPlaceholder1 . $loginPlaceholder2;

        // Verifica se o login já existe no banco de dados
        $queryCheckLogin = "SELECT COUNT(*) AS count FROM Usuarios WHERE login = ?";
        $stmtCheckLogin = mysqli_prepare($conn, $queryCheckLogin);
        mysqli_stmt_bind_param($stmtCheckLogin, "s", $loginTemporario);
        mysqli_stmt_execute($stmtCheckLogin);
        $resultCheckLogin = mysqli_stmt_get_result($stmtCheckLogin);
        $rowCheckLogin = mysqli_fetch_assoc($resultCheckLogin);

        if ($rowCheckLogin['count'] > 0) {
            // Se já existe, ajusta o login
            $loginPlaceholder3 = substr($telefone, 0, 3); 
            $loginTemporario = $loginTemporario. $loginPlaceholder3;
        }

        $senhaPlaceholder1 = substr($cpf, 0, 3);
        $senhaPlaceholder2 = substr($dataNascimento, -2);
        $senhaTemporaria = $senhaPlaceholder1 . $senhaPlaceholder2;
        $senhaHash = password_hash($senhaTemporaria, PASSWORD_DEFAULT);

        // Trata o campo especialidade como NULL se estiver vazio
        $especialidade = !empty($especialidade) ? $especialidade : NULL;

        $query = "INSERT INTO Usuarios (cpf, nome, data_nascimento, genero, data_contratacao, formacao, especialidade, email, telefone, login, senha, tipo_usuario, ativo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aluno', 1)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssssssss", $cpf, $nome, $dataNascimento, $genero, $dataContratacao, $formacao, $especialidade, $email, $telefone, $loginTemporario, $senhaHash);
        
        if (mysqli_stmt_execute($stmt)) {
            $sucesso_cadastro = "Aluno cadastrado com sucesso!";
        } else {
            $erro_cadastro = "Erro ao cadastrar aluno: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastrar Aluno</title>
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
        document.getElementById('data_contratacao').addEventListener('blur', function() {
            var dataContratacao = this.value;
            var dataContratacaoError = document.getElementById('dataContratacaoError');
            if (!isValidDate(dataContratacao)) {
                dataContratacaoError.textContent = 'Por favor, preencha uma data de contratação válida.';
            } else {
                dataContratacaoError.textContent = '';
            }
        });

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
      <div class="main_title"><h2>Cadastrar Aluno</h2></div>
      <div class="content">
        <?php if (!empty($erro_cadastro)): ?>
          <div class="error"><?php echo $erro_cadastro; ?></div>
        <?php endif; ?>
        <?php if (!empty($sucesso_cadastro)): ?>
          <div class="success"><?php echo $sucesso_cadastro; ?></div>
        <?php endif; ?>
        <form class="main_form" action="cadastrarAlunos.php" method="post">
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
          <div class="form_group full_width">
            <button class="botao_cadastro text_button" type="submit" name="botao" value="Cadastrar">Cadastrar</button>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>
</html>