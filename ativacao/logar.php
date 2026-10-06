<?
  $modo  = $_POST['modo'];
  $login = $_POST['modo'];
  $senha = $_POST['senha'];

  switch($modo) {
  case ('financeiro'):
      if (($login == 'financeiro' && $senha == 'simples')) {
        setcookie ("modo", $modo);
        setcookie ("login", $login);
        setcookie ("senha", $senha);
        header ("Location: listagem.php?modo=" . urlencode($modo) . "&filtrar=Filtrar&inicio=01/" . date("m/Y"));
      } else { retornarIndex(); }
      break;
//  case ('financeiro'):
//      if (($login == 'financeiro' && $senha == 'f789') ||
//          ($login == 'financeiro' && $senha == 'd123')) {
//        setcookie ("modo", $modo);
//        setcookie ("login", $login);
//        setcookie ("senha", $senha);
//        header ("Location: listagem.php?modo=" . urlencode($modo) . "&filtrar=Filtrar&inicio=01/" . date("m/Y"));
//      } else { retornarIndex(); }
//      break;
  case ('tecnico'):
      if (($login == 'tecnico' && $senha == 't123') ||
          ($login == 'tecnico' && $senha == 'd123')) {
        setcookie ("modo", $modo);
        setcookie ("login", $login);
        setcookie ("senha", $senha);
        header ("Location: listagem.php?modo=" . urlencode($modo) . "&filtrar=Filtrar&inicio=01/" . date("m/Y"));
      } else { retornarIndex(); }
      break;
  default:
      retornarIndex();
      break;
  }

  function retornarIndex () {
    header("Location: index.php");
  }
?>
