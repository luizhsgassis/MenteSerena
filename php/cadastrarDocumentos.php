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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idPaciente = $_POST["paciente"];
    $idSessao = $_POST["sessao"];
    $tipoDocumento = $_POST["tipo_documento"];
    $idUsuario = $_SESSION['id_usuario'];
    $dataUpload = date('Y-m-d');

    // Validações
    if (empty($idPaciente) || empty($idSessao) || empty($tipoDocumento)) {
        $erro_cadastro = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        // Lidar com o upload do arquivo
        if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == UPLOAD_ERR_OK) {
            $arquivoTmp = $_FILES['arquivo']['tmp_name'];
            $arquivoNome = $_FILES['arquivo']['name'];
            $arquivoTipo = $_FILES['arquivo']['type'];
            $arquivoConteudo = file_get_contents($arquivoTmp);

            // Inserir dados na tabela ArquivosDigitalizados
            $queryArquivo = "INSERT INTO ArquivosDigitalizados (id_paciente, id_usuario, id_sessao, tipo_documento, data_upload, arquivo) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtArquivo = mysqli_prepare($conn, $queryArquivo);
            mysqli_stmt_bind_param($stmtArquivo, "iiisss", $idPaciente, $idUsuario, $idSessao, $tipoDocumento, $dataUpload, $arquivoConteudo);

            if (mysqli_stmt_execute($stmtArquivo)) {
                $sucesso_cadastro = "Documento cadastrado com sucesso!";
            } else {
                $erro_cadastro = "Erro ao cadastrar documento: " . mysqli_error($conn);
            }
        } else {
            $erro_cadastro = "Erro ao fazer upload do arquivo.";
        }
    }
}

// Consulta para obter a lista de pacientes
$queryPacientes = "SELECT id_paciente, nome FROM Pacientes";
$resultPacientes = mysqli_query($conn, $queryPacientes);

// Consulta para obter a lista de sessões
$querySessoes = "SELECT id_sessao, registro_sessao FROM Sessoes";
$resultSessoes = mysqli_query($conn, $querySessoes);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Documentos</title>
    <link rel="stylesheet" href="../estilo.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/mainContent.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Atualizar o texto do botão para o nome do documento anexado
            document.getElementById('arquivo').addEventListener('change', function() {
                var fileName = this.files[0].name;
                var fileLabel = document.querySelector('.file_label span');
                fileLabel.textContent = fileName;
            });

            // Atualizar a lista de sessões com base no paciente selecionado
            document.getElementById('paciente').addEventListener('change', function() {
                var pacienteId = this.value;
                var sessaoSelect = document.getElementById('sessao');
                sessaoSelect.innerHTML = '<option value="">Selecione uma sessão</option>'; // Limpar as opções atuais

                if (pacienteId) {
                    // Desbloquear o campo de sessão
                    sessaoSelect.removeAttribute('disabled');

                    // Fazer uma requisição AJAX para obter as sessões do paciente selecionado
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'obterSessoes.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            var sessoes = JSON.parse(xhr.responseText);
                            sessoes.forEach(function(sessao) {
                                var option = document.createElement('option');
                                option.value = sessao.id_sessao;
                                option.textContent = sessao.registro_sessao;
                                sessaoSelect.appendChild(option);
                            });
                        }
                    };
                    xhr.send('paciente_id=' + pacienteId);
                } else {
                    // Bloquear o campo de sessão se nenhum paciente for selecionado
                    sessaoSelect.setAttribute('disabled', 'disabled');
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
            <div class="main_title"><h2>Cadastrar Documento</h2></div>
            <div class="content">
                <?php if (!empty($erro_cadastro)): ?>
                    <div class="error"><?php echo $erro_cadastro; ?></div>
                <?php endif; ?>
                <?php if (!empty($sucesso_cadastro)): ?>
                    <div class="success"><?php echo $sucesso_cadastro; ?></div>
                <?php endif; ?>
                <form class="main_form" action="cadastrarDocumentos.php" method="post" enctype="multipart/form-data">
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
                            <label for="sessao">Sessão:</label>
                            <select name="sessao" id="sessao" required disabled>
                                <option value="">Selecione uma sessão</option>
                            </select>
                        </div>
                        <div class="form_input">
                            <label for="tipo_documento">Tipo de Documento:</label>
                            <input type="text" name="tipo_documento" id="tipo_documento" required>
                        </div>
                    </div>
                    <div class="form_group full_width">
                        <div class="file_attachment">
                            <input type="file" name="arquivo" id="arquivo" class="file_input" required>
                            <label for="arquivo" class="file_label">
                                <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9.625 20.125C9.39294 20.125 9.17038 20.2172 9.00628 20.3813C8.84219 20.5454 8.75 20.7679 8.75 21C8.75 21.2321 8.84219 21.4546 9.00628 21.6187C9.17038 21.7828 9.39294 21.875 9.625 21.875H18.375C18.6071 21.875 18.8296 21.7828 18.9937 21.6187C19.1578 21.4546 19.25 21.2321 19.25 21C19.25 20.7679 19.1578 20.5454 18.9937 20.3813C18.8296 20.2172 18.6071 20.125 18.375 20.125H9.625ZM8.75 17.5C8.75 17.2679 8.84219 17.0454 9.00628 16.8813C9.17038 16.7172 9.39294 16.625 9.625 16.625H18.375C18.6071 16.625 18.8296 16.7172 18.9937 16.8813C19.1578 17.0454 19.25 17.2679 19.25 17.5C19.25 17.7321 19.1578 17.9546 18.9937 18.1187C18.8296 18.2828 18.6071 18.375 18.375 18.375H9.625C9.39294 18.375 9.17038 18.2828 9.00628 18.1187C8.84219 17.9546 8.75 17.7321 8.75 17.5ZM9.625 13.125C9.39294 13.125 9.17038 13.2172 9.00628 13.3813C8.84219 13.5454 8.75 13.7679 8.75 14C8.75 14.2321 8.84219 14.4546 9.00628 14.6187C9.17038 14.7828 9.39294 14.875 9.625 14.875H18.375C18.6071 14.875 18.8296 14.7828 18.9937 14.6187C19.1578 14.4546 19.25 14.2321 19.25 14C19.25 13.7679 19.1578 13.5454 18.9937 13.3813C18.8296 13.2172 18.6071 13.125 18.375 13.125H9.625ZM4.375 4.375C4.375 3.67881 4.65156 3.01113 5.14384 2.51884C5.63613 2.02656 6.30381 1.75 7 1.75H15.9005C16.5963 1.75038 17.2635 2.02702 17.7555 2.51912L22.8568 7.61862C23.3487 8.11094 23.6251 8.77849 23.625 9.4745V23.625C23.625 24.3212 23.3484 24.9889 22.8562 25.4812C22.3639 25.9734 21.6962 26.25 21 26.25H7C6.30381 26.25 5.63613 25.9734 5.14384 25.4812C4.65156 24.9889 4.375 24.3212 4.375 23.625V4.375ZM7 3.5C6.76794 3.5 6.54538 3.59219 6.38128 3.75628C6.21719 3.92038 6.125 4.14294 6.125 4.375V23.625C6.125 23.8571 6.21719 24.0796 6.38128 24.2437C6.54538 24.4078 6.76794 24.5 7 24.5H21C21.2321 24.5 21.4546 24.4078 21.6187 24.2437C21.7828 24.0796 21.875 23.8571 21.875 23.625V10.5H17.5C16.8038 10.5 16.1361 10.2234 15.6438 9.73116C15.1516 9.23887 14.875 8.57119 14.875 7.875V3.5H7ZM17.5 8.75H21.5128L16.625 3.86225V7.875C16.625 8.10706 16.7172 8.32962 16.8813 8.49372C17.0454 8.65781 17.2679 8.75 17.5 8.75Z" fill="white"/>
                                </svg>
                                <span>Anexar Arquivo</span>
                            </label>
                        </div>
                        <button class="botao_cadastro text_button" type="submit" name="botao" value="Cadastrar">Cadastrar</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>