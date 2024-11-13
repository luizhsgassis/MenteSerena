<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./estilo.css">
    <title>MenteSerena</title>
</head>
<body>
    <section>
        <div class="login_container">
            <div class="login_logo">
                <h3>MenteSerena</h3>
            </div>
            <form class="login_form" action="login.php" method="post">
                <div>
                    <label for="login">Login:</label>
                    <input type="text" name="login" id="login" required>
                </div>
                <div>
                    <label for="senha">Senha:</label>
                    <input type="password" name="senha" id="senha" required>
                </div>
                <a href="#" type="submit">Entrar</a>
            </form>
        </div>
    </section>
</body>
</html>