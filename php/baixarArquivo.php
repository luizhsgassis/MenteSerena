<?php
session_start();

include('../config.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /MenteSerena-master/index.php");
    exit;
}

// Verifica se o ID do arquivo foi passado na URL
if (!isset($_GET['id'])) {
    echo "ID do arquivo não fornecido.";
    exit;
}

$idArquivo = intval($_GET['id']);

// Consulta para obter o arquivo do banco de dados
$queryArquivo = "SELECT tipo_documento, arquivo, nome_arquivo FROM ArquivosDigitalizados WHERE id_arquivo = ?";
$stmtArquivo = mysqli_prepare($conn, $queryArquivo);
mysqli_stmt_bind_param($stmtArquivo, "i", $idArquivo);
mysqli_stmt_execute($stmtArquivo);
mysqli_stmt_store_result($stmtArquivo);
mysqli_stmt_bind_result($stmtArquivo, $tipoDocumento, $arquivo, $nomeArquivo);
mysqli_stmt_fetch($stmtArquivo);

if (mysqli_stmt_num_rows($stmtArquivo) == 0) {
    echo "Arquivo não encontrado.";
    exit;
}

// Verificar se o conteúdo do arquivo está sendo recuperado corretamente
if (empty($arquivo)) {
    echo "Erro ao recuperar o conteúdo do arquivo.";
    exit;
}

mysqli_stmt_close($stmtArquivo);

// Define os cabeçalhos apropriados para forçar o download do arquivo
header("Content-Type: $tipoDocumento");
header("Content-Disposition: attachment; filename=\"$nomeArquivo\"");
header("Content-Length: " . strlen($arquivo));
echo $arquivo;
?>