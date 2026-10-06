<script type="text/javascript" src="rds.js.php?m=planos_servicos"></script>
<form name="adicionar" method="POST" action="[action]" onSubmit="return validaServico(this)">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><b>&nbsp;Nome:</b></td>
        <td><input type="text" name="nome" id="nome" class="inputBrancoAzulEscuro11" style="width:100%" maxlength="50"></td>
      </tr>
     <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Opções:</b></td>
        <td><table border="0" cellspacing="0" cellpadding="0">
          <tr class="txtAzulEscuro11">
            <td valign="top">
              <input type="checkbox" name="aceita_multiplos" id="aceita_multiplos" value="1">  <label for="aceita_multiplos">Aceita múltiplos</label><br>
              <input type="checkbox" name="com_contrato" id="com_contrato" value="1"> <label for="com_contrato">Com contrato</label><br>
              <input type="checkbox" name="tem_dominio" id="tem_dominio" value="1"> <label for="tem_dominio">Tem domínio</label><br>
            </td>
            <td valign="top">
              <input type="checkbox" name="gera_visita" id="gera_visita" value="1"> <label for="gera_visita">Gera visita</label><br>
              <input type="checkbox" name="auto_aprovacao_comercial" id="auto_aprovacao_comercial" value="1"> <label for="auto_aprovacao_comercial">Auto-aprovação comercial</label><br>
              <input type="checkbox" name="auto_aprovacao_tecnico" id="auto_aprovacao_tecnico" value="1"> <label for="auto_aprovacao_tecnico">Auto-aprovação técnica</label><br>   
            </td>
          </tr></table>
        </td>
     </tr>
     <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Multa máxima:</b></td>
        <td><input type="text" name="multa_maxima" id="multa_maxima" class="inputBrancoAzulEscuro11" maxlength="50" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)"></td>
      </tr>
     <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
     </tr>
 	</table></td>
 </tr>
 </table>
</form>
