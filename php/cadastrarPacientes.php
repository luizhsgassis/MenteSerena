<?php
session_start();

include('../config.php');
include('functions.php');

$erro_cadastro = '';
$sucesso_cadastro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    $idUsuarioLogado = $_SESSION['id_usuario'];

    // Validações
    if (!validateCPF($cpf)) {
        $erro_cadastro = "CPF inválido. Deve conter exatamente 11 dígitos.";
    } elseif (empty($nome)) {
        $erro_cadastro = "Por favor, preencha o nome.";
    } elseif (empty($dataNascimento) || !validateDate($dataNascimento)) {
        $erro_cadastro = "Por favor, preencha uma data de nascimento válida.";
    } elseif (empty($genero)) {
        $erro_cadastro = "Por favor, selecione o gênero.";
    } elseif (empty($estadoCivil)) {
        $erro_cadastro = "Por favor, selecione o estado civil.";
    } elseif (empty($email) || !validateEmail($email)) {
        $erro_cadastro = "E-mail inválido.";
    } elseif (!validateTelefone($telefone)) {
        $erro_cadastro = "Telefone inválido. Deve conter exatamente 11 dígitos.";
    } elseif (empty($contatoEmergencia)) {
        $erro_cadastro = "Por favor, preencha o contato de emergência.";
    } elseif (empty($endereco)) {
        $erro_cadastro = "Por favor, preencha o endereço.";
    } elseif (empty($escolaridade)) {
        $erro_cadastro = "Por favor, preencha a escolaridade.";
    } elseif (empty($ocupacao)) {
        $erro_cadastro = "Por favor, preencha a ocupação.";
    } else {
        // Verificar se o CPF já está cadastrado
        $queryCPF = "SELECT id_paciente FROM Pacientes WHERE cpf = ?";
        $stmtCPF = mysqli_prepare($conn, $queryCPF);
        mysqli_stmt_bind_param($stmtCPF, "s", $cpf);
        mysqli_stmt_execute($stmtCPF);
        mysqli_stmt_bind_result($stmtCPF, $idPacienteExistente);
        mysqli_stmt_fetch($stmtCPF);
        mysqli_stmt_close($stmtCPF);

        if ($idPacienteExistente) {
            // Redireciona para a tela de associação
            header("Location: associarPaciente.php?paciente_id=" . $idPacienteExistente);
            exit;
        }

        $query = "INSERT INTO Pacientes (cpf, nome, data_nascimento, genero, estado_civil, email, telefone, contato_emergencia, endereco, escolaridade, ocupacao, necessidade_especial) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssssssssss", $cpf, $nome, $dataNascimento, $genero, $estadoCivil, $email, $telefone, $contatoEmergencia, $endereco, $escolaridade, $ocupacao, $necessidadeEspecial);
        
        if (mysqli_stmt_execute($stmt)) {
            $idPaciente = mysqli_insert_id($conn);

            // Associar o paciente ao usuário logado
            $queryAssociacao = "INSERT INTO AssociacaoPacientesAlunos (id_paciente, id_aluno) VALUES (?, ?)";
            $stmtAssociacao = mysqli_prepare($conn, $queryAssociacao);
            mysqli_stmt_bind_param($stmtAssociacao, "ii", $idPaciente, $idUsuarioLogado);
            mysqli_stmt_execute($stmtAssociacao);
            mysqli_stmt_close($stmtAssociacao);

            header("Location: cadastrarProntuario.php?paciente_id=" . $idPaciente);
            exit;
        } else {
            $erro_cadastro = "Erro ao cadastrar paciente: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastrar Paciente</title>
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
                // Verificação do CPF via AJAX
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'verificarCPF.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        if (xhr.responseText === 'existente') {
                            window.location.href = 'associarPaciente.php?cpf=' + cpf;
                        } else {
                            cpfError.textContent = '';
                        }
                    }
                };
                xhr.send('cpf=' + cpf);
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
      <div class="main_title"><h2>Cadastrar Paciente</h2></div>
      <div class="content">
        <?php if (!empty($erro_cadastro)): ?>
          <div class="error"><?php echo $erro_cadastro; ?></div>
        <?php endif; ?>
        <?php if (!empty($sucesso_cadastro)): ?>
          <div class="success"><?php echo $sucesso_cadastro; ?></div>
        <?php endif; ?>
        <form class="main_form" action="cadastrarPacientes.php" method="post">
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
          <div class="form_group full_width">
            <button class="botao_cadastro text_button" type="submit" name="botao" value="Cadastrar">Cadastrar</button>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>
</html>