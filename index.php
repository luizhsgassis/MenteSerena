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
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="estilo.css">
    <title>MenteSerena</title>
</head>
<body>
    <section class="login_section">
        <div class="login_container">
            <div class="login_logo">
                <h3>MenteSerena</h3>
            </div>
            <form class="login_form" action="" method="post">
                <div>
                    <label for="login">Login:</label>
                    <input type="text" name="login" id="login" required>
                </div>
                <div>
                    <label for="senha">Senha:</label>
                    <input type="password" name="senha" id="senha" required>
                </div>
                <button class="login_button" type="submit" name="botao" value="Entrar">Entrar</button>
            </form>
        </div>
    </section>
</body>
</html>