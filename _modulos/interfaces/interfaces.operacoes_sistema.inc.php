<html>
<head>
<title>Olé TV</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" content="Wed, 26 Feb 1997 08:21:57 GMT">
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<link href="css/estilos.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/formatacao.js?updated=20260629110500"></script>
<script type="text/javascript" src="js/validacao.js?updated=20260629110500"></script>
<script type="text/javascript" src="js/funcoes.js"></script>
<script type="text/javascript" src="js/rds.js"></script>
<script type="text/javascript" src="js/hints.js"></script>
<script type="text/javascript" src="rds.js.php?m=central_telefonica"></script>
<script type="text/javascript" src="rds.js.php?m=operacoes_sistema"></script>
</head>
<body bgcolor="#222222" onresize="redimensionaDivs()" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onbeforeunload="atualizaOperacao()">
<div id="rds" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;"></div>

<script type="text/javascript">
  function redimensionaDivs() {
    if (document.getElementById('historico_conteudo')) document.getElementById('historico_conteudo').style.height = document.body.clientHeight - 391;
    if (document.getElementById('script_conteudo')) document.getElementById('script_conteudo').style.height = document.body.clientHeight - 430;
  }
</script>

<table width="100%" height="100%" align="center" border="0" cellpadding="0" cellspacing="5">
  <tr>
    <td width="55%" height="100">
      <table width="100%" height="110" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td align="center" valign="middle" bgcolor="#252525">[cabecalho]</td>
        </tr>
      </table>
    </td>
    <td width="45%" height="100">
      <table width="100%" height="110" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td align="center" valign="middle" bgcolor="#252525" class="txtBranco11" id="operacao">[operacao]</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
  	<td height="200">
	    <table width="100%" height="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td align="center" valign="middle" bgcolor="#252525" class="txtBranco11" id="contato">[contato]</td>
        </tr>
      </table>
    </td>
  	<td>
	    <table width="100%" height="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td align="center" valign="middle" bgcolor="#252525" class="txtBranco11" id="registro">[registro]</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
  	<td>
	    <table width="100%" height="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td align="center" valign="middle" bgcolor="#252525" class="txtBranco11" id="historico">[historico]</td>
        </tr>
      </table>
    </td>
  	<td>
	    <table width="100%" height="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td align="center" valign="middle" bgcolor="#252525" class="txtBranco11" id="script">[script]</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr height="25">
  	<td colspan="2">
      <table width="100%" height="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td align="center" valign="middle" bgcolor="#252525">[rodape]</td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<script type="text/javascript">
  inicializaOperacaoSistema();
</script>

</body>
</html>
