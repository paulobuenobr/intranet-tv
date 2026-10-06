<script type="text/javascript" src="rds.js.php?m=chamados"></script>
<script type="text/javascript" src="_modulos/chamados_sistema/chamados_sistema.js"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Adicionar&nbsp;</b></td></tr>
</table>
<form name="chamados_adicionar" id="chamados_adicionar" method="POST" onSubmit="return validaChamadosAdicionar(this)" action="[action]">
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td width="20%"><b>&nbsp;Condomínio:</b></td>
    <td width="80%">[nome]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Endereço:</b></td>
    <td>[endereco]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Solicitante:</b></td>
    <td><input name="solicitante" id="solicitante" value="" type="text" class="inputBrancoAzulEscuro11" size="80" maxlength="80" style="width:100%"></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Nível de Stress:</b></td>
    <td>[nivel_stress]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Categoria:</b></td>
    <td>[categoria]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td><b>&nbsp;Prioridade:</b></td>
    <td>[prioridade]</td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Nova Etapa:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="margin:0px;padding:0px">
    <td colspan="2">[nova_etapa]</td>
  </tr>
  <tbody style="display:none">
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td height="30" colspan="2"><b>&nbsp;Solicitação de Fechamento:</b></td>
    </tr>
    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      <td><b>&nbsp;&raquo; Responsável:</b></td>
      <td>[responsavel_fechamento]</td>
    </tr>
  </tbody>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="30" colspan="2"><b>&nbsp;Opções:</b></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td colspan="2">
      <table cellpadding="0" cellspacing="0" border="0">
        <tr class="txtAzulEscuro11">
          <td><input type="checkbox" id="acompanhar" name="acompanhar">&nbsp;</td>
          <td><label for="acompanhar">Acompanhar Chamado</label></td>
        </tr>
        <tr class="txtAzulEscuro11">
          <td><input type="checkbox" id="favoritos" name="favoritos">&nbsp;</td>
          <td><label for="favoritos">Adicionar ao Favoritos</label></td>
        </tr>
      </table>
    </td>
  </tr>
	<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" colspan="2" align="center">[botoes]</td>
  </tr>
</table>
</form>

<script type="text/javascript">
  atualizaCaracteresRestantes();
</script>
