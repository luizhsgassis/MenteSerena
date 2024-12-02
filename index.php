<?php
include('config.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['botao']) && $_POST['botao'] == "Entrar") {
  $login = $_POST['login'];
  $senha = $_POST['senha'];

  $query = "SELECT * FROM Usuarios WHERE login = ?";
  $stmt = mysqli_prepare($conn, $query);
  mysqli_stmt_bind_param($stmt, "s", $login);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if ($coluna = mysqli_fetch_array($result)) {
    if (password_verify($senha, $coluna['senha'])) {
      $_SESSION["id_usuario"] = $coluna["id_usuario"];
      $_SESSION["nome_usuario"] = $coluna["nome"];
      $_SESSION["UsuarioNivel"] = $coluna["tipo_usuario"];

      header("Location: /MenteSerena-master/php/mainContent.php?tipo=pacientes");
      exit;
    } else {
      echo "Login ou senha incorretos.";
    }
  } else {
    echo "Login ou senha incorretos.";
  }
}

$queryLastAdded = "SELECT * FROM Usuarios ORDER BY id_usuario DESC LIMIT 1";
$stmtLA = mysqli_prepare($conn, $queryLastAdded);
mysqli_stmt_execute($stmtLA);
$resultLA = mysqli_stmt_get_result($stmtLA);

$linha = mysqli_fetch_assoc($resultLA);
$nomeLA = $linha['nome'];
$telefoneLA = $linha['telefone'];
$cpfLA = $linha['cpf'];
$dataLA = $linha['data_nascimento'];

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="estilo.css">
    <style>
        .tooltip {
            position: absolute;
            background-color: #333;
            color: #fff;
            padding: 5px;
            border-radius: 5px;
            display: none;
            z-index: 1;
        }

        .input-container {
            position: relative;
        }

        .input-container:hover .tooltip {
            display: block;
        }
    </style>
    <title>MenteSerena</title>
</head>
<body>
  <p align="center" style="color:red;">Nome: <?php echo $nomeLA . " ( " . substr($nomeLA, 0, 3) . " ) "; ?></p>
  <p align="center" style="color:red;">Telefone: <?php echo $telefoneLA . " ( " . substr($telefoneLA, -3) . " ) "; ?></p>
  <p align="center" style="color:red;">CPF: <?php echo $cpfLA . " ( " . substr($cpfLA, 0, 3) . " ) "; ?></p>
  <p align="center" style="color:red;">Data de Nascimento: <?php echo $dataLA . " ( " . substr($dataLA, -2) . " ) "; ?></p>
    <section class="login_section">
        <div class="login_container">
            <div class="login_logo">
                <h3>MenteSerena</h3>
            </div>
            <form class="login_form" action="" method="post">
                <div class="input-container">
                    <label for="login">Login:</label>
                    <input type="text" name="login" id="login" required>
                    <div class="tooltip">Placeholder do Login: 3 primeiras letras do nome + 3 últimos números do telefone.</div>
                </div>
                <div class="input-container">
                    <label for="senha">Senha:</label>
                    <input type="text" name="senha" id="senha" required>
                    <div class="tooltip">Placeholder da Senha: 3 primeiros dígitos do cpf + dia do nascimento.</div>
                </div>
                <button class="login_button" type="submit" name="botao" value="Entrar">Entrar</button>
            </form>
        </div>
    </section>
    <p align="center" style="color:red;"><strong>Modo de Apresentação:</strong> Este branch do MenteSerena traz alterações específicas com o objetivo de tornar a apresentação mais dinâmica e didática.</p>
    <br>
    <p align="center"><strong>Membros da Equipe:</strong></p>
    <p align="center"><strong>Luiz Henrique Schmidt Gonçalves de Assis (RA:172222862)</strong></p>
    <p align="center"><strong>João Gabriel Breve (RA:172317201)</strong></p>
    <p align="center"><strong>Ana Julia Bernardo Lazaro (RA:172211672)</strong></p>
</body>
</html>