<script type="text/javascript" src="rds.js.php?m=clientes_pontos"></script>
<form name="editar" method="POST" onSubmit="return validaClientePontos(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Editar&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><b>&nbsp;Cliente:</b></td>
        <td width="80%" id="cliente">[cliente_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Assinatura:</b></td>
        <td>[assinatura]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Interface:</b></td>
         <td>[interface]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Endereço IP:</b></td>
         <td>[prefixo_ip_1] .[prefixo_ip_2] . [prefixo_ip_3] . [endereco_ip]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;MAC:</b></td>
        <td><input name="mac" id="mac" type="text" class="inputBrancoAzulEscuro11" style="width:177px" size="17" maxlength="17" onkeypress="return formataMAC(this, event)" onblur="verificaMacExistente(this, [ponto_id], this.form.interface.value); ocultarMAC();" value="[mac]"> [capturar]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Velocidade:</b></td>
        <td>[velocidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Comentário:</strong></td>
        <td><input name="comentario" id="comentario" value="[comentario]" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="128" maxlength="128"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Ajustes Programados:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">
        	<table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11">[ajustes_programados]</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Status:</b></td>
        <td>[status]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Comentários:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" style="width:100%" rows="3" cols="52"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[lista_comentarios]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
<input type="hidden" id="assinatura" value="[assinatura]">
</form>

<div id="lista_macs" style="width:170px;background:#FFFFFF;border-width:0px;position:absolute;top:151px;left:112px;display:none;padding:0px"><table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%"><tr><td id="lista_macs_conteudo" style="border-width:1px;border-color:#000000;border-style:solid;padding-right:3px;padding-left:3px;padding-top:2px;padding-bottom:2px"></td></tr></table></div>

<script type="text/javascript">
  var cliente_id = '[cliente_id]';
  var condominio_id = '[condominio_id]';
  var ponto_id = '[ponto_id]';
	if (window.opener && !window.opener.closed) {
		if (window.opener.buscaPontos) {
			window.opener.buscaPontos(cliente_id);
		} else if (window.opener.buscaCondominiosPontos) {
			window.opener.buscaCondominiosPontos(condominio_id);
		}
	}
</script>
