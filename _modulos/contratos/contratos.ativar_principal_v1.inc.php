<form name="ativar" method="POST" action="[action]" onSubmit="return validaContratoAtivar(this)">
<script type="text/javascript" src="rds.js.php?m=contratos" id="contratos.js"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11">[acoes]</tr>
</table>
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center" colspan="2"><b>Atenção:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center" colspan="2"><p>Você deseja realmente <b>ativar</b> o contrato "<b>[contrato]</b>" do cliente<br>"<b>[cliente]</b>"?</p>
    </td>
  </tr>

  
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><strong>&nbsp;Valores:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp; » Instalação:</strong></td>
    <td><select id="instalacao_valor" name="instalacao_valor" class="inputBrancoAzulEscuro11" style="width:80%" onchange="mudaValorInstalacao(this.value)">[instalacao_valor]</select></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp; » Parcelamento:</strong></td>
    <td><select id="parcelamento" name="parcelamento" class="inputBrancoAzulEscuro11" style="width:200px" [parcelamento_disabled]>[parcelamento]</select></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><strong>&nbsp; » Mensalidade:</strong></td>
    <td id="mensalidade_valor">[mensalidade_valor]</td>
  </tr>
  
  
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="25%"><b>&nbsp;Data de Ativação:</b></td>
    <td width="75%"><input name="data_ativacao" id="data_ativacao" type="text" class="inputBrancoAzulEscuro11" style="width:200px" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="[data_ativacao]"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Instalador:</b></td>
    <td>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr class="txtAzulEscuro11">
          <td width="80%"><select id="instalador" name="instalador" class="inputBrancoAzulEscuro11" style="width:100%"><option></option>[instalador]</select></td>
          <td width="5%"><input type="checkbox" name="sem_instalador" id="sem_instalador" onclick="if(this.checked){document.getElementById('instalador').value = ''; document.getElementById('instalador').disabled = true;} else {document.getElementById('instalador').disabled = false;}" [sem_visita_disabled]></td>
          <td width="15%"><label for="sem_instalador">Sem Visita</label></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="30"><b>&nbsp;Comentário:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="center"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" style="width:100%" rows="7" cols="105"></textarea></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" align="left">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr class="txtAzulEscuro11">
    	    <td width="20"><input type="checkbox" name="com_pendencias" id="com_pendencias" value="true"></td>
    	    <td><label for="com_pendencias">Ativar com pendências</label></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2" height="40" align="center" colspan="2">[botoes]</td>
  </tr>
</table>
</form>
