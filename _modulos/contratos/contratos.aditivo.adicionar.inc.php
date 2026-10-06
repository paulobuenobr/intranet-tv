<form name="adicionar" method="POST" onSubmit="return validaContratoAditivoAdicionar(this)" action="[action]">
<script type="text/javascript" src="rds.js.php?m=contratos"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Adicionar Aditivo&nbsp;</b></td></tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td>
      <table width="100%" border="0" cellspacing="1" cellpadding="3">
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="20%"><strong>&nbsp;Cliente:</strong></td>
          <td>[nome]</td>
        </tr>
	      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	        <td><strong>&nbsp;Código:</strong></td>
	        <td>[codigo]</td>
	      </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;Serviço:</strong></td>
          <td>[servico]</td>
        </tr>
        <tbody style="display:[exibir_mensagem_vigencia]">
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="color:#FF0000">
          <td colspan="2" align="center" colspan="2">
            <b>Atenção!</b><br>
            Este é um contrato com plano de fidelidade.<br>
            Vigência: [prazo_vigencia].
          </td>
        </tr>
        </tbody>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;Tipo:</strong></td>
          <td><select id="tipo" name="tipo" class="inputBrancoAzulEscuro11" onchange="mudaContratoTipo(this.value, [servico_id], [contrato_id], [cliente_id])" style="width: 100%"><option></option>[tipos]</select></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" height="30"><strong>&nbsp;Assinaturas:</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" id="assinaturas">Selecione um Tipo</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2"><b>&nbsp;Fidelização:</b></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
		    <td colspan="2">
		      <input type="radio" name="contrato_fidelidade" id="contrato_fidelidade_1" value="1" onclick="desabilitaCampos()" checked>
		      <label for="contrato_fidelidade_1">Manter</label>
		      <input type="radio" name="contrato_fidelidade" id="contrato_fidelidade_2" value="0" onclick="habilitaCampos()" >
		      <label for="contrato_fidelidade_2">Renovar</label>
		    </td>
		  </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Vigência:</strong></td>
          	<td><input name="vigencia" id="vigencia" type="text" style="width:150px" class="inputBrancoAzulEscuro11" size="50" maxlength="10" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" disabled></td>
          </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Valor Multa:</strong></td>
          <td><input name="valor_multa" id="valor_multa" type="text" style="width:150px" class="inputBrancoAzulEscuro11" size="50" maxlength="10" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)" disabled></td>
        </tr>
      	<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="40" colspan="2" align="center" >[botoes]</td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<input type="hidden" name="vigencia_minima" value="[vigencia_minima]">
<input type="hidden" name="valor_minimo_multa" value="[valor_minimo_multa]">

</form>

<script type="text/javascript">
  var cliente_id = 0;
  var servico_id = 0;
  var pontuacao_atual = new Array();
  var pontuacao_nova = new Array();
</script>
