<script type="text/javascript" src="rds.js.php?m=ajustes_financeiros"></script>
<form name="editar" method="POST" onSubmit="return validaAjusteFinanceiro(this)" action="[action]">
<table border="0" cellspacing="1" cellpadding="3" width="100%">
	<tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Editar&nbsp;</b></td></tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="25%"><strong>&nbsp;Condomínio:</strong></td>
        <td width="75%">[condominio_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Categoria:</strong></td>
        <td>[categoria]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td>[tipo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Valor:</strong></td>
        <td>
          <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
              <td width="135">[valor_tipo]</td>
              <td>&nbsp;</td>
              <td width="100%">[valor]</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Período:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Início:</strong></td>
        <td>[competencia_inicio]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Término:</strong></td>
        <td>[competencia_termino]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" rows="3" cols="75" style="width: 100%"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[comentarios]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Status:</strong></td>
        <td>[status]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
<input name="ultimo_lote" type="hidden" value="[ultimo_lote]">
<input name="erro_competencia" type="hidden" value="0">
</form>

<script type="text/javascript">
  if (window.opener && window.opener.buscaAjustesCondominio) {
    window.opener.buscaAjustesCondominio([condominio_id]);
  }
</script>