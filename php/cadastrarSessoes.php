<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /MenteSerena-master/index.php");
    exit;
}

include('../config.php');

// Consulta para obter a lista de pacientes
$queryPacientes = "SELECT id_paciente, nome FROM Pacientes";
$resultPacientes = mysqli_query($conn, $queryPacientes);
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
        $(document).ready(function() {
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

            <form class="main_form" action="cadastrarSessoes.php" method="post">
                <div>
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
                <div>
                    <label for="usuario_aluno">Aluno:</label>
                    <select name="usuario_aluno" id="usuario_aluno" disabled required>
                        <option value="">Selecione um aluno</option>
                    </select>
                </div>
                <div>
                    <label for="usuario_professor">Professor:</label>
                    <select name="usuario_professor" id="usuario_professor" disabled required>
                        <option value="">Selecione um professor</option>
                    </select>
                </div>
                <div>
                    <label for="data">Data:</label>
                    <input type="date" name="data" id="data" required>
                </div>
                <div class="form_text_area">
                    <label for="registro_sessao">Registro da Sessão:</label>
                    <textarea name="registro_sessao" id="registro_sessao" required></textarea>
                </div>
                <div class="form_text_area">
                    <label for="anotacoes">Anotações:</label>
                    <textarea name="anotacoes" id="anotacoes"></textarea>
                </div>
                <button class="botao_azul text_button" type="submit" name="botao" value="Cadastrar">Cadastrar</button>
            </form>
        </main>
    </div>
</body>
</html>