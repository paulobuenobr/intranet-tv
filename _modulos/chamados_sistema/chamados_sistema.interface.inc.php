<html>
<head>
<title>Olé Telecom</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" content="Wed, 26 Feb 1997 08:21:57 GMT">
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<link href="css/estilos.css?atualizacao=[data_hora_alteracao]" rel="stylesheet" type="text/css">
<link href="css/menu.css?atualizacao=[data_hora_alteracao]" rel="stylesheet" type="text/css">
<script src="js/formatacao.js?atualizacao=[data_hora_alteracao]" type="text/javascript"></script>
<script src="js/validacao.js?atualizacao=[data_hora_alteracao]" type="text/javascript"></script>
<script src="js/funcoes.js?atualizacao=[data_hora_alteracao]" type="text/javascript"></script>
<script src="js/rds.js?atualizacao=[data_hora_alteracao]" type="text/javascript"></script>
<script src="js/hints.js?atualizacao=[data_hora_alteracao]" type="text/javascript"></script>
<script src="js/menu.js?atualizacao=[data_hora_alteracao]" type="text/javascript"></script>
<script src="js/menu_tpl.js?atualizacao=[data_hora_alteracao]" type="text/javascript"></script>
<script src="rds.js.php?m=clientes.contatos" type="text/javascript"></script>
<script src="rds.js.php?m=chamados_sistema" type="text/javascript"></script>
<script src="rds.js.php?m=pesquisas_aplicacoes" type="text/javascript"></script>
<script src="rds.js.php?m=central_telefonica" type="text/javascript"></script>
<script src="rds.js.php?m=tarefas" type="text/javascript"></script>
<script src="_modulos/chamados_sistema/chamados_sistema.js?atualizacao=[data_hora_alteracao]" type="text/javascript"></script>
</head>
<body bgcolor="#222244" style="overflow:hidden" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onkeydown="return pressionaTecla(event)" onresize="redimensionaDivs()" onbeforeunload="if (!trataChamadoAlterado()) atualizaUltimaInstancia()">
<div id="rds" style="display:none"></div>
<div id="carregando" class="txtBranco11" style="position:absolute;display:none;z-index:1;top:1px;margin:1px;padding-left:4px;padding-right:4px;padding-top:2px;padding-bottom:2px;border:1px solid #FFFFFF;background-color:#CC0505">Carregando...</div>
<div id="bloqueio" class="txtBranco11" style="position:absolute;display:none;z-index:2;top:0px;left:0px;cursor:wait"></div>

<script type="text/javascript">
  var data_hora_alteracao = '[data_hora_alteracao]';
  var atualizando = false;
  var instancia = '[instancia]';
  var cliente_atual = '[cliente_atual]';
  var cliente_condominio_atual = '[cliente_condominio_atual]';
  var condominio_atual = '[condominio_atual]';
  var chamado_atual = '[chamado_atual]';
  var chamado_acao_atual = '[chamado_acao_atual]';
  var chamado_etapa_atual = '[chamado_etapa_atual]';
  var aba_historico_atual = '[aba_historico_atual]';
  var aba_busca_atual = '[aba_busca_atual]';
  var aba_resumo_atual = '[aba_resumo_atual]';
  var chamado_alterado = false;
  var painel_historico = [painel_historico];
  var pagina_busca_atual = [pagina_busca_atual];
</script>

<table id="tabela_geral" width="100%" height="100%" border="0" cellpadding="0" cellspacing="5">
  <tr>
    <td width="490" valign="top">
      <table width="100%" border="0" cellpadding="0" cellspacing="0" id="coluna_1">
        <tr>
          <td valign="top">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" id="cabecalho">
              <!--
              <tr>
                <td height="71" width="263">
                  <table width="100%" height="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
                    <tr>
                      <td align="center" valign="middle" bgcolor="#252525" class="txtBranco11">[cabecalho]</td>
                    </tr>
                  </table>
                </td>
                <td width="5"></td>
                <td width="222" id="painel" colspan="5">
                  [painel]
                </td>
              </tr>
              <tr>
                <td height="5"></td>
              </tr>
              -->
              <tr>
                <td height="22">
                  <table width="100%" height="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
                    <tr>
                      <td align="center" bgcolor="#252525" class="txtBranco11" valign="middle">
                        [usuario]
                      </td>
                    </tr>
                  </table>
                </td>
                <td width="5" id="alerta_agendamento_visivel_1" [alerta_agendamento_visivel]></td> 
                <td width="22" id="alerta_agendamento_visivel_2" [alerta_agendamento_visivel]>
                  <table width="100%" height="22" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
                    <tr bgcolor="#e20000" class="txtBranco11">
                	  	<td align="center"><img src="imagens/aguardando_retorno.gif" width="18" height="18" onclick="mostrarAguardandoRetorno()" style="cursor:pointer"></td>
                    </tr>
                  </table>
                </td>
                <td width="5"></td>
                <td height="22" width="113">
                  <table width="100%" height="22" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
                    <tr bgcolor="#252525" class="txtBranco11">
                	  	<td id="navegacao">[navegacao]</td>
                    </tr>
                  </table>
                </td>
                <td width="5"></td>
                <td height="22" width="70">
                  <table width="100%" height="22" border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
                    <tr bgcolor="#252525" class="txtBranco11">
                	  	<td width="70">
												<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
													<tr align="center" bgcolor="#E8E8E8">
												    <td style="border:1px solid #252525" class="txtAzulEscuro11">
															<table width="70" height="100%" cellpadding="0" cellspacing="0" border="0">
															  <tr>
															  	<td class="txtAzulEscuro11" id="data_hora" align="left" style="text-align:center;">[data_hora]</td>
															  </tr>
															</table>
												    </td>
											    </tr>
										    </table>
                	  	</td>
                    </tr>
                  </table>
                </td>
[painel_ramais]
              </tr>
[painel_agente]
            </table>
          </td>
        </tr>
        <tr>
          <td height="5"></td>
        </tr>
        <tr>
          <td height="100%" id="coluna_busca" valign="top">
            <table id="tabela_busca" width="100%" height="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
              <tr>
                <td align="center" bgcolor="#252525" class="txtBranco11" valign="top">
                  [busca]
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td height="5"></td>
        </tr>
        <tr>
          <td height="100%" id="coluna_historico">
            <table width="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
              <tr>
                <td align="center" bgcolor="#252525" class="txtBranco11" valign="middle">
                  [historico]
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
    <td valign="top">
      <table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0" id="coluna_2">
        <tr>
          <td height="20">
	          <table width="100%" border="0" cellpadding="0" cellspacing="0" id="tabela_botoes">
	            <tr>
			          <td><input type="button" onclick="adicionarChamado()" class="buttonAzulEscuroBranco11[botao_novo_status]" style="width:100%" value="Novo [F2]" id="botao_novo" [botao_novo_status]></td>
			          <td width="5"></td>
			          <td><input type="button" onclick="adicionarChamadoInterno()" class="buttonAzulEscuroBranco11[botao_chamado_interno_status]" style="width:100%" value="Chamado Interno [F4]" id="botao_chamado_interno" [botao_chamado_interno_status]></td>
			          <td width="5"></td>
			          <td><input type="button" onclick="verDados('dc')" class="buttonAzulEscuroBranco11[botao_ver_dc_status]" style="width:100%" title="Dados Cadastrais" value="DC" id="botao_ver_dc" [botao_ver_dc_status]></td>
			          <td width="5"></td>
			          <td><input type="button" onclick="verDados('df')" class="buttonAzulEscuroBranco11[botao_ver_df_status]" style="width:100%" title="Dados Financeiros" value="DF" id="botao_ver_df" [botao_ver_df_status]></td>
			          <td width="5"></td>
			          <td><input type="button" onclick="verDados('dm')" class="buttonAzulEscuroBranco11[botao_ver_dm_status]" style="width:100%" title="Dados Comerciais" value="DM" id="botao_ver_dm" [botao_ver_dm_status]></td>
			          <td width="5"></td>
			          <td><input type="button" onclick="verDados('dt')" class="buttonAzulEscuroBranco11[botao_ver_dt_status]" style="width:100%" title="Dados Técnicos" value="DT" id="botao_ver_dt" [botao_ver_dt_status]></td>
			          <td width="5"></td>
			          <td><input type="button" onclick="verDados('da')" class="buttonAzulEscuroBranco11[botao_ver_da_status]" style="width:100%" title="Dados Administrativos" value="DA" id="botao_ver_da" [botao_ver_da_status]></td>
			          <td width="5"></td>
			          <td><input type="button" onclick="verDados('ch')" class="buttonAzulEscuroBranco11[botao_ver_ch_status]" style="width:100%" title="Chamados" value="CH" id="botao_ver_ch" [botao_ver_ch_status]></td>
			          <td width="5"></td>
			          <td><input type="button" onclick="executarRoteiros()" class="buttonAzulEscuroBranco11[botao_roteiros_status]" style="width:100%" value="Roteiros" id="botao_roteiros" [botao_roteiros_status]></td>
	            </tr>
	          </table>
          </td>
        </tr>
        <tr>
          <td height="5"></td>
        </tr>
        <tbody id="resumo_box" style="display:[resumo_box_display]">
	        <tr>
	          <td height="117" id="resumo">
	            <table width="100%" height="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
	              <tr>
	                <td align="center" valign="top" bgcolor="#252525" class="txtBranco11">
	                  [resumo]
	                </td>
	              </tr>
	            </table>
	          </td>
	        </tr>
	        <tr>
	          <td height="5" id="resumo_espaco"></td>
	        </tr>
        </tbody>
        <tr>
          <td id="tabela_chamado" height="100%">
            <table width="100%" height="100%" border="0" cellpadding="3" cellspacing="1" bgcolor="#FFFFFF">
              <tr>
                <td align="center" valign="top" bgcolor="#252525" class="txtBranco11">
                  [chamado]
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<script type="text/javascript">
  redimensionaDivs();
  atualizaPNG();
  [comandos]
</script>

</body>
</html>
