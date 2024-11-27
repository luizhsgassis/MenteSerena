<?php
session_start();

include('../config.php');
include('functions.php');

// Verifica se o usuário é administrador ou professor
if ($_SESSION['UsuarioNivel'] == 'aluno') {
    header("Location: /MenteSerena-master/php/logout.php");
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
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'professor', 1)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssssssss", $cpf, $nome, $dataNascimento, $genero, $dataContratacao, $formacao, $especialidade, $email, $telefone, $loginTemporario, $senhaHash);
    
    if (mysqli_stmt_execute($stmt)) {
        // Obter o ID do usuário recém-cadastrado
        $idUsuario = mysqli_insert_id($conn);

        // Inserir o professor na tabela Professores
        $queryProfessor = "INSERT INTO Professores (id_usuario) VALUES (?)";
        $stmtProfessor = mysqli_prepare($conn, $queryProfessor);
        mysqli_stmt_bind_param($stmtProfessor, "i", $idUsuario);

        if (mysqli_stmt_execute($stmtProfessor)) {
            $sucesso_cadastro = "Professor cadastrado com sucesso!";
        } else {
            $erro_cadastro = "Erro ao cadastrar professor na tabela Professores: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmtProfessor);
    } else {
        $erro_cadastro = "Erro ao cadastrar professor: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastrar Professor</title>
  <link rel="stylesheet" href="../estilo.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/mainContent.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('.main_form');

      form.addEventListener('submit', function(event) {
          let hasError = false;

          // Validação do CPF
          const cpfInput = document.getElementById('cpf');
          const cpfError = document.getElementById('cpfError');
          let cpf = cpfInput.value.replace(/_/g, ''); // Remove underscores
          if (cpf.length !== 14) {
              cpfError.textContent = 'CPF inválido. Deve estar no formato 000.000.000-00.';
              cpfInput.focus();
              hasError = true;
          } else {
              cpfError.textContent = '';
          }

          // Validação Nome
          const nomeInput = document.getElementById('nome');
          const nomeError = document.getElementById('nomeError');
          const nome = nomeInput.value;
          if (nome.length == 0) {
            nomeError.textContent = 'Digite o nome do professor.';
            nomeInput.focus();
            hasError = true;
          } else {
            nomeError.textContent = '';
          }

          // Validação da data de nascimento
          const dataNascimentoInput = document.getElementById('data_nascimento');
          const dataNascimentoError = document.getElementById('dataNascimentoError');
          const dataNascimento = dataNascimentoInput.value;
          if (!isValidDate(dataNascimento) || isFutureDate(dataNascimento)) {
              dataNascimentoError.textContent = 'Por favor, preencha uma data de nascimento válida.';
              dataNascimentoInput.focus();
              hasError = true;
          } else {
              dataNascimentoError.textContent = '';
          }

          // Validação do gênero
          const generoInput = document.getElementById('genero');
          const generoError = document.getElementById('generoError');
          const genero = generoInput.value;
          if (genero !== 'Masculino' && genero !== 'Feminino' && genero !== 'Outro') {
            generoError.textContent = 'Por favor, selecione um gênero válido.';
            generoInput.focus();
            hasError = true;
          } else {
            generoError.textContent = '';
          }

          // Validação da data de contratação
          const dataContratacaoInput = document.getElementById('data_contratacao');
          const dataContratacaoError = document.getElementById('dataContratacaoError');
          const dataContratacao = dataContratacaoInput.value;
          if (!isValidDate(dataContratacao) || isFutureDate(dataContratacao)) {
              dataContratacaoError.textContent = 'Por favor, preencha uma data de contratação válida.';
              dataContratacaoInput.focus();
              hasError = true;
          } else {
              dataContratacaoError.textContent = '';
          }

          // Validação da formação
          const formacaoInput = document.getElementById('formacao');
          const formacaoError = document.getElementById('formacaoError');
          const formacao = formacaoInput.value;
          if (formacao.length == 0) {
            formacaoError.textContent = 'Digite a formação do professor.';
            formacaoInput.focus();
            hasError = true;
          } else {
            formacaoError.textContent = '';
          }

          // Validação do email
          const emailInput = document.getElementById('email');
          const emailError = document.getElementById('emailError');
          const email = emailInput.value;
          if (email.length == 0 || !email.includes('@') || !email.includes('.')) {
              emailError.textContent = 'Email inválido. Por favor, insira um email válido.';
              emailInput.focus();
              hasError = true;
          } else {
              emailError.textContent = '';
          }

          // Validação do telefone
          const telefoneInput = document.getElementById('telefone');
          const telefoneError = document.getElementById('telefoneError');
          let telefone = telefoneInput.value.replace(/_/g, ''); // Remove underscores
          if (telefone.length !== 15) {
              telefoneError.textContent = 'Telefone inválido. Deve estar no formato (00) 00000-0000.';
              telefoneInput.focus();
              hasError = true;
          } else {
              telefoneError.textContent = '';
          }

          if (hasError) {
              event.preventDefault(); // Impede o envio do formulário
          }
      });

      // Função para validar a data
      function isValidDate(dateString) {
          const regEx = /^\d{4}-\d{2}-\d{2}$/;
          if (!dateString.match(regEx)) return false;  // Formato inválido
          const d = new Date(dateString);
          const dNum = d.getTime();
          if (!dNum && dNum !== 0) return false; // Data inválida
          return d.toISOString().slice(0, 10) === dateString;
      }

      // Função para verificar se a data é futura
      function isFutureDate(dateString) {
          const today = new Date();
          const inputDate = new Date(dateString);
          return inputDate > today;
      }

      $(document).ready(function(){
        $('#cpf').inputmask('999.999.999-99');
        $('#telefone').inputmask('(99) 99999-9999');
      });
    });
  </script>
</head>
<body>
  <div class="body_section">
    <!-- Inclui o conteúdo de sidebar.php -->
    <?php include('sidebar.php'); ?>
    <main>
      <div class="main_title"><h2>Cadastrar Professor</h2></div>
      <div class="content">
        <?php if (!empty($erro_cadastro)): ?>
          <div class="error"><?php echo $erro_cadastro; ?></div>
        <?php endif; ?>
        <?php if (!empty($sucesso_cadastro)): ?>
          <div class="success"><?php echo $sucesso_cadastro; ?></div>
        <?php endif; ?>
        <form class="main_form" action="cadastrarProfessores.php" method="post">
          <div class="form_group">
            <div class="form_input">
              <label for="cpf">CPF:</label>
              <input type="text" name="cpf" id="cpf" required>
              <span id="cpfError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="nome">Nome:</label>
              <input type="text" name="nome" id="nome" required>
              <span id="nomeError" class="error"></span>
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
              <span id="generoError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="data_contratacao">Data de Contratação:</label>
              <input type="date" name="data_contratacao" id="data_contratacao" required>
              <span id="dataContratacaoError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="formacao">Formação:</label>
              <input type="text" name="formacao" id="formacao" required>
              <span id="formacaoError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="especialidade">Especialidade:</label>
              <input type="text" name="especialidade" id="especialidade">
            </div>
            <div class="form_input">
              <label for="email">Email:</label>
              <input type="text" name="email" id="email" required>
              <span id="emailError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="telefone">Telefone:</label>
              <input type="text" name="telefone" id="telefone" required>
              <span id="telefoneError" class="error"></span>
            </div>
          </div>
          <div class="form_group full_width">
            <button class="botao_cadastro text_button" id="cadastrarBtn" type="submit" name="botao" value="Cadastrar">Cadastrar</button>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>
</html>