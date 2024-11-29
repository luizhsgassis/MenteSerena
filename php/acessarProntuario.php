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
$idPaciente = isset($_GET['paciente_id']) ? $_GET['paciente_id'] : '';
$idUsuarioLogado = $_SESSION['id_usuario'];
$nivelAcesso = $_SESSION['UsuarioNivel'];

$erro_acesso = '';
$sucesso_acesso = '';

// Consulta para obter as informações do prontuário
$queryProntuario = "SELECT * FROM Prontuarios WHERE id_paciente = ?";
$stmtProntuario = mysqli_prepare($conn, $queryProntuario);
mysqli_stmt_bind_param($stmtProntuario, "i", $idPaciente);
mysqli_stmt_execute($stmtProntuario);
$resultProntuario = mysqli_stmt_get_result($stmtProntuario);
$prontuario = mysqli_fetch_assoc($resultProntuario);
mysqli_stmt_close($stmtProntuario);

if (!$prontuario) {
    $erro_acesso = "Prontuário não encontrado.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['botao']) && $_POST['botao'] == 'Concluído') {
    $dataAbertura = trim($_POST["data_abertura"]);
    $historicoFamiliar = trim($_POST["historico_familiar"]);
    $historicoSocial = trim($_POST["historico_social"]);
    $consideracoesFinais = trim($_POST["consideracoes_finais"]);

    // Validações
    if (empty($dataAbertura) || !validateDate($dataAbertura)) {
        $erro_acesso = "Por favor, preencha uma data de abertura válida.";
    } else {
        $queryUpdate = "UPDATE Prontuarios SET data_abertura = ?, historico_familiar = ?, historico_social = ?, consideracoes_finais = ? WHERE id_prontuario = ?";
        $stmtUpdate = mysqli_prepare($conn, $queryUpdate);
        mysqli_stmt_bind_param($stmtUpdate, "issssi", $dataAbertura, $historicoFamiliar, $historicoSocial, $consideracoesFinais, $prontuario['id_prontuario']);

        if (mysqli_stmt_execute($stmtUpdate)) {
            $sucesso_acesso = "Dados do prontuário atualizados com sucesso!";
            // Recarrega a página para mostrar os dados atualizados
            header("Location: acessarProntuario.php?paciente_id=" . $idPaciente);
            exit;
        } else {
            $erro_acesso = "Erro ao atualizar os dados do prontuário: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmtUpdate);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['botao']) && $_POST['botao'] == 'Baixar Prontuário') {
    $id = isset($_GET['paciente_id']) ? $_GET['paciente_id'] : '';
    if ($id <= 0) {
        die("ID inválido.");
    }

    // Consulta para obter o nome do paciente
    $queryPaciente = "SELECT nome FROM Pacientes WHERE id_paciente = ?";
    $stmtPaciente = mysqli_prepare($conn, $queryPaciente);
    mysqli_stmt_bind_param($stmtPaciente, "i", $id);
    mysqli_stmt_execute($stmtPaciente);
    mysqli_stmt_bind_result($stmtPaciente, $nomePaciente);
    mysqli_stmt_fetch($stmtPaciente);
    mysqli_stmt_close($stmtPaciente);

    // Consulta para obter os dados do prontuário
    $sql = "SELECT * FROM Prontuarios WHERE id_paciente = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Pega os dados da linha
        $row = $result->fetch_assoc();

        // Monta o conteúdo do arquivo
        $content = "Prontuário do Paciente: $nomePaciente\n\n";
        $content .= "Dados do Prontuário\n";
        $content .= "-------------------\n";
        $content .= "Data de Abertura: " . $row['data_abertura'] . "\n";
        $content .= "Histórico Familiar: " . $row['historico_familiar'] . "\n";
        $content .= "Histórico Social: " . $row['historico_social'] . "\n";
        $content .= "Considerações Finais: " . $row['consideracoes_finais'] . "\n";

        // Configura os headers para download do arquivo
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $nomePaciente . '_prontuario.txt"');
        header('Content-Length: ' . strlen($content));

        // Envia o conteúdo
        echo $content;
        exit;
    } else {
        echo "Nenhuma linha encontrada.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Acessar Prontuário</title>
  <link rel="stylesheet" href="../estilo.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/mainContent.css">
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
        const baixarBtn = document.getElementById('baixarBtn');
        const formInputs = document.querySelectorAll('.main_form input, .main_form textarea, .main_form select');

        alterarBtn.addEventListener('click', function() {
            formInputs.forEach(input => input.disabled = false);
            alterarBtn.disabled = true;
            concluidoBtn.disabled = false;
            baixarBtn.disabled = false;
        });

        // Validação da data de abertura
        document.getElementById('data_abertura').addEventListener('blur', function() {
            var dataAbertura = this.value;
            var dataAberturaError = document.getElementById('dataAberturaError');
            if (!isValidDate(dataAbertura)) {
                dataAberturaError.textContent = 'Por favor, preencha uma data de abertura válida.';
            } else {
                dataAberturaError.textContent = '';
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
      <div class="main_title"><h2>Informações do Prontuário</h2></div>
      <div class="content">
        <?php if (!empty($erro_acesso)): ?>
          <div class="error"><?php echo $erro_acesso; ?></div>
        <?php elseif (!empty($sucesso_acesso)): ?>
          <div class="success"><?php echo $sucesso_acesso; ?></div>
        <?php endif; ?>
        <form class="main_form" action="acessarProntuario.php?paciente_id=<?php echo $idPaciente; ?>" method="post">
          <div class="form_group full_width">
            <div class="form_input">
            </div>
            <div class="form_input">
              <label for="data_abertura">Data de Abertura:</label>
              <input type="date" name="data_abertura" id="data_abertura" value="<?php echo $prontuario['data_abertura']; ?>" disabled>
              <span id="dataAberturaError" class="error"></span>
            </div>
            <div class="form_text_area">
              <label for="historico_familiar">Histórico Familiar:</label>
              <textarea name="historico_familiar" id="historico_familiar" disabled><?php echo $prontuario['historico_familiar']; ?></textarea>
            </div>
            <div class="form_text_area">
              <label for="historico_social">Histórico Social:</label>
              <textarea name="historico_social" id="historico_social" disabled><?php echo $prontuario['historico_social']; ?></textarea>
            </div>
            <div class="form_text_area">
              <label for="consideracoes_finais">Considerações Finais:</label>
              <textarea name="consideracoes_finais" id="consideracoes_finais" disabled><?php echo $prontuario['consideracoes_finais']; ?></textarea>
            </div>
          </div>
          <div class="form_group full_width">
            <button type="button" id="alterarBtn" class="botao_azul text_button">Alterar</button>
            <button type="submit" id="concluidoBtn" class="botao_azul text_button" name="botao" value="Concluído" disabled>Concluído</button>
            <button type="submit" id="baixarBtn" class="botao_azul text_button" name="botao" value="Baixar Prontuário" disabled>Baixar Prontuário</button>
            <a href="acessarPacientes.php?id=<?php echo $idPaciente; ?>" class="botao_azul text_button">Voltar</a>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>
</html>