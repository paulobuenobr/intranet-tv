<?

require_once("mailing.class.inc.php");
require_once("mailing.filtro.class.inc.php");
require_once("mailing.cache.class.inc.php");
require_once("mailing.http.class.inc.php");
require_once("mailing.listasdaqui.class.inc.php");

$mailing_listasdaqui = new mailing_listasdaqui();

if (!$_GET) {

  $imagem1 = $mailing_listasdaqui->imagemSegurancaEtapa1();
  $imagem2 = $mailing_listasdaqui->imagemSegurancaEtapa1();
  
?>
<html>
<head>
<title>Listasdaqui.com.br</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" content="Wed, 26 Feb 1997 08:21:57 GMT">
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
</head>
<body>
  <form>
    <table border="1" align="center" cellpadding="3">
      <tr>
        <td>Texto</td>
        <td>Imagem</td>
      </tr>
      <tr>
        <td><input type="text" id="texto1" name="texto1" size="30"></td>
        <td><input type="hidden" name="imagem1" value="<?=$imagem1?>"><img src="listasdaqui/<?=$imagem1?>.jpg?<?=md5(time().$imagem1)?>"></td>
      </tr>
      <tr>
        <td><input type="text" id="texto2" name="texto2" size="30"></td>
        <td><input type="hidden" name="imagem2" value="<?=$imagem2?>"><img src="listasdaqui/<?=$imagem2?>.jpg?<?=md5(time().$imagem2)?>"></td>
      </tr>
      <tr>
        <td colspan="2" align="center"><input type="submit" name="continuar" value="Continuar"></td>
      </tr>
    </table>
    <script type="text/javascript">
      document.getElementById('texto1').focus();
    </script>
  </form>
</body>
</html>
<?
  
} else {
  
  if ($_GET["texto1"]) $mailing_listasdaqui->imagemSegurancaEpata2($_GET["imagem1"], $_GET["texto1"]);
  if ($_GET["texto2"]) $mailing_listasdaqui->imagemSegurancaEpata2($_GET["imagem2"], $_GET["texto2"]);

  header("Location: listasdaqui.php");
  
}

?>
