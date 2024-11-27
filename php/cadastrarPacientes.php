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
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastrar Paciente</title>
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

        // Validação Nome
        const nomeInput = document.getElementById('nome');
        const nomeError = document.getElementById('nomeError');
        const nome = nomeInput.value;
        if (nome.length == 0) {
          nomeError.textContent = 'Digite o nome do paciente.';
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

        // Validação do estado civil
        const estadoCivilInput = document.getElementById('estado_civil');
        const estadoCivilError = document.getElementById('estado_civilError');
        const estadoCivil = estadoCivilInput.value;
        if (estadoCivil !== 'Solteiro(a)' && estadoCivil !== 'Casado(a)' && estadoCivil !== 'Divorciado(a)' && estadoCivil !== 'Viúvo(a)') {
          estadoCivilError.textContent = 'Por favor, selecione um estado civil válido.';
          estadoCivilInput.focus();
          hasError = true;
        } else {
          estadoCivilError.textContent = '';
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

        // Validação do contato de emergência
        const contatoInput = document.getElementById('contato_emergencia');
        const contatoError = document.getElementById('contatoError');
        const contato = contatoInput.value;
        if (contato.length == 0) {
          contatoError.textContent = 'Digite um contato de emergência.';
          contatoInput.focus();
          hasError = true;
        } else {
          contatoError.textContent = '';
        }

        // Validação do endereço
        const enderecoInput = document.getElementById('endereco');
        const enderecoError = document.getElementById('enderecoError');
        const endereco = enderecoInput.value;
        if (endereco.length == 0) {
          enderecoError.textContent = 'Digite o endereço do paciente.';
          enderecoInput.focus();
          hasError = true;
        } else {
          enderecoError.textContent = '';
        }

        // Validação da escolaridade
        const escolaridadeInput = document.getElementById('escolaridade');
        const escolaridadeError = document.getElementById('escolaridadeError');
        const escolaridade = escolaridadeInput.value;
        if (escolaridade.length == 0) {
          escolaridadeError.textContent = 'Digite a escolaridade do paciente.';
          escolaridadeInput.focus();
          hasError = true;
        } else {
          escolaridadeError.textContent = '';
        }

        // Validação da ocupação
        const ocupacaoInput = document.getElementById('ocupacao');
        const ocupacaoError = document.getElementById('ocupacaoError');
        const ocupacao = ocupacaoInput.value;
        if (ocupacao.length == 0) {
          ocupacaoError.textContent = 'Digite a ocupação do paciente.';
          ocupacaoInput.focus();
          hasError = true;
        } else {
          ocupacaoError.textContent = '';
        }

        // Validação da necessidade especial
        const necessidadeInput = document.getElementById('necessidade_especial');
        const necessidadeError = document.getElementById('necessidadeError');
        const necessidade = necessidadeInput.value;
        if (necessidade.length == 0) {
          necessidadeError.textContent = 'Digite a necessidade especial do paciente. Se não houver, digite "Nenhuma".';
          necessidadeInput.focus();
          hasError = true;
        } else {
          necessidadeError.textContent = '';
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
              <label for="estado_civil">Estado Civil:</label>
              <select name="estado_civil" id="estado_civil" required>
                <option value="">Selecione</option>
                <option value="Solteiro(a)">Solteiro(a)</option>
                <option value="Casado(a)">Casado(a)</option>
                <option value="Divorciado(a)">Divorciado(a)</option>
                <option value="Viúvo(a)">Viúvo(a)</option>
              </select>
              <span id="estado_civilError" class="error"></span>
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
            <div class="form_input">
              <label for="contato_emergencia">Contato de Emergência:</label>
              <input type="text" name="contato_emergencia" id="contato_emergencia" required>
              <span id="contatoError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="endereco">Endereço:</label>
              <input type="text" name="endereco" id="endereco" required>
              <span id="enderecoError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="escolaridade">Escolaridade:</label>
              <input type="text" name="escolaridade" id="escolaridade" required>
              <span id="escolaridadeError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="ocupacao">Ocupação:</label>
              <input type="text" name="ocupacao" id="ocupacao" required>
              <span id="ocupacaoError" class="error"></span>
            </div>
            <div class="form_input">
              <label for="necessidade_especial">Necessidade Especial:</label>
              <input type="text" name="necessidade_especial" id="necessidade_especial">
              <span id="necessidadeError" class="error"></span>
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