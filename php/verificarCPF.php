<?php
include('../config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cpf = trim($_POST["cpf"]);

    // Verificar se o CPF já está cadastrado
    $queryCPF = "SELECT id_paciente FROM Pacientes WHERE cpf = ?";
    $stmtCPF = mysqli_prepare($conn, $queryCPF);
    mysqli_stmt_bind_param($stmtCPF, "s", $cpf);
    mysqli_stmt_execute($stmtCPF);
    mysqli_stmt_store_result($stmtCPF);

    if (mysqli_stmt_num_rows($stmtCPF) > 0) {
        echo 'existente';
    } else {
        echo 'disponivel';
    }

    mysqli_stmt_close($stmtCPF);
}
?>