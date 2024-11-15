<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /MenteSerena-master/index.php");
    exit;
}

include('../config.php');

// Obtém o tipo de conteúdo a ser exibido
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'pacientes';

// Define a consulta SQL com base no tipo de conteúdo
switch ($tipo) {
    case 'professores':
        $query = "SELECT * FROM Usuarios WHERE tipo_usuario = 'professor'";
        $titulo = "Professores";
        $colunas = ['ID', 'CPF', 'Nome', 'Data de Nascimento', 'Gênero', 'Email', 'Telefone'];
        $mapeamento = [
            'ID' => 'id_usuario',
            'CPF' => 'cpf',
            'Nome' => 'nome',
            'Data de Nascimento' => 'data_nascimento',
            'Gênero' => 'genero',
            'Email' => 'email',
            'Telefone' => 'telefone'
        ];
        break;
    case 'alunos':
        $query = "SELECT * FROM Usuarios WHERE tipo_usuario = 'aluno'";
        $titulo = "Alunos";
        $colunas = ['ID', 'CPF', 'Nome', 'Data de Nascimento', 'Gênero', 'Email', 'Telefone'];
        $mapeamento = [
            'ID' => 'id_usuario',
            'CPF' => 'cpf',
            'Nome' => 'nome',
            'Data de Nascimento' => 'data_nascimento',
            'Gênero' => 'genero',
            'Email' => 'email',
            'Telefone' => 'telefone'
        ];
        break;
    case 'documentos':
        $query = "SELECT * FROM ArquivosDigitalizados";
        $titulo = "Documentos";
        $colunas = ['ID', 'Paciente', 'Usuário', 'Sessão', 'Tipo de Documento', 'Data de Upload'];
        $mapeamento = [
            'ID' => 'id_arquivo',
            'Paciente' => 'id_paciente',
            'Usuário' => 'id_usuario',
            'Sessão' => 'id_sessao',
            'Tipo de Documento' => 'tipo_documento',
            'Data de Upload' => 'data_upload'
        ];
        break;
    case 'sessoes':
        $query = "SELECT * FROM Sessoes";
        $titulo = "Sessões";
        $colunas = ['ID', 'Prontuário', 'Paciente', 'Usuário', 'Data', 'Registro da Sessão', 'Anotações'];
        $mapeamento = [
            'ID' => 'id_sessao',
            'Prontuário' => 'id_prontuario',
            'Paciente' => 'id_paciente',
            'Usuário' => 'id_usuario',
            'Data' => 'data',
            'Registro da Sessão' => 'registro_sessao',
            'Anotações' => 'anotacoes'
        ];
        break;
    case 'notificacoes':
        $query = "SELECT * FROM Avisos";
        $titulo = "Notificações";
        $colunas = ['ID', 'Sessão', 'Usuário', 'Mensagem', 'Data', 'Status'];
        $mapeamento = [
            'ID' => 'id_aviso',
            'Sessão' => 'id_sessao',
            'Usuário' => 'id_usuario',
            'Mensagem' => 'mensagem',
            'Data' => 'data',
            'Status' => 'status'
        ];
        break;
    case 'pacientes':
    default:
        $query = "SELECT * FROM Pacientes";
        $titulo = "Pacientes";
        $colunas = ['ID', 'CPF', 'Nome', 'Data de Nascimento', 'Gênero', 'Email', 'Telefone'];
        $mapeamento = [
            'ID' => 'id_paciente',
            'CPF' => 'cpf',
            'Nome' => 'nome',
            'Data de Nascimento' => 'data_nascimento',
            'Gênero' => 'genero',
            'Email' => 'email',
            'Telefone' => 'telefone'
        ];
        break;
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo $titulo; ?></title>
    <link rel="stylesheet" href="../estilo.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/mainContent.css">
</head>
<body>
    <div class="body_section">
        <!-- Inclui o conteúdo de sidebar.php -->
        <?php include('sidebar.php'); ?>
        <main>
            <div class="main_title"><h2><?php echo $titulo; ?></h2></div>
            <div class="content">
                <!-- Envolva a tabela em um contêiner com rolagem -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <?php foreach ($colunas as $coluna) {
                                    echo "<th>$coluna</th>";
                                } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    foreach ($colunas as $coluna) {
                                        $campo = $mapeamento[$coluna];
                                        echo "<td>" . (isset($row[$campo]) ? $row[$campo] : '') . "</td>";
                                    }
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='" . count($colunas) . "'>Nenhum registro encontrado</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <!-- Botão para cadastrar novo registro, exceto para notificações -->
                <?php if ($tipo != 'notificacoes'): ?>
                <div class="button-container">
                    <a href="cadastrar<?php echo ucfirst($tipo); ?>.php" class="botao_azul">Cadastrar <?php echo ucfirst($tipo); ?></a>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>