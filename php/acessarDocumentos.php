<?php
session_start();

include('../config.php');
include('functions.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /MenteSerena-master/index.php");
    exit;
}

// Obtém o ID do documento da URL
$idDocumento = isset($_GET['id']) ? $_GET['id'] : '';

$querySessao = "SELECT id_sessao FROM ArquivosDigitalizados WHERE id_arquivo = ?";
$stmtSessao = mysqli_prepare($conn, $querySessao);
mysqli_stmt_bind_param($stmtSessao, "i", $idDocumento);
mysqli_stmt_execute($stmtSessao);
$resultSessao = mysqli_stmt_get_result($stmtSessao);
$idSessao = mysqli_fetch_assoc($resultSessao);
mysqli_stmt_close($stmtSessao);

$erro_acesso = '';
$sucesso_acesso = '';

// Consulta para obter as informações do documento
$queryDocumento = "SELECT * FROM ArquivosDigitalizados WHERE id_arquivo = ?";
$stmtDocumento = mysqli_prepare($conn, $queryDocumento);
mysqli_stmt_bind_param($stmtDocumento, "i", $idDocumento);
mysqli_stmt_execute($stmtDocumento);
$resultDocumento = mysqli_stmt_get_result($stmtDocumento);
$documento = mysqli_fetch_assoc($resultDocumento);
mysqli_stmt_close($stmtDocumento);

if (!$documento) {
    $erro_acesso = "Documento não encontrado.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['botao'])) {
    if ($_POST['botao'] == 'Deletar') {
        $queryDelete = "DELETE FROM ArquivosDigitalizados WHERE id_arquivo = ?";
        $stmtDelete = mysqli_prepare($conn, $queryDelete);
        mysqli_stmt_bind_param($stmtDelete, "i", $idDocumento);
        
        if (mysqli_stmt_execute($stmtDelete)) {
            $sucesso_acesso = "Documento deletado com sucesso!";
            // Redireciona para a página de documentos após a deleção
            header("Location: mainContent.php?tipo=documentos");
            exit;
        } else {
            $erro_acesso = "Erro ao deletar o documento: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmtDelete);
    } elseif ($_POST['botao'] == 'Concluído') {
        $tipoDocumento = trim($_POST["tipo_documento"]);
        $dataUpload = trim($_POST["data_upload"]);

        // Validações
        if (empty($tipoDocumento)) {
            $erro_acesso = "Por favor, preencha o tipo de documento.";
        } elseif (empty($dataUpload) || !validateDate($dataUpload)) {
            $erro_acesso = "Por favor, preencha uma data de upload válida.";
        } else {
            $queryUpdate = "UPDATE ArquivosDigitalizados SET tipo_documento = ?, data_upload = ? WHERE id_arquivo = ?";
            $stmtUpdate = mysqli_prepare($conn, $queryUpdate);
            mysqli_stmt_bind_param($stmtUpdate, "ssi", $tipoDocumento, $dataUpload, $idDocumento);

            if (mysqli_stmt_execute($stmtUpdate)) {
                $sucesso_acesso = "Dados do documento atualizados com sucesso!";
                // Recarrega a página para mostrar os dados atualizados
                header("Location: acessarDocumentos.php?id=" . $idDocumento);
                exit;
            } else {
                $erro_acesso = "Erro ao atualizar os dados do documento: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmtUpdate);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Acessar Documento</title>
  <link rel="stylesheet" href="../estilo.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/mainContent.css">
  <script>
    document.addEventListener('DOMContentLoaded', function() {
    const habilitarBtn = document.getElementById('habilitarBtn');
    const deletarBtn = document.getElementById('deletarBtn'); // Corrected ID
    const formInputs = document.querySelectorAll('.main_form input');

    habilitarBtn.addEventListener('click', function() {
        habilitarBtn.disabled = true;
        deletarBtn.disabled = false; // This will now correctly enable the button
    });
  });
  </script>
</head>
<body>
  <div class="body_section">
    <!-- Inclui o conteúdo de sidebar.php -->
    <?php include('sidebar.php'); ?>
    <main>
      <div class="main_title"><h2>Informações do Documento</h2></div>
      <div class="content">
        <?php if (!empty($erro_acesso)): ?>
          <div class="error"><?php echo $erro_acesso; ?></div>
        <?php elseif (!empty($sucesso_acesso)): ?>
          <div class="success"><?php echo $sucesso_acesso; ?></div>
        <?php endif; ?>
        <form class="main_form" action="acessarDocumentos.php?id=<?php echo $idDocumento; ?>" method="post">
          <div class="form_group">
            <div class="form_input">
              <label for="tipo_documento">Tipo de Documento:</label>
              <input type="text" name="tipo_documento" id="tipo_documento" value="<?php echo $documento['tipo_documento']; ?>" disabled>
            </div>
            <div class="form_input">
              <label for="data_upload">Data de Upload:</label>
              <input type="date" name="data_upload" id="data_upload" value="<?php echo $documento['data_upload']; ?>" disabled>
              <span id="dataUploadError" class="error"></span>
            </div>
          </div>
          <div class="form_group full_width">
            <a href="baixarArquivo.php?id=<?php echo $idDocumento; ?>" class="botao_azul text_button" target="_blank">Baixar</a>
            <?php if (!empty($idSessao)): ?>
            <a href="acessarSessoes.php?id=<?php echo $idSessao['id_sessao']; ?>" class="botao_azul text_button">Acessar Sessão</a>
            <?php else: ?>
            <span class="error">Sessão não encontrada.</span>
            <?php endif; ?>
            <a href="mainContent.php?tipo=documentos" class="botao_azul text_button">Voltar</a>
            <br>
            <br>
            <br>
            <button type="submit" id="habilitarBtn" class="botao_azul text_button" name="botao" value="Habilitar Deleção">Habilitar Deleção</button>
            <button type="submit" id="deletarBtn" class="botao_azul text_button" name="botao" value="Deletar" disabled>Deletar</button>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>
</html>