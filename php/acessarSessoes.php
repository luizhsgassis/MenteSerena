<?php
session_start();

include('../config.php');
include('functions.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /MenteSerena-master/index.php");
    exit;
}

// Obtém o ID da sessão da URL
$idSessao = isset($_GET['id']) ? $_GET['id'] : '';

$erro_acesso = '';
$sucesso_acesso = '';

// Consulta para obter as informações adicionais da sessão
$querySessao = "SELECT s.*, p.nome AS nome_paciente, u.nome AS nome_aluno, pr.nome AS nome_professor
                FROM Sessoes s
                LEFT JOIN Pacientes p ON s.id_paciente = p.id_paciente
                LEFT JOIN Usuarios u ON s.id_usuario = u.id_usuario
                LEFT JOIN Usuarios pr ON s.id_usuario = pr.id_usuario
                WHERE s.id_sessao = ?";
$stmtSessao = mysqli_prepare($conn, $querySessao);
mysqli_stmt_bind_param($stmtSessao, "i", $idSessao);
mysqli_stmt_execute($stmtSessao);
$resultSessao = mysqli_stmt_get_result($stmtSessao);
$sessao = mysqli_fetch_assoc($resultSessao);
mysqli_stmt_close($stmtSessao);

// Consulta para obter a lista de pacientes
$queryPacientes = "SELECT id_paciente, nome FROM Pacientes";
$resultPacientes = mysqli_query($conn, $queryPacientes);

// Consulta para obter a lista de alunos
$queryAlunos = "SELECT id_usuario, nome FROM Usuarios WHERE tipo_usuario = 'aluno'";
$resultAlunos = mysqli_query($conn, $queryAlunos);

// Consulta para obter a lista de professores
$queryProfessores = "SELECT id_usuario, nome FROM Usuarios WHERE tipo_usuario = 'professor'";
$resultProfessores = mysqli_query($conn, $queryProfessores);

if (!$sessao) {
    $erro_acesso = "Sessão não encontrada.";
}
// Consulta para obter os arquivos anexados à sessão
$queryArquivos = "SELECT id_arquivo, tipo_documento, data_upload FROM ArquivosDigitalizados WHERE id_sessao = ?";
$stmtArquivos = mysqli_prepare($conn, $queryArquivos);
mysqli_stmt_bind_param($stmtArquivos, "i", $idSessao);
mysqli_stmt_execute($stmtArquivos);
$resultArquivos = mysqli_stmt_get_result($stmtArquivos);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['botao']) && $_POST['botao'] == 'Concluído') {
    $data = trim($_POST["data"]);
    $registroSessao = trim($_POST["registro_sessao"]);
    $anotacoes = trim($_POST["anotacoes"]);

    // Validações
    if (empty($data) || !validateDate($data)) {
        $erro_acesso = "Por favor, preencha uma data válida.";
    } elseif (empty($registroSessao)) {
        $erro_acesso = "Por favor, preencha o registro da sessão.";
    } else {
        $queryUpdate = "UPDATE Sessoes SET data = ?, registro_sessao = ?, anotacoes = ?, rascunho = 0 WHERE id_sessao = ?";
        $stmtUpdate = mysqli_prepare($conn, $queryUpdate);
        mysqli_stmt_bind_param($stmtUpdate, "sssi", $data, $registroSessao, $anotacoes, $idSessao);

        if (mysqli_stmt_execute($stmtUpdate)) {
            // Atualiza a notificação para resolvido, se existir
            $queryNotificacao = "UPDATE Avisos SET status = 'resolvido' WHERE id_sessao = ? AND status = 'pendente'";
            $stmtNotificacao = mysqli_prepare($conn, $queryNotificacao);
            mysqli_stmt_bind_param($stmtNotificacao, "i", $idSessao);
            mysqli_stmt_execute($stmtNotificacao);
            mysqli_stmt_close($stmtNotificacao);

            $sucesso_acesso = "Dados da sessão atualizados com sucesso!";
            // Recarrega a página para mostrar os dados atualizados
            header("Location: acessarSessoes.php?id=" . $idSessao);
            exit;
        } else {
            $erro_acesso = "Erro ao atualizar os dados da sessão: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmtUpdate);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Acessar Sessão</title>
  <link rel="stylesheet" href="../estilo.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/mainContent.css">
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const alterarBtn = document.getElementById('alterarBtn');
      const concluidoBtn = document.getElementById('concluidoBtn');
      const formInputs = document.querySelectorAll('.main_form input, .main_form textarea');
      const alunoInput = document.getElementById('aluno');
      const pacienteInput = document.getElementById('paciente');
      const professorInput = document.getElementById('professor');

      alterarBtn.addEventListener('click', function() {
          formInputs.forEach(input => {
              if (input !== alunoInput && input !== pacienteInput && input !== professorInput) {
                  input.disabled = false;
              }
          });
          alterarBtn.disabled = true;
          checkFormInputs();
      });

      formInputs.forEach(input => {
          input.addEventListener('input', checkFormInputs);
      });

      function checkFormInputs() {
          let allFilled = true;
          formInputs.forEach(input => {
              if (input !== alunoInput && input !== pacienteInput && input !== professorInput && input.value === '') {
                  allFilled = false;
              }
          });
          concluidoBtn.disabled = !allFilled;
      }

      // Validação da data
      document.getElementById('data').addEventListener('blur', function() {
          var data = this.value;
          var dataError = document.getElementById('dataError');
          if (!isValidDate(data)) {
              dataError.textContent = 'Por favor, preencha uma data válida.';
          } else {
              dataError.textContent = '';
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
    });
  </script>
</head>
<body>
  <div class="body_section">
    <!-- Inclui o conteúdo de sidebar.php -->
    <?php include('sidebar.php'); ?>
    <main>
      <div class="main_title"><h2>Informações da Sessão</h2></div>
      <div class="content">
        <?php if (!empty($erro_acesso)): ?>
          <div class="error"><?php echo $erro_acesso; ?></div>
        <?php elseif (!empty($sucesso_acesso)): ?>
          <div class="success"><?php echo $sucesso_acesso; ?></div>
        <?php endif; ?>
        <form class="main_form" action="acessarSessoes.php?id=<?php echo $idSessao; ?>" method="post">
          <div class="form_group">
            <div class="form_input">
              <label for="paciente">Paciente:</label>
              <input type="text" name="paciente" id="paciente" value="<?php echo isset($sessao['nome_paciente']) ? $sessao['nome_paciente'] : ''; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="aluno">Aluno:</label>
              <input type="text" name="aluno" id="aluno" value="<?php echo isset($sessao['nome_aluno']) ? $sessao['nome_aluno'] : ''; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="professor">Professor:</label>
              <input type="text" name="professor" id="professor" value="<?php echo isset($sessao['nome_professor']) ? $sessao['nome_professor'] : ''; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="data">Data:</label>
              <input type="date" name="data" id="data" value="<?php echo $sessao['data']; ?>" disabled>
              <span id="dataError" class="error"></span>
            </div>
            <div class="form_text_area">
              <label for="registro_sessao">Registro da Sessão:</label>
              <textarea name="registro_sessao" id="registro_sessao" disabled><?php echo $sessao['registro_sessao']; ?></textarea>
            </div>
            <div class="form_text_area">
              <label for="anotacoes">Anotações:</label>
              <textarea name="anotacoes" id="anotacoes" disabled><?php echo $sessao['anotacoes']; ?></textarea>
            </div>
          </div>
          <div class="form_group full_width">
            <button type="button" id="alterarBtn" class="botao_azul text_button">Alterar</button>
            <button type="submit" id="concluidoBtn" class="botao_azul text_button" name="botao" value="Concluído" disabled>Concluído</button>
            <a href="mainContent.php?tipo=sessoes" class="botao_azul text_button">Voltar</a>
          </div>
        </form>
        <div class="file_list">
          <h3>Arquivos Anexados</h3>
          <ul>
            <?php while ($arquivo = mysqli_fetch_assoc($resultArquivos)): ?>
              <li>
                <a href="baixarArquivo.php?id=<?php echo $arquivo['id_arquivo']; ?>"><?php echo $arquivo['tipo_documento']; ?> (<?php echo $arquivo['data_upload']; ?>)</a>
              </li>
            <?php endwhile; ?>
          </ul>
        </div>
      </div>
    </main>
  </div>
</body>
</html>