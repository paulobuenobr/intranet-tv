<script type="text/javascript" src="rds.js.php?m=planos" id="planos.js"></script>
<form name="adicionar" method="POST" action="[action]" onSubmit="return validaPlano(this);">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="30%"><b>&nbsp;Nome:</b></td>
        <td><input type="text" name="nome" id="nome" class="inputBrancoAzulEscuro11" style="width:350px" maxlength="250"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Serviço:</b></td>
        <td><select id="servico" name="servico" class="inputBrancoAzulEscuro11" style="width:350px" onchange="mudaServico(this.value)">[servico]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Tipo:</b></td>
        <td><select id="tipo" name="tipo" class="inputBrancoAzulEscuro11" style="width:350px" onchange="atualizaAplicavel()">[tipo]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" id="aplicavel_linha" style="display:[aplicavel_display]">
        <td><b>&nbsp;Aplicável à:</b></td>
        <td id="aplicavel_container">[aplicavel]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Sistema:</b></td>
        <td><select name="sistema" class="inputBrancoAzulEscuro11" style="width:350px">[sistema]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Ajuste:</b></td>
        <td><select name="ajuste_categoria" class="inputBrancoAzulEscuro11" style="width:350px">[ajuste_categoria]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Condomínio:</b></td>
        <td><select name="condominio" class="inputBrancoAzulEscuro11" style="width:350px">[condominio]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Origens:</strong></td>
        <td>[origens]</td>
      </tr>
      
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;NFS-e:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Gerar NFS-e:</b></td>
        <td><select name="gerar_nfse" id="gerar_nfse" class="inputBrancoAzulEscuro11" style="width:350px" onchange="mudaGerarNota(this.value)">[gerar_nfse]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Cóodigo de serviço:</b></td>
        <td><select name="codigo_servico" id="codigo_servico" class="inputBrancoAzulEscuro11" style="width:350px" [disabled_codigo_servico]>[codigo_servico]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Cóodigo de NBS:</b></td>
        <td><select name="codigo_nbs" id="codigo_nbs" class="inputBrancoAzulEscuro11" style="width:350px" [disabled_codigo_nbs]>[codigo_nbs]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; CNAE:</b></td>
        <td><select name="cnae" id="cnae" class="inputBrancoAzulEscuro11" style="width:350px" [disable_cnae]>[cnae]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; ISS Retido:</b></td>
        <td><select name="issRetido" id="issRetido" class="inputBrancoAzulEscuro11" style="width:350px" [disabled_issRetido]>[issRetido]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Alíquota:</b></td>
        <td><input name="aliquota" id="aliquota" type="text" class="inputBrancoAzulEscuro11" [disable_aliquota] onKeyPress="return formataNumeroDecimal(this, event)" value="[aliquota]" onBlur="return formataNumeroDecimal(this, event)" style="width:350px"></td>
      </tr>
      
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;Parceiro:</b></td>
      </tr>

      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo de Limitação:</strong></td>
        <td>
          <input type="radio" id="tipo_limitacao1" name="tipo_limitacao" value="1" checked="checked" onchange="mudaTipoLimitacao(this.value)"> <label for="tipo_limitacao1">Por Quantidade</label>
          <input type="radio" id="tipo_limitacao2" name="tipo_limitacao" value="2" onchange="mudaTipoLimitacao(this.value)"> <label for="tipo_limitacao2">Por Valor</label>
          
        </td>
      </tr>

      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Plano:</b></td>
        <td><select name="parceiro_plano" id="parceiro_plano" class="inputBrancoAzulEscuro11" style="width:350px">[parceiro_planos]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Máximo:</b></td>
        <td><input name="parceiro_max" id="parceiro_max" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:350px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Valor Máximo:</b></td>
        <td><input name="valor_max" id="valor_max" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)" style="width:350px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><b>&nbsp;&raquo; Planos/Valores Vinculados:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="planos_limitacao_listagem">[planos_limitacao_listagem]</td>
      </tr>

      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;Valores:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Instalação:</b></td>
        <td><input name="instalacao" id="instalacao" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)" style="width:350px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Mensalidade:</b></td>
        <td><input name="mensalidade" id="mensalidade" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)" style="width:350px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Periodicidade:</b></td>
        <td><select name="periodicidade" class="inputBrancoAzulEscuro11" style="width:350px">[periodicidade]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Parcelamento Máximo:</b></td>
        <td><input name="parcelamento_maximo" id="parcelamento_maximo" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:350px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display: [mostrar_multiservice]">
        <td height="30" colspan="2"><b>&nbsp;Multiservice:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display: [mostrar_multiservice]">
        <td colspan="2" id="multiservice_listagem">[multiservice_listagem]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;IPTV:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Pacotes:</b></td>
        <td>[iptv_pacotes]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; STBs:</b></td>
        <td><input name="iptv_stb_quantidade" id="iptv_stb_quantidade" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:350px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Aplicativos:</b></td>
        <td><input name="iptv_aplicativos_quantidade" id="iptv_aplicativos_quantidade" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:350px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Gravação (horas):</b></td>
        <td><input name="iptv_gravacao_horas" id="iptv_gravacao_horas" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:350px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Streaming:</b></td>
      	<td><select name="iptv_streaming" id="iptv_streaming" class="inputBrancoAzulEscuro11" style="width:90px">[iptv_streaming]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Apps:</b></td>
        <td>[iptv_apps]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;VOD:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Pacotes:</b></td>
        <td>[vod_pacotes]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Telas:</b></td>
        <td><input name="vod_telas" id="vod_telas" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:350px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;Pontos:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Quantidade:</b></td>
        <td><input name="pontos_quantidade" id="pontos_quantidade" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:350px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Velocidade Download:</b></td>
        <td><input name="pontos_velocidade_download" id="pontos_velocidade_download" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:257px">&nbsp;<select name="pontos_velocidade_download_unidade" id="pontos_velocidade_download_unidade" class="inputBrancoAzulEscuro11" style="width:90px">[unidades_bits]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Velocidade Upload:</b></td>
        <td><input name="pontos_velocidade_upload" id="pontos_velocidade_upload" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:257px">&nbsp;<select name="pontos_velocidade_upload_unidade" id="pontos_velocidade_upload_unidade" class="inputBrancoAzulEscuro11" style="width:90px">[unidades_bits]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;E-Mails:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Quantidade:</b></td>
        <td><input name="emails_quantidade" id="emails_quantidade" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:350px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Capacidade:</b></td>
        <td><input name="emails_capacidade" id="emails_capacidade" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:257px">&nbsp;<select name="emails_capacidade_unidade" id="emails_capacidade_unidade" class="inputBrancoAzulEscuro11" style="width:90px">[unidades]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;Hospedagens:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Quantidade:</b></td>
        <td><input name="hospedagens_quantidade" id="hospedagens_quantidade" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:350px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Capacidade:</b></td>
        <td><input name="hospedagens_capacidade" id="hospedagens_capacidade" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:257px">&nbsp;<select name="hospedagens_capacidade_unidade" id="hospedagens_capacidade_unidade" class="inputBrancoAzulEscuro11" style="width:90px">[unidades]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Transferência:</b></td>
        <td><input name="hospedagens_transferencia" id="hospedagens_transferencia" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:257px">&nbsp;<select name="hospedagens_transferencia_unidade" id="hospedagens_transferencia_unidade" class="inputBrancoAzulEscuro11" style="width:90px">[unidades]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;Base de Dados:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Quantidade:</b></td>
        <td><input name="basesdedados_quantidade" id="basesdedados_quantidade" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:350px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Capacidade:</b></td>
        <td><input name="basesdedados_capacidade" id="basesdedados_capacidade" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:257px">&nbsp;<select name="basesdedados_capacidade_unidade" id="basesdedados_capacidade_unidade" class="inputBrancoAzulEscuro11" style="width:90px">[unidades]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Equipamentos em Comodato:</b></td>
      	<td id="equipamentos_comodatos">[equipamentos_comodatos]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td ><b>&nbsp;Valor Comodato: </b></td>
      	<td id="valor_comodato"><b>&nbsp;[valor_comodato]</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Opções:</b></td>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr class="txtAzulEscuro11">
            <td width="25%" valign="top">
              <input type="checkbox" name="aplica_pessoa_fisica" id="aplica_pessoa_fisica"> <label for="aplica_pessoa_fisica">Pessoa Física</label><br>
              <input type="checkbox" name="aplica_pessoa_juridica" id="aplica_pessoa_juridica"> <label for="aplica_pessoa_juridica">Pessoa Jurídica</label><br>
            </td>
            <td width="25%" valign="top">
              <input type="checkbox" name="aplica_com_condominio" id="aplica_com_condominio"> <label for="aplica_com_condominio">Com Condomínio</label><br>
              <input type="checkbox" name="aplica_sem_condominio" id="aplica_sem_condominio"> <label for="aplica_sem_condominio">Sem Condomínio</label><br>
            </td>
            <td width="50%" valign="top">
              <input type="checkbox" name="desconto_condominio" id="desconto_condominio"> <label for="desconto_condominio">Herda Desconto Condomínio</label>
            </td>
          </tr></table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Tipo de Contrato:</b></td>
      	  <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
		        <tr class="txtAzulEscuro11">
		            <td width="25%" valign="top">
		            	<input type="checkbox" name="plano_tipo_contrato_principal" id="plano_tipo_contrato_principal" value="plano_tipo_contrato_principal"> <label for="plano_tipo_contrato_principal">Contrato Principal</label><br>
		      		</td>
		      		<td width="25%" valign="top">
						<input type="checkbox" name="plano_tipo_migracao" id="plano_tipo_migracao" value="plano_tipo_migracao"> <label for="plano_tipo_migracao">Migração</label><br>
	      			</td>
					<td width="50%" valign="top"></td>
	      		</tr>
	      	</table>
          </td>	
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Vínculos:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="vinculos_listagem">[vinculos_listagem]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;Comissões:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[comissoes]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Premiação &raquo; Pontos:</b></td>
        <td><input name="premiacao_pontos" id="premiacao_pontos" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumeroReal(this,event);" onBlur="return formataNumeroReal(this,event);" style="width:150"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Status:</b></td>
        <td><select name="status" class="inputBrancoAzulEscuro11" style="width:15%">[status]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
   </table></td>
  </tr>
</table>
<input type="hidden" name="multiservice_lista" id="multiservice_lista">
<input type="hidden" name="planos_limitacao_lista" id="planos_limitacao_lista">
<input type="hidden" name="vinculos_lista" id="vinculos_lista">
<input type="hidden" name="voip_tabelas_planos_lista" id="voip_tabelas_planos_lista">
</form>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    var tipoLimitacaoChecked = document.querySelector('input[name="tipo_limitacao"]:checked');
    if (tipoLimitacaoChecked) {
      mudaTipoLimitacao(tipoLimitacaoChecked.value);
    }
});
</script>