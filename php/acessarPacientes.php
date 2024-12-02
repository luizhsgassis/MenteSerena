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
$nivelAcesso = $_SESSION['UsuarioNivel'];
$idUsuarioLogado = $_SESSION['id_usuario'];

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

// Consulta para obter o aluno responsável por um paciente
$queryAlunoResp = "SELECT u.id_usuario, u.nome
                  FROM Usuarios u
                  JOIN AssociacaoPacientesAlunos apa
                  ON u.id_usuario = apa.id_aluno
                  WHERE apa.id_paciente = $idPaciente";
$stmtAlunoResp = mysqli_prepare($conn, $queryAlunoResp);
mysqli_stmt_execute($stmtAlunoResp);
$resultAlunoResp = mysqli_stmt_get_result($stmtAlunoResp);
$alunoResp = mysqli_fetch_assoc($resultAlunoResp);
$alunoRespNome = $alunoResp ['nome'];
$alunoRespID = $alunoResp ['id_usuario'];
mysqli_stmt_close($stmtAlunoResp);

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

// Consulta para obter as sessões do prontuário
$idUsuarioLogado = $_SESSION['id_usuario'];
$querySessoes = "SELECT s.id_sessao, s.data, s.registro_sessao, s.anotacoes, u.nome AS nome_usuario 
         FROM Sessoes s 
         LEFT JOIN Usuarios u ON s.id_usuario = u.id_usuario 
         WHERE s.id_paciente = ? AND s.id_usuario = ?";
$stmtSessoes = mysqli_prepare($conn, $querySessoes);
mysqli_stmt_bind_param($stmtSessoes, "ii", $paciente['id_paciente'], $idUsuarioLogado);
mysqli_stmt_execute($stmtSessoes);
$resultSessoes = mysqli_stmt_get_result($stmtSessoes);
// Initialize button variables
$textoBotao = "Default Text"; // Default text
$paginaBotao = "default_page.php";
$linkBotao = "empty";

// Consulta para obter as informações do paciente
$queryExists = "SELECT id_prontuario FROM Prontuarios WHERE id_paciente = ?";
$stmtExists = mysqli_prepare($conn, $queryExists);
mysqli_stmt_bind_param($stmtExists, "i", $idPaciente);
mysqli_stmt_execute($stmtExists);
$resultExists = mysqli_stmt_get_result($stmtExists);
$exists = mysqli_fetch_assoc($resultExists);
mysqli_stmt_close($stmtExists);

if (!$exists) {
  $textoBotao = "Cadastrar Prontuário";
  $paginaBotao = "cadastrarProntuario.php?paciente_id=";
  $linkBotao = $paginaBotao . $idPaciente;
} else {
  $textoBotao = "Acessar Prontuário";
  $paginaBotao = "acessarProntuario.php?paciente_id=";
  $linkBotao = $paginaBotao . $idPaciente;
}

$queryAlunoProf = "SELECT Usuarios.id_usuario, Usuarios.nome 
            FROM AssociacaoAlunosProfessores
            JOIN Usuarios ON AssociacaoAlunosProfessores.id_aluno = Usuarios.id_usuario
            JOIN Professores ON AssociacaoAlunosProfessores.id_professor = Professores.id_professor
            WHERE AssociacaoAlunosProfessores.id_professor = (
            SELECT id_professor 
            FROM Professores 
            WHERE id_usuario = $idUsuarioLogado) AND Usuarios.ativo = 1";

$resultAlunoProf = mysqli_query($conn, $queryAlunoProf);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['botao']) && $_POST['botao'] == 'Confirmar Troca de Aluno') {
  $novoAluno = trim($_POST["alterar_aluno"]);

  // Atualiza o banco de dados
  $queryUpdateAluno = "UPDATE AssociacaoPacientesAlunos SET id_aluno = $novoAluno WHERE id_paciente = $idPaciente";
  $stmtUpdateAluno = mysqli_prepare($conn, $queryUpdateAluno);

  if (mysqli_stmt_execute($stmtUpdateAluno)) {
      $sucesso_acesso = "Aluno trocado com sucesso!";
  } else {
      $erro_acesso = "Erro ao trocar aluno: " . mysqli_error($conn);
  }

  mysqli_stmt_close($stmtUpdateAluno);
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
  <style>
    main {
        max-height: 80vh;
        overflow-y: auto;
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const alterarBtn = document.getElementById('alterarBtn');
        const concluidoBtn = document.getElementById('concluidoBtn');
        const formInputs = document.querySelectorAll('.main_form input, .main_form select');
        const form = document.querySelector('.main_form');

        alterarBtn.addEventListener('click', function() {
            formInputs.forEach(input => input.disabled = false);
            alterarBtn.disabled = true;
            concluidoBtn.disabled = false;
            trocarAlunoBtn.disabled = false;
            var alunoRespField = document.getElementById('aluno_resp');
            alunoRespField.disabled = true;
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
      <div class="main_title"><h2>Informações do Paciente</h2></div>
      <div class="conteudo">
        <?php if (!empty($erro_acesso)): ?>
          <div class="error"><?php echo $erro_acesso; ?></div>
        <?php elseif (!empty($sucesso_acesso)): ?>
          <div class="success"><?php echo $sucesso_acesso; ?></div>
        <?php endif; ?>
        <div class="leftBlock">
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
                <span id="nomeError" class="error"></span>
                </div>
              <div class="form_input">
                <label for="data_nascimento">Data de Nascimento:</label>
                <input type="date" name="data_nascimento" id="data_nascimento" value="<?php echo $paciente['data_nascimento']; ?>" disabled>
                <span id="dataNascimentoError" class="error"></span>
              </div>
              <div class="form_input">
                <label for="genero">Gênero:</label>
                <select name="genero" id="genero" disabled>
                  <option value="">Selecione</option>
                  <option value="Masculino" <?php echo ($paciente['genero'] == 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                  <option value="Feminino" <?php echo ($paciente['genero'] == 'Feminino') ? 'selected' : ''; ?>>Feminino</option>
                  <option value="Outro" <?php echo ($paciente['genero'] == 'Outro') ? 'selected' : ''; ?>>Outro</option>
                </select>
                <span id="generoError" class="error"></span>
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
                <span id="estado_civilError" class="error"></span>
                </div>
              <div class="form_input">
                <label for="email">Email:</label>
                <input type="text" name="email" id="email" value="<?php echo $paciente['email']; ?>" disabled>
                <span id="emailError" class="error"></span>
                </div>
              <div class="form_input">
                <label for="telefone">Telefone:</label>
                <input type="text" name="telefone" id="telefone" value="<?php echo $paciente['telefone']; ?>" disabled>
                <span id="telefoneError" class="error"></span>
                </div>
              <div class="form_input">
                <label for="contato_emergencia">Contato de Emergência:</label>
                <input type="text" name="contato_emergencia" id="contato_emergencia" value="<?php echo $paciente['contato_emergencia']; ?>" disabled>
                <span id="contatoError" class="error"></span>
                </div>
              <div class="form_input">
                <label for="endereco">Endereço:</label>
                <input type="text" name="endereco" id="endereco" value="<?php echo $paciente['endereco']; ?>" disabled>
                <span id="enderecoError" class="error"></span>
                </div>
              <div class="form_input">
                <label for="escolaridade">Escolaridade:</label>
                <input type="text" name="escolaridade" id="escolaridade" value="<?php echo $paciente['escolaridade']; ?>" disabled>
                <span id="escolaridadeError" class="error"></span>
                </div>
              <div class="form_input">
                <label for="ocupacao">Ocupação:</label>
                <input type="text" name="ocupacao" id="ocupacao" value="<?php echo $paciente['ocupacao']; ?>" disabled>
                <span id="ocupacaoError" class="error"></span>
                </div>
              <div class="form_input">
                <label for="necessidade_especial">Necessidade Especial:</label>
                <input type="text" name="necessidade_especial" id="necessidade_especial" value="<?php echo $paciente['necessidade_especial']; ?>" disabled>
                <span id="necessidadeError" class="error"></span>
                </div>
              <div class="form_input">
                <label for="aluno_resp">Aluno Responsável:</label>
                <input type="text" name="aluno_resp" id="aluno_resp" value="<?php echo $alunoResp['nome']; ?>" disabled>
                </div>
              <?php if($nivelAcesso == 'professor'): ?>
              <div class="form_input">
                <label for="alterar_aluno">Alterar Aluno:</label>
                <select name="alterar_aluno" id="alterar_aluno" disabled>
                  <option value="">Selecione um Aluno:</option>
                    <?php
                    while ($row = mysqli_fetch_assoc($resultAlunoProf)) {
                        echo '<option value="' . $row['id_usuario'] . '">' . $row['nome'] . '</option>';
                    }
                    ?>
                </select>
                </div>
              <div class="form_group full_width">
                <button type="submit" id="trocarAlunoBtn" class="botao_azul text_button" name="botao" value="Confirmar Troca de Aluno" disabled>Confirmar Troca de Aluno</button>
              </div>
                <?php endif; ?>
            </div>
            <div class="form_group full_width">
              <button type="button" id="alterarBtn" class="botao_azul text_button">Alterar</button>
              <button type="submit" id="concluidoBtn" class="botao_azul text_button" name="botao" value="Concluído" disabled>Concluído</button>
              <a href="mainContent.php?tipo=pacientes" class="botao_azul text_button">Voltar</a>
              <br>
              <br>
              <br>
              <a href="<?php echo $linkBotao ?>" class="botao_azul text_button"><?php echo $textoBotao; ?></a>
              <br>
              <br>
              <br>
            </div>
          </form>
        </div>
        <div class="rightBlock">
          <h3>Sessões</h3>
          <div class="table-container">            
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Data</th>
                  <th>Registro da Sessão</th>
                  <th>Anotações</th>
                  <th>Usuário</th>
                </tr>
              </thead>
              <tbody>
                <?php if (mysqli_num_rows($resultSessoes) > 0): ?>
                  <?php while ($sessao = mysqli_fetch_assoc($resultSessoes)): ?>
                    <tr onclick="window.location.href='acessarSessoes.php?id=<?php echo $sessao['id_sessao']; ?>'">
                      <td><?php echo $sessao['id_sessao']; ?></td>
                      <td><?php echo date('d/m/Y', strtotime($sessao['data'])); ?></td>
                      <td><?php echo $sessao['registro_sessao']; ?></td>
                      <td><?php echo $sessao['anotacoes']; ?></td>
                      <td><?php echo $sessao['nome_usuario']; ?></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5">Nenhuma sessão encontrada</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>