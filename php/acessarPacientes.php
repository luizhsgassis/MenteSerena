<?php
session_start();

include('../config.php');
include('functions.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /MenteSerena-master/index.php");
    exit;
}

// Obtém o ID do paciente da URL
$idPaciente = isset($_GET['id']) ? $_GET['id'] : '';

$erro_acesso = '';
$sucesso_acesso = '';

// Consulta para obter as informações do paciente
$queryPaciente = "SELECT * FROM Pacientes WHERE id_paciente = ?";
$stmtPaciente = mysqli_prepare($conn, $queryPaciente);
mysqli_stmt_bind_param($stmtPaciente, "i", $idPaciente);
mysqli_stmt_execute($stmtPaciente);
$resultPaciente = mysqli_stmt_get_result($stmtPaciente);
$paciente = mysqli_fetch_assoc($resultPaciente);
mysqli_stmt_close($stmtPaciente);

if (!$paciente) {
    $erro_acesso = "Paciente não encontrado.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['botao']) && $_POST['botao'] == 'Concluído') {
    $cpf = trim($_POST["cpf"]);
    $nome = trim($_POST["nome"]);
    $dataNascimento = trim($_POST["data_nascimento"]);
    $genero = trim($_POST["genero"]);
    $estadoCivil = trim($_POST["estado_civil"]);
    $email = trim($_POST["email"]);
    $telefone = trim($_POST["telefone"]);
    $contatoEmergencia = trim($_POST["contato_emergencia"]);
    $endereco = trim($_POST["endereco"]);
    $escolaridade = trim($_POST["escolaridade"]);
    $ocupacao = trim($_POST["ocupacao"]);
    $necessidadeEspecial = trim($_POST["necessidade_especial"]);

    // Validações
    if (!validateCPF($cpf)) {
        $erro_acesso = "CPF inválido. Deve conter exatamente 11 dígitos.";
    } elseif (empty($nome)) {
        $erro_acesso = "Por favor, preencha o nome.";
    } elseif (empty($dataNascimento) || !validateDate($dataNascimento)) {
        $erro_acesso = "Por favor, preencha uma data de nascimento válida.";
    } elseif (empty($genero)) {
        $erro_acesso = "Por favor, selecione o gênero.";
    } elseif (empty($estadoCivil)) {
        $erro_acesso = "Por favor, selecione o estado civil.";
    } elseif (empty($email) || !validateEmail($email)) {
        $erro_acesso = "E-mail inválido.";
    } elseif (!validateTelefone($telefone)) {
        $erro_acesso = "Telefone inválido. Deve conter exatamente 11 dígitos.";
    } elseif (empty($contatoEmergencia)) {
        $erro_acesso = "Por favor, preencha o contato de emergência.";
    } elseif (empty($endereco)) {
        $erro_acesso = "Por favor, preencha o endereço.";
    } elseif (empty($escolaridade)) {
        $erro_acesso = "Por favor, preencha a escolaridade.";
    } elseif (empty($ocupacao)) {
        $erro_acesso = "Por favor, preencha a ocupação.";
    } else {
        $queryUpdate = "UPDATE Pacientes SET cpf = ?, nome = ?, data_nascimento = ?, genero = ?, estado_civil = ?, email = ?, telefone = ?, contato_emergencia = ?, endereco = ?, escolaridade = ?, ocupacao = ?, necessidade_especial = ? WHERE id_paciente = ?";
        $stmtUpdate = mysqli_prepare($conn, $queryUpdate);
        mysqli_stmt_bind_param($stmtUpdate, "ssssssssssssi", $cpf, $nome, $dataNascimento, $genero, $estadoCivil, $email, $telefone, $contatoEmergencia, $endereco, $escolaridade, $ocupacao, $necessidadeEspecial, $idPaciente);

        if (mysqli_stmt_execute($stmtUpdate)) {
            $sucesso_acesso = "Dados do paciente atualizados com sucesso!";
            // Recarrega a página para mostrar os dados atualizados
            header("Location: acessarPacientes.php?id=" . $idPaciente);
            exit;
        } else {
            $erro_acesso = "Erro ao atualizar os dados do paciente: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmtUpdate);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Acessar Paciente</title>
  <link rel="stylesheet" href="../estilo.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/mainContent.css">
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const alterarBtn = document.getElementById('alterarBtn');
        const concluidoBtn = document.getElementById('concluidoBtn');
        const formInputs = document.querySelectorAll('.main_form input, .main_form select');

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
      <div class="main_title"><h2>Informações do Paciente</h2></div>
      <div class="content">
        <?php if (!empty($erro_acesso)): ?>
          <div class="error"><?php echo $erro_acesso; ?></div>
        <?php elseif (!empty($sucesso_acesso)): ?>
          <div class="success"><?php echo $sucesso_acesso; ?></div>
        <?php endif; ?>
        <form class="main_form" action="acessarPacientes.php?id=<?php echo $idPaciente; ?>" method="post">
          <div class="form_group">
            <div class="form_input">
              <label for="cpf">CPF:</label>
              <input type="text" name="cpf" id="cpf" value="<?php echo $paciente['cpf']; ?>" disabled>
              <span id="cpfError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="nome">Nome:</label>
              <input type="text" name="nome" id="nome" value="<?php echo $paciente['nome']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="data_nascimento">Data de Nascimento:</label>
              <input type="date" name="data_nascimento" id="data_nascimento" value="<?php echo $paciente['data_nascimento']; ?>" disabled>
              <span id="dataNascimentoError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="genero">Gênero:</label>
              <input type="text" name="genero" id="genero" value="<?php echo $paciente['genero']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="estado_civil">Estado Civil:</label>
              <select name="estado_civil" id="estado_civil" disabled>
                <option value="">Selecione</option>
                <option value="Solteiro(a)" <?php echo ($paciente['estado_civil'] == 'Solteiro(a)') ? 'selected' : ''; ?>>Solteiro(a)</option>
                <option value="Casado(a)" <?php echo ($paciente['estado_civil'] == 'Casado(a)') ? 'selected' : ''; ?>>Casado(a)</option>
                <option value="Divorciado(a)" <?php echo ($paciente['estado_civil'] == 'Divorciado(a)') ? 'selected' : ''; ?>>Divorciado(a)</option>
                <option value="Viúvo(a)" <?php echo ($paciente['estado_civil'] == 'Viúvo(a)') ? 'selected' : ''; ?>>Viúvo(a)</option>
              </select>
            </div>
            <div class="form_input">
              <label for="email">Email:</label>
              <input type="email" name="email" id="email" value="<?php echo $paciente['email']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="telefone">Telefone:</label>
              <input type="text" name="telefone" id="telefone" value="<?php echo $paciente['telefone']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="contato_emergencia">Contato de Emergência:</label>
              <input type="text" name="contato_emergencia" id="contato_emergencia" value="<?php echo $paciente['contato_emergencia']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="endereco">Endereço:</label>
              <input type="text" name="endereco" id="endereco" value="<?php echo $paciente['endereco']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="escolaridade">Escolaridade:</label>
              <input type="text" name="escolaridade" id="escolaridade" value="<?php echo $paciente['escolaridade']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="ocupacao">Ocupação:</label>
              <input type="text" name="ocupacao" id="ocupacao" value="<?php echo $paciente['ocupacao']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="necessidade_especial">Necessidade Especial:</label>
              <input type="text" name="necessidade_especial" id="necessidade_especial" value="<?php echo $paciente['necessidade_especial']; ?>" disabled>
            </div>
          </div>
          <div class="form_group full_width">
            <button type="button" id="alterarBtn" class="botao_azul text_button">Alterar</button>
            <button type="submit" id="concluidoBtn" class="botao_azul text_button" name="botao" value="Concluído" disabled>Concluído</button>
            <a href="mainContent.php?tipo=pacientes" class="botao_azul text_button">Voltar</a>
            <a href="acessarProntuario.php?paciente_id=<?php echo $idPaciente; ?>" class="botao_azul text_button">Ver Prontuário</a>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>
</html>