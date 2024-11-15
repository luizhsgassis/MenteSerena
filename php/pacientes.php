<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /MenteSerena-master/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pacientes</title>
    <link rel="stylesheet" href="../estilo.css">
    <link rel="stylesheet" href="../css/sidebar.css">
</head>
<body>
    
    <!-- Inclui o conteúdo de sidebar.php -->
    <?php include('sidebar.php'); ?>
    <div class="content">
        
        <!-- Conteúdo da página de pacientes -->
        <h1>Pacientes</h1>
        <!-- Adicione aqui o conteúdo específico da página de pacientes -->
    </div>
</body>
</html>