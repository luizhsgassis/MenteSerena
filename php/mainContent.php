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

// Verifica o nível de acesso do usuário
$nivelAcesso = $_SESSION['UsuarioNivel'];
$idUsuarioLogado = $_SESSION['id_usuario'];

// Definir um valor padrão para a variável $query
$query = ''; // Inicializando a variável $query

switch ($tipo) {
    case 'professores':
        // Verifica se o usuário é administrador
        if ($nivelAcesso != 'administrador') {
            header("Location: /MenteSerena-master/php/logout.php");
            exit;
        }
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
        $detalheUrl = 'acessarProfessores.php';
        break;
    case 'alunos':
        // Verifica se o usuário é administrador ou professor
        if ($nivelAcesso == 'aluno') {
            header("Location: /MenteSerena-master/php/logout.php");
            exit;
        } elseif ($nivelAcesso == 'professor') {
            $query = "SELECT Usuarios.id_usuario, Usuarios.nome, Usuarios.data_nascimento, Usuarios.data_contratacao, Usuarios.telefone, Professores.id_professor 
                        FROM AssociacaoAlunosProfessores
                        JOIN Usuarios ON AssociacaoAlunosProfessores.id_aluno = Usuarios.id_usuario
                        JOIN Professores ON AssociacaoAlunosProfessores.id_professor = Professores.id_professor
                        WHERE AssociacaoAlunosProfessores.id_professor = (
                        SELECT id_professor 
                        FROM Professores 
                        WHERE id_usuario = $idUsuarioLogado)";
        
            // Prepare the query
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            // Check if the result is not empty
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    // Process each row (e.g., display the data)
                }
            } else {
                echo "Nenhum aluno encontrado para este professor.";
            }
            
            mysqli_stmt_close($stmt);
            $titulo = "Alunos";
            $colunas = ['ID', 'Nome', 'Data de Nascimento', 'Data de Contratação', 'Telefone'];
            $mapeamento = [
                'ID' => 'id_usuario',
                'Nome' => 'nome',
                'Data de Nascimento' => 'data_nascimento',
                'Data de Contratação' => 'data_contratacao',
                'Telefone' => 'telefone'
            ];
            $detalheUrl = 'acessarAlunos.php';
            break;
        } else {
            $query = "SELECT * FROM Usuarios WHERE tipo_usuario = 'aluno'";
            $titulo = "Alunos";
            $colunas = ['ID', 'Nome', 'Data de Nascimento', 'Data de Contratação', 'Telefone'];
            $mapeamento = [
                'ID' => 'id_usuario',
                'Nome' => 'nome',
                'Data de Nascimento' => 'data_nascimento',
                'Data de Contratação' => 'data_contratacao',
                'Telefone' => 'telefone'
            ];
            $detalheUrl = 'acessarAlunos.php';
            break;
        }
    case 'documentos':
        if ($nivelAcesso == 'aluno') {
            $query = "SELECT ad.id_arquivo, ad.id_paciente, ad.id_usuario, ad.id_sessao, ad.tipo_documento, ad.data_upload, p.nome AS nome_paciente, u.nome AS nome_usuario 
                        FROM ArquivosDigitalizados ad
                        LEFT JOIN Pacientes p ON ad.id_paciente = p.id_paciente
                        LEFT JOIN Usuarios u ON ad.id_usuario = u.id_usuario
                        WHERE ad.id_usuario = $idUsuarioLogado";
        } else {
            $query = "SELECT ad.id_arquivo, ad.id_paciente, ad.id_usuario, ad.id_sessao, ad.tipo_documento, ad.data_upload, p.nome AS nome_paciente, u.nome AS nome_usuario 
                        FROM ArquivosDigitalizados ad
                        LEFT JOIN Pacientes p ON ad.id_paciente = p.id_paciente
                        LEFT JOIN Usuarios u ON ad.id_usuario = u.id_usuario";
        }
        $titulo = "Documentos";
        $colunas = ['ID', 'Paciente', 'Usuário', 'Sessão', 'Tipo de Documento', 'Data de Upload'];
        $mapeamento = [
            'ID' => 'id_arquivo',
            'Paciente' => 'nome_paciente',
            'Usuário' => 'nome_usuario',
            'Sessão' => 'id_sessao',
            'Tipo de Documento' => 'tipo_documento',
            'Data de Upload' => 'data_upload'
        ];
        $detalheUrl = 'acessarDocumentos.php';
        break;
    case 'sessoes':
        if ($nivelAcesso == 'aluno') {
            $query = "SELECT s.id_sessao, s.id_prontuario, s.id_paciente, s.id_usuario, s.data, s.registro_sessao, s.anotacoes, p.consideracoes_finais, pa.nome AS nome_paciente, u.nome AS nome_usuario 
                      FROM Sessoes s 
                      LEFT JOIN Prontuarios p ON s.id_prontuario = p.id_prontuario
                      LEFT JOIN Pacientes pa ON s.id_paciente = pa.id_paciente
                      LEFT JOIN Usuarios u ON s.id_usuario = u.id_usuario
                      WHERE s.id_usuario = $idUsuarioLogado";
        } else {
            $query = "SELECT s.id_sessao, s.id_prontuario, s.id_paciente, s.id_usuario, s.data, s.registro_sessao, s.anotacoes, p.consideracoes_finais, pa.nome AS nome_paciente, u.nome AS nome_usuario 
                      FROM Sessoes s 
                      LEFT JOIN Prontuarios p ON s.id_prontuario = p.id_prontuario
                      LEFT JOIN Pacientes pa ON s.id_paciente = pa.id_paciente
                      LEFT JOIN Usuarios u ON s.id_usuario = u.id_usuario";
        }
        $titulo = "Sessões";
        $colunas = ['ID', 'Paciente', 'Usuário', 'Data', 'Registro da Sessão', 'Anotações'];
        $mapeamento = [
            'ID' => 'id_sessao',
            
            'Paciente' => 'nome_paciente',
            'Usuário' => 'nome_usuario',
            'Data' => 'data',
            'Registro da Sessão' => 'registro_sessao',
            'Anotações' => 'anotacoes'
        ];
        $detalheUrl = 'acessarSessoes.php';
        break;
    case 'notificacoes':
        if ($nivelAcesso == 'aluno') {
            $query = "SELECT a.id_aviso, a.id_sessao, a.id_usuario, a.mensagem, s.data AS data_sessao, a.status, u.nome AS nome_usuario
                      FROM Avisos a
                      LEFT JOIN Usuarios u ON a.id_usuario = u.id_usuario
                      LEFT JOIN Sessoes s ON a.id_sessao = s.id_sessao
                      WHERE a.id_usuario = $idUsuarioLogado AND a.status = 'pendente' AND s.data <= DATE_SUB(NOW(), INTERVAL 48 HOUR)";
        } else {
            $query = "SELECT a.id_aviso, a.id_sessao, a.id_usuario, a.mensagem, s.data AS data_sessao, a.status, u.nome AS nome_usuario
                      FROM Avisos a
                      LEFT JOIN Usuarios u ON a.id_usuario = u.id_usuario
                      LEFT JOIN Sessoes s ON a.id_sessao = s.id_sessao
                      WHERE a.status = 'pendente' AND s.data <= DATE_SUB(NOW(), INTERVAL 48 HOUR)";
        }
        $titulo = "Notificações";
        $colunas = ['ID', 'Sessão', 'Usuário', 'Mensagem', 'Data da Sessão', 'Status'];
        $mapeamento = [
            'ID' => 'id_aviso',
            'Sessão' => 'id_sessao',
            'Usuário' => 'nome_usuario',
            'Mensagem' => 'mensagem',
            'Data da Sessão' => 'data_sessao',
            'Status' => 'status'
        ];
        $detalheUrl = 'acessarSessoes.php'; // Redireciona para a sessão específica
        break;
    case 'pacientes':
        if ($nivelAcesso == 'aluno') {
            $query = "SELECT p.id_paciente, p.cpf, p.nome, p.data_nascimento, p.genero, p.email, p.telefone
                        FROM Pacientes p
                        INNER JOIN AssociacaoPacientesAlunos apa ON p.id_paciente = apa.id_paciente
                        WHERE apa.id_aluno = $idUsuarioLogado";
        } else {
            $query = "SELECT * FROM Pacientes";
        }
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
        $detalheUrl = 'acessarPacientes.php';
        break;
    default:
        // Valor default para evitar que a variável $query seja indefinida
        $query = "SELECT * FROM Usuarios";
        break;
}

// Verifica se a variável $query não está vazia
if (!empty($query)) {
    $result = mysqli_query($conn, $query);
} else {
    // Caso $query esteja vazia, exibe mensagem de erro
    echo "Erro na consulta.";
}
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
                                    $rowUrl = ($tipo == 'notificacoes') ? "acessarSessoes.php?id=" . $row['id_sessao'] : "$detalheUrl?id=" . $row[$mapeamento['ID']];
                                    echo "<tr onclick=\"window.location.href='$rowUrl'\">";
                                    foreach ($colunas as $coluna) {
                                        $campo = $mapeamento[$coluna];
                                        if ($coluna == 'Data de Nascimento' && isset($row[$campo])) {
                                            $dataFormatada = date('d/m/Y', strtotime($row[$campo]));
                                            echo "<td>$dataFormatada</td>";
                                        } elseif ($coluna == 'Data da Sessão' && isset($row[$campo])) {
                                            $dataFormatada = date('d/m/Y', strtotime($row[$campo]));
                                            echo "<td>$dataFormatada</td>";
                                        } elseif ($coluna == 'Data' && isset($row[$campo])) {
                                            $dataFormatada = date('d/m/Y', strtotime($row[$campo]));
                                            echo "<td>$dataFormatada</td>";
                                        } else {
                                            echo "<td>" . (isset($row[$campo]) ? $row[$campo] : '') . "</td>";
                                        }
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
                <!-- Botão para cadastrar novo registro -->
                <?php if ($tipo != 'notificacoes'): ?>
                <div class="button-container">
                    <?php
                    switch ($tipo) {
                        case 'pacientes':
                            if ($nivelAcesso != 'administrador') {
                                echo '<a href="cadastrarPacientes.php" class="botao_azul text_button">Cadastrar Paciente</a>';
                            }
                            break;
                        case 'alunos':
                            if ($nivelAcesso != 'administrador') {
                                echo '<a href="cadastrarAlunos.php" class="botao_azul text_button">Cadastrar Aluno</a>';
                            }
                            break;
                        case 'professores':
                            echo '<a href="cadastrarProfessores.php" class="botao_azul text_button">Cadastrar Professor</a>';
                            break;
                        case 'documentos':
                            if ($nivelAcesso != 'administrador') {
                                echo '<a href="cadastrarDocumentos.php" class="botao_azul text_button">Cadastrar Documento</a>';
                            }
                            break;
                        case 'sessoes':
                            if ($nivelAcesso != 'administrador') {
                                echo '<a href="cadastrarSessoes.php" class="botao_azul text_button">Cadastrar Sessão</a>';
                            }
                            break;
                    }
                    ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>