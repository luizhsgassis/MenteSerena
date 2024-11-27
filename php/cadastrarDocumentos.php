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
    $idUsuario = $_SESSION['id_usuario'];
    $dataUpload = date('Y-m-d');

    // Validações iniciais
    if (empty($idPaciente) || empty($idSessao)) {
        $erro_cadastro = "Por favor, preencha todos os campos obrigatórios.";
    } else {
        // Verifica se o arquivo foi enviado sem erros
        if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] == UPLOAD_ERR_OK) {
            $arquivoTmp = $_FILES['arquivo']['tmp_name'];
            $arquivoNome = $_FILES['arquivo']['name'];
            $arquivoTipo = $_FILES['arquivo']['type'];
            $arquivoConteudo = file_get_contents($arquivoTmp);

            // Detecta o tipo de documento pelo cabeçalho MIME ou extensão
            $extensao = strtolower(pathinfo($arquivoNome, PATHINFO_EXTENSION));
            $tiposPermitidos = [
                'pdf' => 'application/pdf',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'txt' => 'text/plain',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ];

            if (!array_key_exists($extensao, $tiposPermitidos) || $arquivoTipo !== $tiposPermitidos[$extensao]) {
                $erro_cadastro = "O tipo de arquivo não é permitido. Formatos aceitos: PDF, DOCX, TXT, EXCEL";
            } else {
                $tipoDocumentoDetectado = strtoupper($extensao);

                // Inserir dados no banco de dados
                $queryArquivo = "INSERT INTO ArquivosDigitalizados (id_paciente, id_usuario, id_sessao, tipo_documento, data_upload, arquivo, nome_original) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmtArquivo = mysqli_prepare($conn, $queryArquivo);
                mysqli_stmt_bind_param($stmtArquivo, "iiissss", $idPaciente, $idUsuario, $idSessao, $tipoDocumentoDetectado, $dataUpload, $arquivoConteudo, $arquivoNome);

                if (mysqli_stmt_execute($stmtArquivo)) {
                    $sucesso_cadastro = "Documento cadastrado com sucesso! Tipo detectado: $tipoDocumentoDetectado.";
                } else {
                    $erro_cadastro = "Erro ao cadastrar documento: " . mysqli_error($conn);
                }
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
$querySessoes = "SELECT id_sessao, data FROM Sessoes";
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
                                option.textContent = sessao.id_sessao;
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
                    </div>
                    <div class="form_group full_width">
                        <div class="file_attachment">
                            <input type="file" name="arquivo" id="arquivo" class="file_input" required>
                            <label for="arquivo" class="file_label">
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