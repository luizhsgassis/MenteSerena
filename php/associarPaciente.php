<?php
session_start();

include('../config.php');
include('functions.php');

if (!isset($_GET['cpf'])) {
    header("Location: cadastrarPacientes.php");
    exit;
}

$cpf = $_GET['cpf'];
$idAluno = $_SESSION['id_usuario'];

// Verificar se o paciente existe pelo CPF
$queryVerificaPaciente = "SELECT id_paciente FROM Pacientes WHERE cpf = ?";
$stmtVerificaPaciente = mysqli_prepare($conn, $queryVerificaPaciente);
mysqli_stmt_bind_param($stmtVerificaPaciente, "s", $cpf);
mysqli_stmt_execute($stmtVerificaPaciente);
mysqli_stmt_bind_result($stmtVerificaPaciente, $idPaciente);
mysqli_stmt_fetch($stmtVerificaPaciente);
mysqli_stmt_close($stmtVerificaPaciente);

if (!$idPaciente) {
    // Paciente não encontrado, redirecionar para a página de cadastro
    header("Location: cadastrarPacientes.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $queryAssociacao = "INSERT INTO AssociacaoPacientesAlunos (id_paciente, id_aluno) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $queryAssociacao);
    mysqli_stmt_bind_param($stmt, "ii", $idPaciente, $idAluno);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: mainContent.php?tipo=pacientes");
        exit;
    } else {
        $erroAssociacao = "Erro ao associar paciente: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Associar Paciente</title>
  <link rel="stylesheet" href="../estilo.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/mainContent.css">
</head>
<body>
    <div class="body_section">
        <?php include('sidebar.php'); ?>
        <main>
            <div class="main_title"><h2>Associar Paciente</h2></div>
            <div class="content">
                <?php if (!empty($erroAssociacao)): ?>
                    <div class="error"><?php echo $erroAssociacao; ?></div>
                <?php endif; ?>
                <form class="main_form" action="associarPaciente.php?cpf=<?php echo $cpf; ?>" method="post">
                    <div class="form_group">
                        <p>O paciente com CPF <?php echo $cpf; ?> já está cadastrado. Deseja associá-lo ao seu perfil?</p>
                    </div>
                    <div class="form_group full_width">
                        <button class="botao_cadastro text_button" type="submit" name="botao" value="Associar">Associar</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>