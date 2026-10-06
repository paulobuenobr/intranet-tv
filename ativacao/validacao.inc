<?

global $modo;

if(!(empty($modo))) {
  switch($modo) {
  case ('financeiro'):
      if (($_COOKIE["login"] != 'financeiro' OR $_COOKIE["senha"] != 'simples')) {
        setcookie ("modo");
        setcookie ("login");
        setcookie ("senha");
        retornarIndex();
      }
      break;
//  case ('financeiro'):
//      if (($_COOKIE["login"] != 'financeiro' OR $_COOKIE["senha"] != 'f789') &&
//          ($_COOKIE["login"] != 'financeiro' OR $_COOKIE["senha"] != 'd123')) {
//        setcookie ("modo");
//        setcookie ("login");
//        setcookie ("senha");
//        retornarIndex();
//      }
//      break;
  case ('tecnico'):
      if (($_COOKIE["login"] != 'tecnico' OR $_COOKIE["senha"] != 't123') &&
          ($_COOKIE["login"] != 'tecnico' OR $_COOKIE["senha"] != 'd123')) {
        setcookie ("modo");
        setcookie ("login");
        setcookie ("senha");
        retornarIndex();
      }
      break;
  default:
      retornarIndex();
      break;
  }
} else { retornarIndex(); }

  function retornarIndex () {
    header("Location: index.php");
  }
?>