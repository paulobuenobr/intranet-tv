<html>
<head>
<title>Olé Telecom</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" content="Wed, 26 Feb 1997 08:21:57 GMT">
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<link href="css/estilos.css" rel="stylesheet" type="text/css">
<script src="js/funcoes.js" type="text/javascript"></script>
<script src="js/rds.js" type="text/javascript"></script>
<script src="js/hints.js" type="text/javascript"></script>
<script src="rds.js.php?m=placar.comercial" type="text/javascript"></script>
</head>
<body bgcolor="#222244" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<div id="rds" style="display:none"></div>
<div id="carregando" class="txtBranco11" style="position:absolute;display:none;z-index:1;top:1px;margin:1px;padding-left:4px;padding-right:4px;padding-top:2px;padding-bottom:2px;border:1px solid #FFFFFF;background-color:#CC0505">Carregando...</div>

<script type="text/javascript">
  var data = new Date();
  var atualiza_data_hora = setInterval('atualizaDataHora()', (60 - data.getSeconds()) * 1000);
  var atualiza_proxima_atualizacao = setInterval('atualizaProximaAtualizacao()', 1000);
  var proxima_atualizacao = 300;
</script>

<table id="tabela_geral" width="100%" height="100%" border="0" cellpadding="0" cellspacing="5">
  <tr>
    <td width="450" valign="top">
      <table width="100%" height="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td align="center" valign="middle" bgcolor="#252525" class="txtBranco11">
            <img src="imagens/logo_ole_tv.png" width="223" height="96" border="0" style="cursor:pointer" onclick="history.go(0)">
          </td>
        </tr>
      </table>
    </td>
    <td>
      <table width="100%" height="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td align="center" valign="middle" bgcolor="#252525" class="txtBranco11" style="font-size:36px;font-weight:bold">
            Placar Comercial
            <div style="font-size:18px;font-weight:bold">(Próxima atualização em <span id="proxima_atualizacao">5:00</span>)</div>
          </td>
        </tr>
      </table>
    </td>
    <td width="170">
      <table width="100%" height="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td align="center" valign="middle" bgcolor="#252525" class="txtBranco11" id="data_hora">
            [data_hora]
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="3" valign="top">
      <table width="100%" height="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td align="center" valign="top" bgcolor="#252525" class="txtBranco11" id="placar">
            [placar]
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="3" valign="top" height="100%">
      <table width="100%" height="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td bgcolor="#252525" class="txtBranco11" id="placar_total" valign="top">
            [placar_total]
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="3" height="22">
      <table width="100%" height="27" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td bgcolor="#252525" class="txtBranco11" style="padding:6px">
            <table width="100%" cellspacing="1" cellpadding="3" border="0">
              <tr>
                <td class="txtBranco11" align="center" style="background-color:#505050;font-size:14px;font-weight:bold" colspan="5">Premiações</td>
              </tr>
              <tr>
                <td width="15%" class="txtBranco11" align="center" style="background-color:#505050;font-size:14px;font-weight:bold">Diária</td>
                <td width="20%" class="txtBranco11" align="center" style="background-color:#505050;font-size:14px;font-weight:bold">Semanal (Sorteio)</td>
                <td width="15%" class="txtBranco11" align="center" style="background-color:#505050;font-size:14px;font-weight:bold">Mensal</td>
                <td width="20%" class="txtBranco11" align="center" style="background-color:#505050;font-size:14px;font-weight:bold">Mensal (Equipe)</td>
                <td width="30%" class="txtBranco11" align="center" style="background-color:#505050;font-size:14px;font-weight:bold">Anual (Equipe)</td>
              </tr>
              <tr>
                <td class="txtAzulEscuro11" align="center" style="background-color:#E8E8E8;font-size:14px;font-weight:bold">R$ 20,00</td>
                <td class="txtAzulEscuro11" align="center" style="background-color:#E8E8E8;font-size:14px;font-weight:bold">R$ 50,00<br>R$ 70,00 R$ 100,00</td>
                <td class="txtAzulEscuro11" align="center" style="background-color:#E8E8E8;font-size:14px;font-weight:bold">R$ 200,00</td>
                <td class="txtAzulEscuro11" align="center" style="background-color:#E8E8E8;font-size:14px;font-weight:bold">R$ 300,00<br><span style="font-size:12px">(Todos tem que bater sua meta)</span></td>
                <td class="txtAzulEscuro11" align="center" style="background-color:#E8E8E8;font-size:14px;font-weight:bold">Uma TV de Plasma 42"</td>
              </tr>
            </table>
            <div style="font-size:12px">Obs: Em caso de se atingir duas ou mais vezes a meta só será pago o valor de uma única premiação.</div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="3" height="22">
      <table width="100%" height="27" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
        <tr>
          <td align="center" valign="middle" bgcolor="#252525" class="txtBranco11" style="font-size:14px;font-weight:bold">
            Olé Telecom. Todos os direitos reservados.
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
