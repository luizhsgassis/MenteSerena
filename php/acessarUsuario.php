<?php
session_start();

include('../config.php');
include('functions.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /MenteSerena-master/index.php");
    exit;
}

// Obtém o ID do usuário da URL
$idUsuario = isset($_GET['id']) ? $_GET['id'] : '';

$erro_acesso = '';
$sucesso_acesso = '';

// Consulta para obter as informações do usuário
$queryUsuario = "SELECT * FROM Usuarios WHERE id_usuario = ?";
$stmtUsuario = mysqli_prepare($conn, $queryUsuario);
mysqli_stmt_bind_param($stmtUsuario, "i", $idUsuario);
mysqli_stmt_execute($stmtUsuario);
$resultUsuario = mysqli_stmt_get_result($stmtUsuario);
$usuario = mysqli_fetch_assoc($resultUsuario);
mysqli_stmt_close($stmtUsuario);

if (!$usuario) {
    $erro_acesso = "Usuário não encontrado.";
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
    } elseif (empty($email) || !validateEmail($email)) {
        $erro_acesso = "E-mail inválido.";
    } elseif (!validateTelefone($telefone)) {
        $erro_acesso = "Telefone inválido. Deve conter exatamente 11 dígitos.";
    } elseif (empty($login)) {
        $erro_acesso = "Por favor, preencha o login.";
    } else {
        $queryUpdate = "UPDATE Usuarios SET cpf = ?, nome = ?, data_nascimento = ?, genero = ?, data_contratacao = ?, formacao = ?, especialidade = ?, email = ?, telefone = ?, login = ? WHERE id_usuario = ?";
        $stmtUpdate = mysqli_prepare($conn, $queryUpdate);
        mysqli_stmt_bind_param($stmtUpdate, "ssssssssssi", $cpf, $nome, $dataNascimento, $genero, $dataContratacao, $formacao, $especialidade, $email, $telefone, $login, $idUsuario);

        if (mysqli_stmt_execute($stmtUpdate)) {
            if (!empty($novaSenha)) {
                $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
                $querySenha = "UPDATE Usuarios SET senha = ? WHERE id_usuario = ?";
                $stmtSenha = mysqli_prepare($conn, $querySenha);
                mysqli_stmt_bind_param($stmtSenha, "si", $senhaHash, $idUsuario);
                mysqli_stmt_execute($stmtSenha);
                mysqli_stmt_close($stmtSenha);
            }
            $sucesso_acesso = "Dados do usuário atualizados com sucesso!";
            // Recarrega a página para mostrar os dados atualizados
            header("Location: acessarUsuario.php?id=" . $idUsuario);
            exit;
        } else {
            $erro_acesso = "Erro ao atualizar os dados do usuário: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmtUpdate);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Acessar Usuário</title>
  <link rel="stylesheet" href="../estilo.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/mainContent.css">
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const alterarBtn = document.getElementById('alterarBtn');
        const concluidoBtn = document.getElementById('concluidoBtn');
        const formInputs = document.querySelectorAll('.main_form input');

        alterarBtn.addEventListener('click', function() {
            formInputs.forEach(input => input.disabled = false);
            alterarBtn.disabled = true;
            concluidoBtn.disabled = false;
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
      <div class="main_title"><h2>Informações do Usuário</h2></div>
      <div class="content">
        <?php if (!empty($erro_acesso)): ?>
          <div class="error"><?php echo $erro_acesso; ?></div>
        <?php elseif (!empty($sucesso_acesso)): ?>
          <div class="success"><?php echo $sucesso_acesso; ?></div>
        <?php endif; ?>
        <form class="main_form" action="acessarUsuario.php?id=<?php echo $idUsuario; ?>" method="post">
          <div class="form_group">
            <div class="form_input">
              <label for="cpf">CPF:</label>
              <input type="text" name="cpf" id="cpf" value="<?php echo $usuario['cpf']; ?>" disabled>
              <span id="cpfError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="nome">Nome:</label>
              <input type="text" name="nome" id="nome" value="<?php echo $usuario['nome']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="data_nascimento">Data de Nascimento:</label>
              <input type="date" name="data_nascimento" id="data_nascimento" value="<?php echo $usuario['data_nascimento']; ?>" disabled>
              <span id="dataNascimentoError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="genero">Gênero:</label>
              <input type="text" name="genero" id="genero" value="<?php echo $usuario['genero']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="data_contratacao">Data de Contratação:</label>
              <input type="date" name="data_contratacao" id="data_contratacao" value="<?php echo $usuario['data_contratacao']; ?>" disabled>
              <span id="dataContratacaoError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="formacao">Formação:</label>
              <input type="text" name="formacao" id="formacao" value="<?php echo $usuario['formacao']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="especialidade">Especialidade:</label>
              <input type="text" name="especialidade" id="especialidade" value="<?php echo $usuario['especialidade']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="email">Email:</label>
              <input type="email" name="email" id="email" value="<?php echo $usuario['email']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="telefone">Telefone:</label>
              <input type="text" name="telefone" id="telefone" value="<?php echo $usuario['telefone']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="login">Login:</label>
              <input type="text" name="login" id="login" value="<?php echo $usuario['login']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="nova_senha">Nova Senha:</label>
              <input type="password" name="nova_senha" id="nova_senha" disabled>
            </div>
          </div>
          <div class="form_group full_width">
            <button type="button" id="alterarBtn" class="botao_azul text_button">Alterar</button>
            <button type="submit" id="concluidoBtn" class="botao_azul text_button" name="botao" value="Concluído" disabled>Concluído</button>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>
</html>