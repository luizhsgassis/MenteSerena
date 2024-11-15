<?php
session_start();

include('../config.php');

// Verifica se o usuário é administrador ou professor
if ($_SESSION['UsuarioNivel'] == 'aluno') {
    header("Location: /MenteSerena-master/index.php");
    exit;
}

$erro_cadastro = '';
$sucesso_cadastro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cpf = trim($_POST["cpf"]);
    $nome = trim($_POST["nome"]);
    $dataNascimento = trim($_POST["data_nascimento"]);
    $genero = trim($_POST["genero"]);
    $dataContratacao = trim($_POST["data_contratacao"]);
    $formacao = trim($_POST["formacao"]);
    $especialidade = trim($_POST["especialidade"]);
    $email = trim($_POST["email"]);
    $telefone = trim($_POST["telefone"]);

    // Validações
    if (empty($cpf) || strlen($cpf) != 11 || !ctype_digit($cpf)) {
        $erro_cadastro = "CPF inválido. Deve conter 11 dígitos.";
    } elseif (empty($nome)) {
        $erro_cadastro = "Por favor, preencha o nome.";
    } elseif (empty($dataNascimento)) {
        $erro_cadastro = "Por favor, preencha a data de nascimento.";
    } elseif (empty($genero)) {
        $erro_cadastro = "Por favor, selecione o gênero.";
    } elseif (empty($dataContratacao)) {
        $erro_cadastro = "Por favor, preencha a data de contratação.";
    } elseif (empty($formacao)) {
        $erro_cadastro = "Por favor, preencha a formação.";
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro_cadastro = "E-mail inválido.";
    } elseif (empty($telefone) || strlen($telefone) != 11 || !ctype_digit($telefone)) {
        $erro_cadastro = "Telefone inválido. Deve conter 11 dígitos.";
    } else {
        $loginPlaceholder1 = substr($nome, 0, 3);
        $loginPlaceholder2 = substr($telefone, -3);
        $loginTemporario = $loginPlaceholder1 . $loginPlaceholder2;

        $senhaPlaceholder1 = substr($cpf, 0, 3);
        $senhaPlaceholder2 = substr($dataNascimento, -2);
        $senhaTemporaria = $senhaPlaceholder1 . $senhaPlaceholder2;
        $senhaHash = password_hash($senhaTemporaria, PASSWORD_DEFAULT);

        // Trata o campo especialidade como NULL se estiver vazio
        $especialidade = !empty($especialidade) ? $especialidade : NULL;

        $query = "INSERT INTO Usuarios (cpf, nome, data_nascimento, genero, data_contratacao, formacao, especialidade, email, telefone, login, senha, tipo_usuario, ativo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aluno', 1)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssssssss", $cpf, $nome, $dataNascimento, $genero, $dataContratacao, $formacao, $especialidade, $email, $telefone, $loginTemporario, $senhaHash);
        
        if (mysqli_stmt_execute($stmt)) {
            $sucesso_cadastro = "Aluno cadastrado com sucesso!";
        } else {
            $erro_cadastro = "Erro ao cadastrar aluno: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastrar Aluno</title>
  <link rel="stylesheet" href="../estilo.css">
  <link rel="stylesheet" href="../css/sidebar.css">
  <link rel="stylesheet" href="../css/mainContent.css">
</head>
<body>
  <div class="body_section">
    <!-- Inclui o conteúdo de sidebar.php -->
    <?php include('sidebar.php'); ?>
    <main>
      <div class="main_title"><h2>Cadastrar Aluno</h2></div>
      <div class="content">
        <?php if (!empty($erro_cadastro)): ?>
          <div class="error"><?php echo $erro_cadastro; ?></div>
        <?php endif; ?>
        <?php if (!empty($sucesso_cadastro)): ?>
          <div class="success"><?php echo $sucesso_cadastro; ?></div>
        <?php endif; ?>
        <form class="main_form" action="cadastrarAlunos.php" method="post">
          <div>
            <label for="cpf">CPF:</label>
            <input type="text" name="cpf" id="cpf" maxlength="11" required>
          </div>
          <div>
            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" required>
          </div>
          <div>
            <label for="data_nascimento">Data de Nascimento:</label>
            <input type="date" name="data_nascimento" id="data_nascimento" required>
          </div>
          <div class="form_group">
            <label for="genero">Gênero:</label>
            <select name="genero" id="genero" required>
              <option value="">Selecione</option>
              <option value="Masculino">Masculino</option>
              <option value="Feminino">Feminino</option>
              <option value="Outro">Outro</option>
            </select>
          </div>
          <div>
            <label for="data_contratacao">Data de Contratação:</label>
            <input type="date" name="data_contratacao" id="data_contratacao" required>
          </div>
          <div>
            <label for="formacao">Formação:</label>
            <input type="text" name="formacao" id="formacao" required>
          </div>
          <div>
            <label for="especialidade">Especialidade:</label>
            <input type="text" name="especialidade" id="especialidade">
          </div>
          <div>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
          </div>
          <div>
            <label for="telefone">Telefone:</label>
            <input type="text" name="telefone" id="telefone" maxlength="11" required>
          </div>
          <button class="botao_azul text_button" type="submit" name="botao" value="Cadastrar">Cadastrar</button>
        </form>
      </div>
    </main>
  </div>
</body>
</html>