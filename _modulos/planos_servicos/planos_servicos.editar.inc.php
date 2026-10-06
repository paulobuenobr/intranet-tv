<script type="text/javascript" src="rds.js.php?m=planos_servicos"></script>
<form name="editar" method="POST" action="[action]" onSubmit="return validaServico(this)">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><b>&nbsp;Nome:</b></td>
        <td><input type="text" name="nome" id="nome" class="inputBrancoAzulEscuro11" value="[nome]" style="width:100%" maxlength="50"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Opções:</b></td>
        <td><table border="0" cellspacing="0" cellpadding="0">
          <tr class="txtAzulEscuro11">
            <td valign="top">
              [aceita_multiplos] <label for="aceita_multiplos">Aceita múltiplos</label><br>
              [com_contrato] <label for="com_contrato">Com contrato</label><br>
              [gera_visita] <label for="gera_visita">Gera visita</label>
            </td>
            <td valign="top">
              [auto_aprovacao_tecnico] <label for="auto_aprovacao_tecnico">Auto-aprovação técnica</label><br>
              [auto_aprovacao_comercial] <label for="auto_aprovacao_comercial">Auto-aprovação comercial</label><br>
    		  		[tem_dominio] <label for="tem_dominio">Tem domínio</label><br>
            </td>
          </tr></table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Multa máxima:</b></td>
        <td><input type="text" name="multa_maxima" id="multa_maxima" class="inputBrancoAzulEscuro11" value="[multa_maxima]" maxlength="50" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Classificação Fiscal:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="classificacao_fiscal_listagem">[classificacao_fiscal_listagem]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Formas de Pagamento (Mudança de Endereço):</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="formas_pagamento_listagem">[formas_pagamento_listagem]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
<input type="hidden" name="formas_pagamento_lista" id="formas_pagamento_lista" value='[formas_pagamento_lista]'>
<input type="hidden" name="classificacao_fiscal_lista" id="classificacao_fiscal_lista" value='[classificacao_fiscal_lista]'>
</form>
