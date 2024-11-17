<?php
session_start();

include('../config.php');
include('functions.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /MenteSerena-master/index.php");
    exit;
}

$erro_cadastro = '';
$sucesso_cadastro = '';

// Obtém o ID do paciente da URL
$idPaciente = isset($_GET['paciente_id']) ? $_GET['paciente_id'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idPaciente = $_POST["paciente"];
    $idUsuario = $_SESSION['id_usuario'];
    $dataAbertura = $_POST["data_abertura"];
    $historicoFamiliar = $_POST["historico_familiar"];
    $historicoSocial = $_POST["historico_social"];
    $consideracoesFinais = $_POST["consideracoes_finais"];

    // Validações
    if (empty($idPaciente) || empty($dataAbertura) || empty($historicoFamiliar) || empty($historicoSocial)) {
        $erro_cadastro = "Por favor, preencha todos os campos obrigatórios.";
    } elseif (!validateDate($dataAbertura)) {
        $erro_cadastro = "Por favor, preencha uma data válida.";
    } else {
        // Inserir dados na tabela Prontuarios
        $queryProntuario = "INSERT INTO Prontuarios (id_paciente, id_usuario, data_abertura, historico_familiar, historico_social, consideracoes_finais) VALUES (?, ?, ?, ?, ?, ?)";
        $stmtProntuario = mysqli_prepare($conn, $queryProntuario);
        mysqli_stmt_bind_param($stmtProntuario, "iissss", $idPaciente, $idUsuario, $dataAbertura, $historicoFamiliar, $historicoSocial, $consideracoesFinais);

        if (mysqli_stmt_execute($stmtProntuario)) {
            $sucesso_cadastro = "Prontuário cadastrado com sucesso!";
        } else {
            $erro_cadastro = "Erro ao cadastrar prontuário: " . mysqli_error($conn);
        }
    }
}

// Consulta para obter o nome do paciente
$queryPaciente = "SELECT nome FROM Pacientes WHERE id_paciente = ?";
$stmtPaciente = mysqli_prepare($conn, $queryPaciente);
mysqli_stmt_bind_param($stmtPaciente, "i", $idPaciente);
mysqli_stmt_execute($stmtPaciente);
mysqli_stmt_bind_result($stmtPaciente, $nomePaciente);
mysqli_stmt_fetch($stmtPaciente);
mysqli_stmt_close($stmtPaciente);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Prontuário</title>
    <link rel="stylesheet" href="../estilo.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/mainContent.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validação da data
            document.getElementById('data_abertura').addEventListener('blur', function() {
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
            <div class="main_title"><h2>Cadastrar Prontuário</h2></div>
            <div class="content">
                <?php if (!empty($erro_cadastro)): ?>
                    <div class="error"><?php echo $erro_cadastro; ?></div>
                <?php endif; ?>
                <?php if (!empty($sucesso_cadastro)): ?>
                    <div class="success"><?php echo $sucesso_cadastro; ?></div>
                <?php endif; ?>
                <form class="main_form" action="cadastrarProntuario.php" method="post">
                    <div class="form_group">
                        <div class="form_input">
                            <label for="paciente">Paciente:</label>
                            <input type="text" name="paciente_nome" id="paciente_nome" value="<?php echo $nomePaciente; ?>" disabled>
                            <input type="hidden" name="paciente" id="paciente" value="<?php echo $idPaciente; ?>">
                        </div>
                        <div class="form_input">
                            <label for="data_abertura">Data de Abertura:</label>
                            <input type="date" name="data_abertura" id="data_abertura" required>
                            <span id="dataError" class="error"></span>
                        </div>
                    </div>
                    <div class="form_group full_width">
                        <div class="form_text_area">
                            <label for="historico_familiar">Histórico Familiar:</label>
                            <textarea name="historico_familiar" id="historico_familiar" required></textarea>
                        </div>
                        <div class="form_text_area">
                            <label for="historico_social">Histórico Social:</label>
                            <textarea name="historico_social" id="historico_social" required></textarea>
                        </div>
                        <div class="form_text_area">
                            <label for="consideracoes_finais">Considerações Finais:</label>
                            <textarea name="consideracoes_finais" id="consideracoes_finais"></textarea>
                        </div>
                        <button class="botao_cadastro text_button" type="submit" name="botao" value="Cadastrar">Cadastrar</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>