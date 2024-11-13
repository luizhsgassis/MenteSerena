<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="./estilo.css">
    <title>MenteSerena</title>
</head>
<body>
    <section>
        <div class="container">
            <h3>MenteSerena</h3>
            <form action="login.php" method="post">
                <label for="login">Login:</label>
                <input type="text" name="login" id="login" required>
                <label for="senha">Senha:</label>
                <input type="password" name="senha" id="senha" required>
                <button type="submit">Entrar</button>
            </form>
        </div>
    </section>
</body>
</html>