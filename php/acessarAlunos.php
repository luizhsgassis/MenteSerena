<?php
session_start();

include('../config.php');
include('functions.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /MenteSerena-master/index.php");
    exit;
}

// Obtém o ID do aluno da URL
$idAluno = isset($_GET['id']) ? $_GET['id'] : '';

$erro_acesso = '';
$sucesso_acesso = '';

// Consulta para obter as informações do aluno
$queryAluno = "SELECT * FROM Usuarios WHERE id_usuario = ? AND tipo_usuario = 'aluno'";
$stmtAluno = mysqli_prepare($conn, $queryAluno);
mysqli_stmt_bind_param($stmtAluno, "i", $idAluno);
mysqli_stmt_execute($stmtAluno);
$resultAluno = mysqli_stmt_get_result($stmtAluno);
$aluno = mysqli_fetch_assoc($resultAluno);
mysqli_stmt_close($stmtAluno);

if (!$aluno) {
    $erro_acesso = "Aluno não encontrado.";
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
    $login = trim($_POST["login"]);
    $novaSenha = trim($_POST["nova_senha"]);

    // Validações
    if (!validateCPF($cpf)) {
        $erro_acesso = "CPF inválido. Deve conter exatamente 11 dígitos.";
    } elseif (empty($nome)) {
        $erro_acesso = "Por favor, preencha o nome.";
    } elseif (empty($dataNascimento) || !validateDate($dataNascimento)) {
        $erro_acesso = "Por favor, preencha uma data de nascimento válida.";
    } elseif (empty($genero)) {
        $erro_acesso = "Por favor, selecione o gênero.";
    } elseif (empty($dataContratacao) || !validateDate($dataContratacao)) {
        $erro_acesso = "Por favor, preencha uma data de contratação válida.";
    } elseif (empty($formacao)) {
        $erro_acesso = "Por favor, preencha a formação.";
    } elseif (!validateTelefone($telefone)) {
        $erro_acesso = "Telefone inválido. Deve conter exatamente 11 dígitos.";
    } else {
        $queryUpdate = "UPDATE Usuarios SET cpf = ?, nome = ?, data_nascimento = ?, genero = ?, data_contratacao = ?, formacao = ?, especialidade = ?, email = ?, telefone = ? WHERE id_usuario = ?";
        $stmtUpdate = mysqli_prepare($conn, $queryUpdate);
        mysqli_stmt_bind_param($stmtUpdate, "sssssssssi", $cpf, $nome, $dataNascimento, $genero, $dataContratacao, $formacao, $especialidade, $email, $telefone, $idAluno);

        if (mysqli_stmt_execute($stmtUpdate)) {
            $sucesso_acesso = "Dados do aluno atualizados com sucesso!";
            // Recarrega a página para mostrar os dados atualizados
            header("Location: acessarAlunos.php?id=" . $idAluno);
            exit;
        } else {
            $erro_acesso = "Erro ao atualizar os dados do aluno: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmtUpdate);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['botao']) && $_POST['botao'] == 'Restaurar Login e Senha') {
  // Recupera os valores do banco de dados para o professor
  $nome = $aluno['nome'];
  $telefone = $aluno['telefone'];
  $cpf = $aluno['cpf'];
  $dataNascimento = $aluno['data_nascimento'];

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

  mysqli_stmt_bind_param($stmtUpdateLoginSenha, "ssi", $login, $senhaHash, $idAluno);

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
  <title>Acessar Aluno</title>
  <link rel="stylesheet" href="../estilo.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/mainContent.css">
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const alterarBtn = document.getElementById('alterarBtn');
        const concluidoBtn = document.getElementById('concluidoBtn');
        const restaurarLoginBtn = document.getElementById('restaurarLoginBtn');
        const formInputs = document.querySelectorAll('.main_form input');

        alterarBtn.addEventListener('click', function() {
            formInputs.forEach(input => input.disabled = false);
            alterarBtn.disabled = true;
            concluidoBtn.disabled = false;
            restaurarLoginBtn.disabled = false;
        });

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
      <div class="main_title"><h2>Informações do Aluno</h2></div>
      <div class="content">
        <?php if (!empty($erro_acesso)): ?>
          <div class="error"><?php echo $erro_acesso; ?></div>
        <?php elseif (!empty($sucesso_acesso)): ?>
          <div class="success"><?php echo $sucesso_acesso; ?></div>
        <?php endif; ?>
        <form class="main_form" action="acessarAlunos.php?id=<?php echo $idAluno; ?>" method="post">
          <div class="form_group">
            <div class="form_input">
              <label for="cpf">CPF:</label>
              <input type="text" name="cpf" id="cpf" value="<?php echo $aluno['cpf']; ?>" disabled>
              <span id="cpfError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="nome">Nome:</label>
              <input type="text" name="nome" id="nome" value="<?php echo $aluno['nome']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="data_nascimento">Data de Nascimento:</label>
              <input type="date" name="data_nascimento" id="data_nascimento" value="<?php echo $aluno['data_nascimento']; ?>" disabled>
              <span id="dataNascimentoError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="genero">Gênero:</label>
              <input type="text" name="genero" id="genero" value="<?php echo $aluno['genero']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="data_contratacao">Data de Contratação:</label>
              <input type="date" name="data_contratacao" id="data_contratacao" value="<?php echo $aluno['data_contratacao']; ?>" disabled>
              <span id="dataContratacaoError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="formacao">Formação:</label>
              <input type="text" name="formacao" id="formacao" value="<?php echo $aluno['formacao']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="especialidade">Especialidade:</label>
              <input type="text" name="especialidade" id="especialidade" value="<?php echo $aluno['especialidade']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="email">Email:</label>
              <input type="text" name="email" id="email" value="<?php echo $aluno['email']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="telefone">Telefone:</label>
              <input type="text" name="telefone" id="telefone" value="<?php echo $aluno['telefone']; ?>" disabled>
            </div>
          </div>
          <div class="form_group full_width">
            <button type="button" id="alterarBtn" class="botao_azul text_button">Alterar</button>
            <button type="submit" id="concluidoBtn" class="botao_azul text_button" name="botao" value="Concluído" disabled>Concluído</button>
            <button type="submit" id="restaurarLoginBtn" class="botao_azul text_button" name="botao" value="Restaurar Login e Senha" disabled>Restaurar Login e Senha</button>
            <a href="mainContent.php?tipo=alunos" class="botao_azul text_button">Voltar</a>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>
</html>