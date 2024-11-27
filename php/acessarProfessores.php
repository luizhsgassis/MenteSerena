<?php
session_start();

include('../config.php');
include('functions.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /MenteSerena-master/php/logout.php");
    exit;
}

// Obtém o ID do professor da URL
$idProfessor = isset($_GET['id']) ? $_GET['id'] : '';

$erro_acesso = '';
$sucesso_acesso = '';

// Consulta para obter as informações do professor
$queryProfessor = "SELECT * FROM Usuarios WHERE id_usuario = ? AND tipo_usuario = 'professor'";
$stmtProfessor = mysqli_prepare($conn, $queryProfessor);
mysqli_stmt_bind_param($stmtProfessor, "i", $idProfessor);
mysqli_stmt_execute($stmtProfessor);
$resultProfessor = mysqli_stmt_get_result($stmtProfessor);
$professor = mysqli_fetch_assoc($resultProfessor);
mysqli_stmt_close($stmtProfessor);

if (!$professor) {
    $erro_acesso = "Professor não encontrado.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['botao']) && $_POST['botao'] == 'Concluído') {
  $cpf = trim($_POST["cpf"]);
  $nome = trim($_POST["nome"]);
  $dataNascimento = trim($_POST["data_nascimento"]);
  $genero = trim($_POST["genero"]);
  $dataContratacao = trim($_POST["data_contratacao"]);
  $formacao = trim($_POST["formacao"]);
  $especialidade = trim($_POST["especialidade"]);
  $email = trim($_POST["email"]);
  $telefone = trim($_POST["telefone"]);

  $queryUpdate = "UPDATE Usuarios SET cpf = ?, nome = ?, data_nascimento = ?, genero = ?, data_contratacao = ?, formacao = ?, especialidade = ?, email = ?, telefone = ? WHERE id_usuario = ?";
  $stmtUpdate = mysqli_prepare($conn, $queryUpdate);
  mysqli_stmt_bind_param($stmtUpdate, "sssssssssi", $cpf, $nome, $dataNascimento, $genero, $dataContratacao, $formacao, $especialidade, $email, $telefone, $idProfessor);

  if (mysqli_stmt_execute($stmtUpdate)) {
      $sucesso_acesso = "Dados do professor atualizados com sucesso!";
      // Recarrega a página para mostrar os dados atualizados
      header("Location: acessarProfessores.php?id=" . $idProfessor);
      exit;
  } else {
      $erro_acesso = "Erro ao atualizar os dados do professor: " . mysqli_error($conn);
  }
  mysqli_stmt_close($stmtUpdate);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['botao']) && $_POST['botao'] == 'Restaurar Login e Senha') {
  // Recupera os valores do banco de dados para o professor
  $nome = $professor['nome'];
  $telefone = $professor['telefone'];
  $cpf = $professor['cpf'];
  $dataNascimento = $professor['data_nascimento'];

  // Gera o novo login
  $login = substr($nome, 0, 3) . substr($telefone, -3);
  $login = strtolower($login); // Transforma em letras minúsculas

  // Gera a nova senha
  $anoNascimento = substr($dataNascimento, -2); 
  $senha = substr($cpf, 0, 3) . $anoNascimento;

  // Atualiza o banco de dados
  $queryUpdateLoginSenha = "UPDATE Usuarios SET login = ?, senha = ? WHERE id_usuario = ?";
  $stmtUpdateLoginSenha = mysqli_prepare($conn, $queryUpdateLoginSenha);
  $senhaHash = password_hash($senha, PASSWORD_DEFAULT); // Hash da senha para segurança

  mysqli_stmt_bind_param($stmtUpdateLoginSenha, "ssi", $login, $senhaHash, $idProfessor);

  if (mysqli_stmt_execute($stmtUpdateLoginSenha)) {
      $sucesso_acesso = "Login e senha restaurados com sucesso!";
  } else {
      $erro_acesso = "Erro ao restaurar login e senha: " . mysqli_error($conn);
  }

  mysqli_stmt_close($stmtUpdateLoginSenha);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Acessar Professor</title>
  <link rel="stylesheet" href="../estilo.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/mainContent.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const alterarBtn = document.getElementById('alterarBtn');
      const concluidoBtn = document.getElementById('concluidoBtn');
      const restaurarLoginBtn = document.getElementById('restaurarLoginBtn');
      const formInputs = document.querySelectorAll('.main_form input, .main_form select');
      const form = document.querySelector('.main_form');

      alterarBtn.addEventListener('click', function() {
          formInputs.forEach(input => input.disabled = false);
          alterarBtn.disabled = true;
          concluidoBtn.disabled = false;
          restaurarLoginBtn.disabled = false;
      });

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
      <div class="main_title"><h2>Informações do Professor</h2></div>
      <div class="content">
        <?php if (!empty($erro_acesso)): ?>
          <div class="error"><?php echo $erro_acesso; ?></div>
        <?php elseif (!empty($sucesso_acesso)): ?>
          <div class="success"><?php echo $sucesso_acesso; ?></div>
        <?php endif; ?>
        <form class="main_form" action="acessarProfessores.php?id=<?php echo $idProfessor; ?>" method="post">
          <div class="form_group">
            <div class="form_input">
              <label for="cpf">CPF:</label>
              <input type="text" name="cpf" id="cpf" value="<?php echo $professor['cpf']; ?>" disabled>
              <span id="cpfError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="nome">Nome:</label>
              <input type="text" name="nome" id="nome" value="<?php echo $professor['nome']; ?>" disabled>
              <span id="nomeError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="data_nascimento">Data de Nascimento:</label>
              <input type="date" name="data_nascimento" id="data_nascimento" value="<?php echo $professor['data_nascimento']; ?>" disabled>
              <span id="dataNascimentoError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="genero">Gênero:</label>
              <select name="genero" id="genero" disabled>
                <option value="">Selecione</option>
                <option value="Masculino" <?php echo ($professor['genero'] == 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                <option value="Feminino" <?php echo ($professor['genero'] == 'Feminino') ? 'selected' : ''; ?>>Feminino</option>
                <option value="Outro" <?php echo ($professor['genero'] == 'Outro') ? 'selected' : ''; ?>>Outro</option>
              </select>
              <span id="generoError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="data_contratacao">Data de Contratação:</label>
              <input type="date" name="data_contratacao" id="data_contratacao" value="<?php echo $professor['data_contratacao']; ?>" disabled>
              <span id="dataContratacaoError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="formacao">Formação:</label>
              <input type="text" name="formacao" id="formacao" value="<?php echo $professor['formacao']; ?>" disabled>
              <span id="formacaoError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="especialidade">Especialidade:</label>
              <input type="text" name="especialidade" id="especialidade" value="<?php echo $professor['especialidade']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="email">Email:</label>
              <input type="text" name="email" id="email" value="<?php echo $professor['email']; ?>" disabled>
              <span id="emailError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="telefone">Telefone:</label>
              <input type="text" name="telefone" id="telefone" value="<?php echo $professor['telefone']; ?>" disabled>
              <span id="telefoneError" class="error"></span>
            </div>
          </div>
          <div class="form_group full_width">
            <button type="button" id="alterarBtn" class="botao_azul text_button">Alterar</button>
            <button type="submit" id="concluidoBtn" class="botao_azul text_button" name="botao" value="Concluído" disabled>Concluído</button>
            <button type="submit" id="restaurarLoginBtn" class="botao_azul text_button" name="botao" value="Restaurar Login e Senha" disabled>Restaurar Login e Senha</button>
            <a href="mainContent.php?tipo=professores" class="botao_azul text_button">Voltar</a>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>
</html>