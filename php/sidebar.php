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
  $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'pacientes'; // Define 'pacientes' como padrão

  // Função para gerar itens da sidebar
  function gerarItemSidebar($paginaAtual, $tipo, $tipoPagina, $url, $imgSrc, $texto) {
    $activeClass = ($paginaAtual == 'mainContent.php' && $tipo == $tipoPagina) ? 'active' : '';
    return '
      <li class="' . $activeClass . '">
        <a href="' . $url . '">
          <img src="' . $imgSrc . '" alt="' . $texto . '">
          <span>' . $texto . '</span>
        </a>
      </li>';
  }

  // Define o HTML da sidebar
  $sidebar = '
    <div class="sidebar">
      <div class="logo"><h3>MenteSerena</h3></div>
      <ul>';

  // Adiciona os botões de acordo com o nível de acesso
  if ($nivelAcesso == ADMINISTRADOR) {
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'pacientes', '/MenteSerena-master/php/mainContent.php?tipo=pacientes', '../images/pacientes.svg', 'Pacientes');
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'documentos', '/MenteSerena-master/php/mainContent.php?tipo=documentos', '../images/documentos.svg', 'Documentos');
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'notificacoes', '/MenteSerena-master/php/mainContent.php?tipo=notificacoes', '../images/notificacoes.svg', 'Notificações');
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'alunos', '/MenteSerena-master/php/mainContent.php?tipo=alunos', '../images/alunos.svg', 'Alunos');
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'professores', '/MenteSerena-master/php/mainContent.php?tipo=professores', '../images/professores.svg', 'Professores');
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'sessoes', '/MenteSerena-master/php/mainContent.php?tipo=sessoes', '../images/sessoes.svg', 'Sessões');
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'relatorios', '/MenteSerena-master/php/mainContent.php?tipo=relatorios', '../images/relatorios.svg', 'Relatórios');
  } else if ($nivelAcesso == PROFESSOR) {
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'pacientes', '/MenteSerena-master/php/mainContent.php?tipo=pacientes', '../images/pacientes.svg', 'Pacientes');
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'documentos', '/MenteSerena-master/php/mainContent.php?tipo=documentos', '../images/documentos.svg', 'Documentos');
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'notificacoes', '/MenteSerena-master/php/mainContent.php?tipo=notificacoes', '../images/notificacoes.svg', 'Notificações');
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'alunos', '/MenteSerena-master/php/mainContent.php?tipo=alunos', '../images/alunos.svg', 'Alunos');
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'sessoes', '/MenteSerena-master/php/mainContent.php?tipo=sessoes', '../images/sessoes.svg', 'Sessões');
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'relatorios', '/MenteSerena-master/php/mainContent.php?tipo=relatorios', '../images/relatorios.svg', 'Relatórios');
  } else if ($nivelAcesso == ALUNO) {
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'pacientes', '/MenteSerena-master/php/mainContent.php?tipo=pacientes', '../images/pacientes.svg', 'Pacientes');
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'documentos', '/MenteSerena-master/php/mainContent.php?tipo=documentos', '../images/documentos.svg', 'Documentos');
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'notificacoes', '/MenteSerena-master/php/mainContent.php?tipo=notificacoes', '../images/notificacoes.svg', 'Notificações');
    $sidebar .= gerarItemSidebar($paginaAtual, $tipo, 'sessoes', '/MenteSerena-master/php/mainContent.php?tipo=sessoes', '../images/sessoes.svg', 'Sessões');
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