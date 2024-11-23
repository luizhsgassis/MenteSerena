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

// Obtém o ID do aluno logado
$idAlunoLogado = $_SESSION['id_usuario'];
$nomeAlunoLogado = $_SESSION['nome_usuario'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idPaciente = $_POST["paciente"];
    $idAluno = $_POST["usuario_aluno"];
    $idProfessor = $_POST["usuario_professor"];
    $data = $_POST["data"];
    $registroSessao = $_POST["registro_sessao"];
    $anotacoes = $_POST["anotacoes"];
    $idUsuario = $_SESSION['id_usuario'];
    $rascunho = ($_POST['botao'] == 'Rascunho') ? 1 : 0;

    // Consulta para obter o ID do prontuário do paciente
    $queryProntuario = "SELECT id_prontuario FROM Prontuarios WHERE id_paciente = ?";
    $stmtProntuario = mysqli_prepare($conn, $queryProntuario);
    mysqli_stmt_bind_param($stmtProntuario, "i", $idPaciente);
    mysqli_stmt_execute($stmtProntuario);
    mysqli_stmt_bind_result($stmtProntuario, $idProntuario);
    mysqli_stmt_fetch($stmtProntuario);
    mysqli_stmt_close($stmtProntuario);

    // Inserir dados na tabela Sessoes
    $querySessao = "INSERT INTO Sessoes (id_prontuario, id_paciente, id_usuario, data, registro_sessao, anotacoes, rascunho) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmtSessao = mysqli_prepare($conn, $querySessao);
    mysqli_stmt_bind_param($stmtSessao, "iiisssi", $idProntuario, $idPaciente, $idUsuario, $data, $registroSessao, $anotacoes, $rascunho);

    if (mysqli_stmt_execute($stmtSessao)) {
        $idSessao = mysqli_insert_id($conn);
        $sucesso_cadastro = "Sessão cadastrada com sucesso!";

        // Lidar com o upload do arquivo
        if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == UPLOAD_ERR_OK) {
            $arquivoTmp = $_FILES['arquivo']['tmp_name'];
            $arquivoNome = $_FILES['arquivo']['name'];
            $arquivoTipo = $_FILES['arquivo']['type'];
            $arquivoData = date('Y-m-d');
            $arquivoConteudo = file_get_contents($arquivoTmp);

            // Inserir dados na tabela ArquivosDigitalizados
            $queryArquivo = "INSERT INTO ArquivosDigitalizados (id_paciente, id_usuario, id_sessao, tipo_documento, data_upload, arquivo) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtArquivo = mysqli_prepare($conn, $queryArquivo);
            mysqli_stmt_bind_param($stmtArquivo, "iiisss", $idPaciente, $idUsuario, $idSessao, $arquivoTipo, $arquivoData, $arquivoConteudo);

            if (mysqli_stmt_execute($stmtArquivo)) {
                $sucesso_cadastro .= " Arquivo anexado com sucesso!";
            } else {
                $erro_cadastro = "Erro ao anexar o arquivo: " . mysqli_error($conn);
            }
        }

        // Verificar se a sessão é um rascunho e criar notificação se necessário
        if ($rascunho) {
            $queryNotificacao = "INSERT INTO Avisos (id_sessao, id_usuario, mensagem, data, status) VALUES (?, ?, ?, ?, 'pendente')";
            $stmtNotificacao = mysqli_prepare($conn, $queryNotificacao);
            $mensagem = "A sessão de ID $idSessao está em rascunho há mais de 48 horas.";
            $dataNotificacao = date('Y-m-d H:i:s');
            mysqli_stmt_bind_param($stmtNotificacao, "iiss", $idSessao, $idUsuario, $mensagem, $dataNotificacao);
            mysqli_stmt_execute($stmtNotificacao);
            mysqli_stmt_close($stmtNotificacao);
        }
    } else {
        $erro_cadastro = "Erro ao cadastrar sessão: " . mysqli_error($conn);
    }
}

// Consulta para obter a lista de pacientes relacionados ao aluno logado
$queryPacientes = "SELECT p.id_paciente, p.nome 
                   FROM Pacientes p
                   INNER JOIN AssociacaoPacientesAlunos apa ON p.id_paciente = apa.id_paciente
                   WHERE apa.id_aluno = ?";
$stmtPacientes = mysqli_prepare($conn, $queryPacientes);
mysqli_stmt_bind_param($stmtPacientes, "i", $idAlunoLogado);
mysqli_stmt_execute($stmtPacientes);
$resultPacientes = mysqli_stmt_get_result($stmtPacientes);

// Consulta para obter a lista de professores
$queryProfessores = "SELECT id_usuario, nome FROM Usuarios WHERE tipo_usuario = 'professor'";
$resultProfessores = mysqli_query($conn, $queryProfessores);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Sessões</title>
    <link rel="stylesheet" href="../estilo.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/mainContent.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const finalizarBtn = document.getElementById('finalizarBtn');
            const formInputs = document.querySelectorAll('.main_form input[required], .main_form select[required], .main_form textarea');
            const registroSessao = document.getElementById('registro_sessao');
            const anotacoes = document.getElementById('anotacoes');

            formInputs.forEach(input => {
                input.addEventListener('input', checkFormInputs);
            });

            function checkFormInputs() {
                let allFilled = true;
                formInputs.forEach(input => {
                    if (input.value === '') {
                        allFilled = false;
                    }
                });

                if (registroSessao.value === '' || anotacoes.value === '') {
                    allFilled = false;
                }

                finalizarBtn.disabled = !allFilled;
                finalizarBtn.classList.toggle('disabled', !allFilled);
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

            // Atualizar o texto do botão para o nome do documento anexado
            document.getElementById('arquivo').addEventListener('change', function() {
                var fileName = this.files[0].name;
                var fileLabel = document.querySelector('.file_label span');
                fileLabel.textContent = fileName;
            });

            $('#paciente').change(function() {
                var pacienteId = $(this).val();
                if (pacienteId) {
                    // Habilita os campos "Aluno" e "Professor"
                    $('#usuario_aluno').prop('disabled', false);
                    $('#usuario_professor').prop('disabled', false);

                    // Faz uma requisição AJAX para buscar os alunos e professores relacionados ao paciente
                    $.ajax({
                        url: 'buscarUsuarios.php',
                        type: 'POST',
                        data: { paciente_id: pacienteId },
                        success: function(response) {
                            var data = JSON.parse(response);
                            // Preenche o dropdown "Aluno"
                            $('#usuario_aluno').empty();
                            $('#usuario_aluno').append('<option value="">Selecione um aluno</option>');
                            $.each(data.alunos, function(key, value) {
                                $('#usuario_aluno').append('<option value="' + value.id_usuario + '">' + value.nome + '</option>');
                            });

                            // Preenche o dropdown "Professor"
                            $('#usuario_professor').empty();
                            $('#usuario_professor').append('<option value="">Selecione um professor</option>');
                            $.each(data.professores, function(key, value) {
                                $('#usuario_professor').append('<option value="' + value.id_usuario + '">' + value.nome + '</option>');
                            });
                        }
                    });
                } else {
                    // Desabilita os campos "Aluno" e "Professor" se nenhum paciente for selecionado
                    $('#usuario_aluno').prop('disabled', true);
                    $('#usuario_professor').prop('disabled', true);
                }
            });
        });
    </script>
</head>
<body>
    <div class="body_section">
        <!-- Inclui o conteúdo de sidebar.php -->
        <?php include('sidebar.php'); ?>
        <main>
            <div class="main_title"><h2>Cadastrar Sessão</h2></div>
            <div class="content">
                <?php if (!empty($erro_cadastro)): ?>
                    <div class="error"><?php echo $erro_cadastro; ?></div>
                <?php endif; ?>
                <?php if (!empty($sucesso_cadastro)): ?>
                    <div class="success"><?php echo $sucesso_cadastro; ?></div>
                <?php endif; ?>
                <form class="main_form" action="cadastrarSessoes.php" method="post" enctype="multipart/form-data">
                    <div class="form_group">
                        <div class="form_input">
                            <label for="paciente">Paciente:</label>
                            <select name="paciente" id="paciente" required>
                                <option value="">Selecione um paciente</option>
                                <?php
                                while ($row = mysqli_fetch_assoc($resultPacientes)) {
                                    echo '<option value="' . $row['id_paciente'] . '">' . $row['nome'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form_input">
                            <label for="usuario_aluno">Aluno:</label>
                            <input type="text" name="usuario_aluno_nome" id="usuario_aluno_nome" value="<?php echo $nomeAlunoLogado; ?>" disabled>
                            <input type="hidden" name="usuario_aluno" id="usuario_aluno" value="<?php echo $idAlunoLogado; ?>" required>
                        </div>
                        <div class="form_input">
                            <label for="usuario_professor">Professor:</label>
                            <select name="usuario_professor" id="usuario_professor" required>
                                <option value="">Selecione um professor</option>
                                <?php
                                while ($row = mysqli_fetch_assoc($resultProfessores)) {
                                    echo '<option value="' . $row['id_usuario'] . '">' . $row['nome'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form_input">
                            <label for="data">Data:</label>
                            <input type="date" name="data" id="data" required>
                            <span id="dataError" class="error"></span>
                        </div>
                    </div>
                    <div class="form_group full_width">
                        <div class="form_text_area">
                            <label for="registro_sessao">Registro da Sessão:</label>
                            <textarea name="registro_sessao" id="registro_sessao"></textarea>
                        </div>
                        <div class="form_text_area">
                            <label for="anotacoes">Anotações:</label>
                            <textarea name="anotacoes" id="anotacoes"></textarea>
                        </div>
                        <div class="file_attachment">
                            <input type="file" name="arquivo" id="arquivo" class="file_input">
                            <label for="arquivo" class="file_label">
                                <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9.625 20.125C9.39294 20.125 9.17038 20.2172 9.00628 20.3813C8.84219 20.5454 8.75 20.7679 8.75 21C8.75 21.2321 8.84219 21.4546 9.00628 21.6187C9.17038 21.7828 9.39294 21.875 9.625 21.875H18.375C18.6071 21.875 18.8296 21.7828 18.9937 21.6187C19.1578 21.4546 19.25 21.2321 19.25 21C19.25 20.7679 19.1578 20.5454 18.9937 20.3813C18.8296 20.2172 18.6071 20.125 18.375 20.125H9.625ZM8.75 17.5C8.75 17.2679 8.84219 17.0454 9.00628 16.8813C9.17038 16.7172 9.39294 16.625 9.625 16.625H18.375C18.6071 16.625 18.8296 16.7172 18.9937 16.8813C19.1578 17.0454 19.25 17.2679 19.25 17.5C19.25 17.7321 19.1578 17.9546 18.9937 18.1187C18.8296 18.2828 18.6071 18.375 18.375 18.375H9.625C9.39294 18.375 9.17038 18.2828 9.00628 18.1187C8.84219 17.9546 8.75 17.7321 8.75 17.5ZM9.625 13.125C9.39294 13.125 9.17038 13.2172 9.00628 13.3813C8.84219 13.5454 8.75 13.7679 8.75 14C8.75 14.2321 8.84219 14.4546 9.00628 14.6187C9.17038 14.7828 9.39294 14.875 9.625 14.875H18.375C18.6071 14.875 18.8296 14.7828 18.9937 14.6187C19.1578 14.4546 19.25 14.2321 19.25 14C19.25 13.7679 19.1578 13.5454 18.9937 13.3813C18.8296 13.2172 18.6071 13.125 18.375 13.125H9.625ZM4.375 4.375C4.375 3.67881 4.65156 3.01113 5.14384 2.51884C5.63613 2.02656 6.30381 1.75 7 1.75H15.9005C16.5963 1.75038 17.2635 2.02702 17.7555 2.51912L22.8568 7.61862C23.3487 8.11094 23.6251 8.77849 23.625 9.4745V23.625C23.625 24.3212 23.3484 24.9889 22.8562 25.4812C22.3639 25.9734 21.6962 26.25 21 26.25H7C6.30381 26.25 5.63613 25.9734 5.14384 25.4812C4.65156 24.9889 4.375 24.3212 4.375 23.625V4.375ZM7 3.5C6.76794 3.5 6.54538 3.59219 6.38128 3.75628C6.21719 3.92038 6.125 4.14294 6.125 4.375V23.625C6.125 23.8571 6.21719 24.0796 6.38128 24.2437C6.54538 24.4078 6.76794 24.5 7 24.5H21C21.2321 24.5 21.4546 24.4078 21.6187 24.2437C21.7828 24.0796 21.875 23.8571 21.875 23.625V10.5H17.5C16.8038 10.5 16.1361 10.2234 15.6438 9.73116C15.1516 9.23887 14.875 8.57119 14.875 7.875V3.5H7ZM17.5 8.75H21.5128L16.625 3.86225V7.875C16.625 8.10706 16.7172 8.32962 16.8813 8.49372C17.0454 8.65781 17.2679 8.75 17.5 8.75Z" fill="white"/>
                                </svg>
                                <span>Anexar Arquivo</span>
                            </label>
                        </div>
                        <button class="botao_cadastro text_button" type="submit" name="botao" value="Rascunho">Rascunho</button>
                        <button type="submit" id="finalizarBtn" class="botao_cadastro text_button" name="botao" value="Finalizar" disabled>Finalizar</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>