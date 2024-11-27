<?php
session_start();

include('../config.php');
include('functions.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /MenteSerena-master/php/logout.php");
    exit;
}

$erro_busca = '';
$sucesso_busca = '';
$sessoes = [];
$totalSessoes = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idPaciente = $_POST["paciente"];
    $idAluno = $_POST["aluno"];
    $idProfessor = $_POST["professor"];
    $dataInicio = $_POST["data_inicio"];
    $dataFim = $_POST["data_fim"];

    if (empty($dataInicio) || empty($dataFim)) {
        $erro_busca = "Os campos de data são obrigatórios.";
    } else {
        $query = "SELECT s.id_sessao, s.id_prontuario, s.id_paciente, s.id_usuario, s.data, s.registro_sessao, s.anotacoes, p.consideracoes_finais, pa.nome AS nome_paciente, u.nome AS nome_usuario 
                  FROM Sessoes s 
                  LEFT JOIN Prontuarios p ON s.id_prontuario = p.id_prontuario
                  LEFT JOIN Pacientes pa ON s.id_paciente = pa.id_paciente
                  LEFT JOIN Usuarios u ON s.id_usuario = u.id_usuario
                  WHERE s.data BETWEEN ? AND ?";

        $params = [$dataInicio, $dataFim];
        $types = "ss";

        if (!empty($idPaciente)) {
            $query .= " AND s.id_paciente = ?";
            $params[] = $idPaciente;
            $types .= "i";
        }
        if (!empty($idAluno)) {
            $query .= " AND s.id_usuario = ?";
            $params[] = $idAluno;
            $types .= "i";
        }

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $sessoes = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $totalSessoes = mysqli_num_rows($result);
        mysqli_stmt_close($stmt);
    }
}

// Consulta para obter a lista de pacientes
$queryPacientes = "SELECT id_paciente, nome FROM Pacientes";
$resultPacientes = mysqli_query($conn, $queryPacientes);

// Consulta para obter a lista de alunos
$queryAlunos = "SELECT id_usuario, nome FROM Usuarios WHERE tipo_usuario = 'aluno'";
$resultAlunos = mysqli_query($conn, $queryAlunos);

// Consulta para obter a lista de professores
$queryProfessores = "SELECT id_usuario, nome FROM Usuarios WHERE tipo_usuario = 'professor'";
$resultProfessores = mysqli_query($conn, $queryProfessores);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Sessões</title>
    <link rel="stylesheet" href="../estilo.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/mainContent.css">
</head>
<body>
    <div class="body_section">
        <!-- Inclui o conteúdo de sidebar.php -->
        <?php include('sidebar.php'); ?>
        <main>
            <div class="main_title"><h2>Relatório de Sessões</h2></div>
            <div class="content">
                <?php if (!empty($erro_busca)): ?>
                    <div class="error"><?php echo $erro_busca; ?></div>
                <?php endif; ?>
                <?php if (!empty($sucesso_busca)): ?>
                    <div class="success"><?php echo $sucesso_busca; ?></div>
                <?php endif; ?>
                <form class="main_form" action="relatorio.php" method="post">
                    <div class="form_group">
                        <div class="form_input">
                            <label for="paciente">Paciente:</label>
                            <select name="paciente" id="paciente">
                                <option value="">Selecione um paciente</option>
                                <?php
                                while ($row = mysqli_fetch_assoc($resultPacientes)) {
                                    echo '<option value="' . $row['id_paciente'] . '">' . $row['nome'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form_input">
                            <label for="aluno">Aluno:</label>
                            <select name="aluno" id="aluno">
                                <option value="">Selecione um aluno</option>
                                <?php
                                while ($row = mysqli_fetch_assoc($resultAlunos)) {
                                    echo '<option value="' . $row['id_usuario'] . '">' . $row['nome'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form_input">
                            <label for="professor">Professor:</label>
                            <select name="professor" id="professor">
                                <option value="">Selecione um professor</option>
                                <?php
                                while ($row = mysqli_fetch_assoc($resultProfessores)) {
                                    echo '<option value="' . $row['id_usuario'] . '">' . $row['nome'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form_input">
                            <label for="data_inicio">Data Início:</label>
                            <input type="date" name="data_inicio" id="data_inicio" required>
                        </div>
                        <div class="form_input">
                            <label for="data_fim">Data Fim:</label>
                            <input type="date" name="data_fim" id="data_fim" required>
                        </div>
                    </div>
                    <div class="form_group full_width">
                        <button type="submit" class="botao_cadastro text_button">Buscar</button>
                    </div>
                </form>
                <h3>Total de Sessões: <?php echo $totalSessoes; ?></h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Prontuário</th>
                                <th>Paciente</th>
                                <th>Usuário</th>
                                <th>Data</th>
                                <th>Registro da Sessão</th>
                                <th>Anotações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($totalSessoes > 0) {
                                foreach ($sessoes as $sessao) {
                                    echo "<tr>";
                                    echo "<td>{$sessao['id_sessao']}</td>";
                                    echo "<td>{$sessao['consideracoes_finais']}</td>";
                                    echo "<td>{$sessao['nome_paciente']}</td>";
                                    echo "<td>{$sessao['nome_usuario']}</td>";
                                    echo "<td>{$sessao['data']}</td>";
                                    echo "<td>{$sessao['registro_sessao']}</td>";
                                    echo "<td>{$sessao['anotacoes']}</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7'>Nenhuma sessão encontrada</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>