<?php
session_start();

// Verifica se o usuário é administrador
if ($_SESSION['UsuarioNivel'] != 'administrador') {
    header("Location: /MenteSerena-master/index.php");
    exit;
}

include('../config.php');

// Consulta para recuperar os dados dos pacientes
$query = "SELECT * FROM Usuarios WHERE tipo_usuario = 'professor'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pacientes</title>
    <link rel="stylesheet" href="../estilo.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
    <div class="body_section">
        <!-- Inclui o conteúdo de sidebar.php -->
        <?php include('sidebar.php'); ?>
        <main>
            <div class="main_title"><h2>Professores</h2></div>
            <div class="content">
                <!-- Exibe os dados dos professores -->
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Formação</th>
                            <th>Data de Contratação</th>
                            <th>Especialidade</th>
                            <th>E-Mail</th>
                            <th>Telefone</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . $row['id_usuario'] . "</td>";
                                echo "<td>" . $row['nome'] . "</td>";
                                echo "<td>" . $row['formacao'] . "</td>";
                                echo "<td>" . $row['data_contratacao'] . "</td>";
                                echo "<td>" . $row['especialidade'] . "</td>";
                                echo "<td>" . $row['email'] . "</td>";
                                echo "<td>" . $row['telefone'] . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>Nenhum professor encontrado</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="login_button">
                <a href="cadastrarProfessor.php" class="login_button">Cadastrar Professor</a>
            </div>
        </main>
    </div>
</body>
</html>