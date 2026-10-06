<script type="text/javascript" src="rds.js.php?m=clientes_origens"></script>
<form name="editar" method="POST" action="[action]" onSubmit="return validaClienteOrigem(this)">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td>[acoes]</td>
  </tr>
  <tr>
    <td>
    <table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="200"><b>&nbsp;Nome:</b></td>
        <td>[nome]</td>
      </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" height="28"><strong>&nbsp;Integração</strong></td>
        </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Vendor ID:</b></td>
        <td><input type="text" name="vendor_id" id="vendor_id" class="inputBrancoAzulEscuro11" value="[vendor_id]" style="width:200px" maxlength="50"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Vendor Lot ID:</b></td>
        <td><input type="text" name="vendor_lot_id" id="vendor_lot_id" class="inputBrancoAzulEscuro11" value="[vendor_lot_id]" style="width:200px" maxlength="50"></td>
      </tr>
      
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" height="28"><strong>&nbsp;Separar Pontos</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Separar:</b></td>
        <td>[separar_pontos]</td>
      </tr>
      
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" height="28"><strong>&nbsp;Formato Carteira</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Apenas Carteira:</b></td>
        <td>[apenas_carteira]</td>
      </tr>
      
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" height="28"><strong>&nbsp;VOD</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Status:</b></td>
        <td>[vod_status]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Exibir t&iacute;tulos:</b></td>
        <td>[vod_exibir_titulos]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display: [verDegustacao]">
        <td><b>&nbsp;&raquo; Plano VOD:</b></td>
        <td>[plano_vod]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display: [verDegustacao]">
        <td><b>&nbsp;&raquo; Tempo degustação:</b></td>
        <td><input type="number" name="tempo_degustacao" id="tempo_degustacao" class="inputBrancoAzulEscuro11" value="[tempo_degustacao]" style="width:200px" [vod_disabled]></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display: [verDegustacao]">
        <td><b>&nbsp;&raquo; Data Inicio da degustação:</b></td>
        <td><input type="date" name="data_inicio_degustacao" [disabledDataDegustaca] [vod_disabled] [data_locked] id="data_inicio_degustacao" class="inputBrancoAzulEscuro11" value="[data_inicio_degustacao]" style="width:200px" maxlength="50"></td>
      </tr>
      
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display: [verDegustacaoTv]">
          <td colspan="2" height="28"><strong>&nbsp;TV</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display: [verDegustacaoTv]">
        <td><b>&nbsp;&raquo; Plano TV:</b></td>
        <td>[plano_tv]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display: [verDegustacaoTv]">
        <td><b>&nbsp;&raquo; Tempo degustação:</b></td>
        <td><input type="number" name="tempo_degustacao_tv" id="tempo_degustacao_tv" class="inputBrancoAzulEscuro11" value="[tempo_degustacao_tv]" style="width:200px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display: [verDegustacaoTv]">
        <td><b>&nbsp;&raquo; Data Inicio da degustação:</b></td>
        <td><input type="date" name="data_inicio_degustacao_tv" [disabledDataDegustaca_tv] id="data_inicio_degustacao_tv" class="inputBrancoAzulEscuro11" value="[data_inicio_degustacao_tv]" style="width:200px" maxlength="50"></td>
      </tr>
      
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
<script type="text/javascript">
function atualizaCamposVod(status) {
	var ativo = (status === 'ATIVO');
	var ids = ['vod_exibir_titulos', 'plano_vod', 'tempo_degustacao'];
	for (var i = 0; i < ids.length; i++) {
		var el = document.getElementById(ids[i]);
		if (el) el.disabled = !ativo;
	}
	var dt = document.getElementById('data_inicio_degustacao');
	if (dt && !dt.hasAttribute('data-locked')) dt.disabled = !ativo;
}
</script>
</form>