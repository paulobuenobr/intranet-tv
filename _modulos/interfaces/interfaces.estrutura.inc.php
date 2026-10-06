<html>
<head>
<title>Olé TV</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" content="Wed, 26 Feb 1997 08:21:57 GMT">
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<link href="css/estilos.css?update=20141105110500" rel="stylesheet" type="text/css">
<link href="css/menu.css?update=20141105110500" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/hints.js"></script>
<script type="text/javascript" src="js/rds.js"></script>
<script type="text/javascript" src="js/formatacao.js?updated=20260629110500"></script>
<script type="text/javascript" src="js/validacao.js?updated=20260629110500"></script>
<script type="text/javascript" src="js/funcoes.js"></script>
<script type="text/javascript" src="js/menu.js"></script>
<script type="text/javascript" src="js/menu_tpl.js"></script>
<script type="text/javascript" src="js/jquery-1.5.2.min.js"></script>
<script type="text/javascript" src="rds.js.php?m=central_telefonica"></script>
</head>
<body bgcolor="#222222" onblur="hintHide()">
<div id="rds" style="display:none"></div>
<table width="100%" border="0" align="center" cellpadding="1" cellspacing="0">
  <tr>
    <td style="padding:10px">
      <table width="760" border="0" align="center" cellpadding="0" cellspacing="5">
        <tr>
			<td width="93" rowspan="2" style="background-color:#252525;padding-right:5px;padding-left:5px;border:1px solid #FFFFFF">
				<a href="/"><img src="imagens/logo_ole_tv.png" width="93" height="40" /></a>
			</td>
        	<td>
      	    <table width="100%" border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td style="width:30px">
                  <script type="text/javascript">
                    var menu_ramal = new menu([[painel_ramais]], RAMAL_POS);
                  </script>
                </td>
                <td style="width:5px"></td>
                <td align="center" style="background-color:#252525;padding-right:5px;padding-left:5px;border:1px solid #FFFFFF">
                  <table width="100%" height="20" border="0" cellspacing="0" cellpadding="0" bgcolor="#252525">
                    <tr>
                      <td align="left" id="painel_mensagem" class="txtBranco11">[painel_mensagem]</td>
                    </tr>
                  </table>
                </td>
                <td style="width:5px"></td>
                <td style="width:77px;background-color:#252525;border:1px solid #FFFFFF">
                  <table width="100%" height="20" border="0" cellspacing="0" cellpadding="0" bgcolor="#252525">
                    <tr>
                      <td align="center" id="painel_datahora" class="txtBranco11">[painel_datahora]</td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
        	<td colspan="2">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td bgcolor="#252525">
                  <script type="text/javascript">
                    var menu = new menu([[menu_itens]], MENU_POS);
                  </script>
                </td>
                <td width="5"><div style="width:5px"></div>
                </td>
                <td width="79">
                  <table width="79" border="0" cellpadding="1" cellspacing="1" bgcolor="#FFFFFF">
                    <tr>
                      <td bgcolor="#252525" height="20" class="txtBranco11" style="padding-left:2px;padding-right:2px">
                        [busca_cliente]
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr style="display:[exibe_corpo]">
        	<td colspan="2">
      	    <table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
              <tr>
                <td align="center" bgcolor="#252525">
                  <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#252525">
                    <tr>
                      <td align="center" valign="top" style="padding:2px">[corpo]<td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
        	<td colspan="2">
            <table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
              <tr>
                <td height="25" bgcolor="#252525" align="center">[rodape]</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<script type="text/javascript">
  atualizaPainel();
  if (document.getElementById('ramal')) {
    document.getElementById('ramal').focus();
  } else if (document.getElementById('id')) {
    document.getElementById('id').focus();
  }
</script>
</body>
</html>
