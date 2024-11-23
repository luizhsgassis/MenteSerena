<?php
include('../config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pacienteId = intval($_POST["paciente_id"]);

    // Consulta para obter as sessões do paciente selecionado
    $querySessoes = "SELECT id_sessao, registro_sessao FROM Sessoes WHERE id_paciente = ?";
    $stmtSessoes = mysqli_prepare($conn, $querySessoes);
    mysqli_stmt_bind_param($stmtSessoes, "i", $pacienteId);
    mysqli_stmt_execute($stmtSessoes);
    $resultSessoes = mysqli_stmt_get_result($stmtSessoes);

    $sessoes = [];
    while ($row = mysqli_fetch_assoc($resultSessoes)) {
        $sessoes[] = $row;
    }

    echo json_encode($sessoes);
    mysqli_stmt_close($stmtSessoes);
}
?>