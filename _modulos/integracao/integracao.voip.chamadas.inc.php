<script type="text/javascript">

function mudaPagina (pagina, incremento) {
  if (!pagina) pagina = parseInt(document.getElementById("filtro").pagina.value);
  document.getElementById("filtro").reset();
  document.getElementById("filtro").pagina.value = pagina + incremento;
  document.getElementById("filtro").submit();  
}

function filtroSubmit () {
  if (validaVoipFiltro(document.getElementById("filtro"))) {
    document.getElementById("filtro").pagina.value = 1;
    document.getElementById("filtro").submit();
  }
}

function comparacaoTarifasComparatel (origem, destino, duracao, data, hora) {
  url = "http://www.comparatel.com.br/online/icompare.asp?from=" + origem + "&to=" + destino + "&length=" + duracao + "&date=" + data + "&time=" + hora + "";
  parametros = "top,left,width=" + (screen.availWidth-10>760?760:screen.availWidth-10) + ",height=" + (screen.availHeight-30) + "statusbar=0,toolbar=0,scrollbars=1,resizable=1";
  operacoes_sistema_janela = window.open(url, "comparacao_tarifas", parametros);
}

</script>
<table width="760" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td>
			<p class="titulo">Resumo</p>
			<table width="100%" cellpadding="3" border="1" class="voip">
			  <tr>
			    <td colspan="6" class="titulo">Conta</td>
			  </tr>
			  <tr>
			    <td class="subtitulo">Usuário:</td>
			    <td>[conta_usuario]</td>
			    <td class="subtitulo">Status:</td>
			    <td>[conta_status]</td>
			    <td class="subtitulo">Situação:</td>
			    <td>[conta_situacao]</td>
			  </tr>
			  <tr>
			    <td colspan="6" class="titulo">Saldo</td>
			  </tr>
			  <tr>
			    <td class="subtitulo">Atual:</td>
			    <td>[saldo_atual]</td>
			    <td class="subtitulo">Cheque Especial:</td>
			    <td>[saldo_cheque_especial]</td>
			    <td class="subtitulo">Disponível:</td>
			    <td>[saldo_disponivel]</td>
			  </tr>
			  <tr>
			    <td colspan="6" class="titulo">Consumo</td>
			  </tr>
			  <tr>
			    <td class="subtitulo">Dia:</td>
			    <td>[consumo_dia]</td>
			    <td class="subtitulo">Semana:</td>
			    <td>[consumo_semana]</td>
			    <td class="subtitulo">Mês:</td>
			    <td>[consumo_mes]</td>
			  </tr>
			</table>
			<p class="titulo">Chamadas</p>
			<form method="GET" name="filtro" id="filtro">
				<table width="100%" cellpadding="3" border="1" class="voip">
				  <tr>
				    <td rowspan="2" align="center" valign="middle" class="titulo">Data/Hora</td>
				    <td colspan="2" align="center" valign="middle" class="titulo">Destino</td>
				    <td rowspan="2" align="center" valign="middle" class="titulo">Dura&ccedil;&atilde;o</td>
				    <td rowspan="2" align="center" valign="middle" class="titulo">Valor</td>
				    <td rowspan="2" align="center" valign="middle" class="titulo">&nbsp;</td>
				  </tr>
				  <tr>
				    <td align="center" valign="middle" class="titulo">Nome</td>
				    <td align="center" valign="middle" class="titulo">Número</td>
				  </tr>
				  <tr>
				    <td nowrap>
				      <input id="filtro_datahora_inicio" name="filtro_datahora_inicio" type="text" size="11" class="formInput" onKeyPress="return formataDataHora(this,event);" onBlur="return formataDataHora(this,event);" value="[filtro_datahora_inicio]" />
				      <input id="filtro_datahora_termino" name="filtro_datahora_termino" type="text" size="11" class="formInput" onKeyPress="return formataDataHora(this,event);" onBlur="return formataDataHora(this,event);" value="[filtro_datahora_termino]" />
				    </td>
				    <td nowrap colspan="2">
				      <input id="filtro_destino" name="filtro_destino" type="text" size="40" class="formInput" onKeyPress="return formataDestino(this,event);" onBlur="return formataDestino(this,event);" value="[filtro_destino]" />
				    </td>
				    <td nowrap>
				      <input id="filtro_duracao_inicio" name="filtro_duracao_inicio" type="text" size="8" class="formInput" onKeyPress="return formataDuracao(this,event);" onBlur="return formataDuracao(this,event);" value="[filtro_duracao_inicio]" />
				      <input id="filtro_duracao_termino" name="filtro_duracao_termino" type="text" size="8" class="formInput" onKeyPress="return formataDuracao(this,event);" onBlur="return formataDuracao(this,event);" value="[filtro_duracao_termino]" />
				    </td>
				    <td nowrap>
				      <input id="filtro_valor_inicio" name="filtro_valor_inicio" type="text" size="9" class="formInput" onKeyPress="return formataDinheiro(this,event);" onBlur="return formataDinheiro(this,event);" value="[filtro_valor_inicio]" />
				      <input id="filtro_valor_termino" name="filtro_valor_termino" type="text" size="9" class="formInput" onKeyPress="return formataDinheiro(this,event);" onBlur="return formataDinheiro(this,event);" value="[filtro_valor_termino]" />
				    </td>
				    <td nowrap>
				      <input id="filtrar" name="filtrar" type="button" value="F" class="formButton" onClick="filtroSubmit()" />
				      <input id="pagina" name="pagina" type="hidden" value="[filtro_pagina]" />
				      <input id="m" name="m" type="hidden" value="voip" />
				      <input id="a" name="a" type="hidden" value="chamadas" />
				    </td>
				  </tr>
				  <tr>
				    <td colspan="6" align="center"><input name="logout" type="submit" class="formButton" value="Logout" style="width:75px" onclick="javascript:window.location='./?m=voip&a=logout'"></td>
				  </tr>
				</table>
  		</form>
    </td>
  </tr>
</table>
