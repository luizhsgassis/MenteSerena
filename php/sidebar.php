<?php
// Define os níveis de acesso
define('ADMINISTRADOR', 'administrador');
define('PROFESSOR', 'professor');
define('ALUNO', 'aluno');

// Verifica se o usuário está logado
if (isset($_SESSION['id_usuario'])) {
  // Obtém o nível de acesso do usuário
  $nivelAcesso = $_SESSION['UsuarioNivel'];

  // Define a página atual
  $paginaAtual = basename($_SERVER['PHP_SELF']);
  $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

  // Define o HTML da sidebar
  $sidebar = '
    <div class="sidebar">
      <div class="logo"><h3>MenteSerena</h3></div>
      <ul>';

  // Adiciona os botões de acordo com o nível de acesso
  if ($nivelAcesso == ADMINISTRADOR) {
    $sidebar .= '
      <li class="' . ($paginaAtual == 'mainContent.php' && $tipo == 'pacientes' ? 'active' : '') . '">
        <a href="/MenteSerena-master/php/mainContent.php?tipo=pacientes">
          <img src="../images/pacientes.svg" alt="Pacientes">
          <span>Pacientes</span>
        </a>
      </li>
      <li class="' . ($paginaAtual == 'mainContent.php' && $tipo == 'documentos' ? 'active' : '') . '">
        <a href="/MenteSerena-master/php/mainContent.php?tipo=documentos">
          <img src="../images/documentos.svg" alt="Documentos">
          <span>Documentos</span>
        </a>
      </li>
      <li class="' . ($paginaAtual == 'mainContent.php' && $tipo == 'notificacoes' ? 'active' : '') . '">
        <a href="/MenteSerena-master/php/mainContent.php?tipo=notificacoes">
          <img src="../images/notificacoes.svg" alt="Notificações">
          <span>Notificações</span>
        </a>
      </li>
      <li class="' . ($paginaAtual == 'mainContent.php' && $tipo == 'alunos' ? 'active' : '') . '">
        <a href="/MenteSerena-master/php/mainContent.php?tipo=alunos">
          <img src="../images/alunos.svg" alt="Alunos">
          <span>Alunos</span>
        </a>
      </li>
      <li class="' . ($paginaAtual == 'mainContent.php' && $tipo == 'professores' ? 'active' : '') . '">
        <a href="/MenteSerena-master/php/mainContent.php?tipo=professores">
          <img src="../images/professores.svg" alt="Professores">
          <span>Professores</span>
        </a>
      </li>
      <li class="' . ($paginaAtual == 'mainContent.php' && $tipo == 'sessoes' ? 'active' : '') . '">
        <a href="/MenteSerena-master/php/mainContent.php?tipo=sessoes">
          <img src="../images/sessoes.svg" alt="Sessões">
          <span>Sessões</span>
        </a>
      </li>
      <li class="' . ($paginaAtual == 'mainContent.php' && $tipo == 'relatorios' ? 'active' : '') . '">
        <a href="/MenteSerena-master/php/mainContent.php?tipo=relatorios">
          <img src="../images/relatorios.svg" alt="Relatórios">
          <span>Relatórios</span>
        </a>
      </li>';
  } else if ($nivelAcesso == PROFESSOR) {
    $sidebar .= '
      <li class="' . ($paginaAtual == 'mainContent.php' && $tipo == 'pacientes' ? 'active' : '') . '">
        <a href="/MenteSerena-master/php/mainContent.php?tipo=pacientes">
          <img src="../images/pacientes.svg" alt="Pacientes">
          <span>Pacientes</span>
        </a>
      </li>
      <li class="' . ($paginaAtual == 'mainContent.php' && $tipo == 'documentos' ? 'active' : '') . '">
        <a href="/MenteSerena-master/php/mainContent.php?tipo=documentos">
          <img src="../images/documentos.svg" alt="Documentos">
          <span>Documentos</span>
        </a>
      </li>
      <li class="' . ($paginaAtual == 'mainContent.php' && $tipo == 'notificacoes' ? 'active' : '') . '">
        <a href="/MenteSerena-master/php/mainContent.php?tipo=notificacoes">
          <img src="../images/notificacoes.svg" alt="Notificações">
          <span>Notificações</span>
        </a>
      </li>
      <li class="' . ($paginaAtual == 'mainContent.php' && $tipo == 'alunos' ? 'active' : '') . '">
        <a href="/MenteSerena-master/php/mainContent.php?tipo=alunos">
          <img src="../images/alunos.svg" alt="Alunos">
          <span>Alunos</span>
        </a>
      </li>
      <li class="' . ($paginaAtual == 'mainContent.php' && $tipo == 'sessoes' ? 'active' : '') . '">
        <a href="/MenteSerena-master/php/mainContent.php?tipo=sessoes">
          <img src="../images/sessoes.svg" alt="Sessões">
          <span>Sessões</span>
        </a>
      </li>';
  } else if ($nivelAcesso == ALUNO) {
    $sidebar .= '
      <li class="' . ($paginaAtual == 'mainContent.php' && $tipo == 'pacientes' ? 'active' : '') . '">
        <a href="/MenteSerena-master/php/mainContent.php?tipo=pacientes">
          <img src="../images/pacientes.svg" alt="Pacientes">
          <span>Pacientes</span>
        </a>
      </li>
      <li class="' . ($paginaAtual == 'mainContent.php' && $tipo == 'documentos' ? 'active' : '') . '">
        <a href="/MenteSerena-master/php/mainContent.php?tipo=documentos">
          <img src="../images/documentos.svg" alt="Documentos">
          <span>Documentos</span>
        </a>
      </li>
      <li class="' . ($paginaAtual == 'mainContent.php' && $tipo == 'notificacoes' ? 'active' : '') . '">
        <a href="/MenteSerena-master/php/mainContent.php?tipo=notificacoes">
          <img src="../images/notificacoes.svg" alt="Notificações">
          <span>Notificações</span>
        </a>
      </li>';
  }

  $sidebar .= '
      </ul>
      <div class="menu">
        <div class="user">
          <img src="../images/user.svg" alt="Usuário">
          <div class="user_name">
            <span class="placeholder">' . $_SESSION['nome_usuario'] . '</span>
            <span class="caption">' . $_SESSION['UsuarioNivel'] . '</span>
          </div>
        </div>
        <div class="configuracoes">
          <a href="#">
            <img src="../images/setting.svg" alt="Configurações">
            <span>Configurações</span>
          </a>
          <a href="logout.php">
            <img src="../images/exit.svg" alt="Sair">
            <span>Sair</span>
          </a>
        </div>
      </div>
    </div>';

  // Exibe a sidebar
  echo $sidebar;
} else {
  // Redireciona para a página de login se o usuário não estiver logado
  header('Location: /MenteSerena-master/index.php');
  exit();
}
?>