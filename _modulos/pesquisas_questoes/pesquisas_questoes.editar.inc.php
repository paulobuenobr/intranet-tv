<script type="text/javascript" src="rds.js.php?m=pesquisas_questoes"></script>
<form name="editar" id="editar" method="POST" onSubmit="return validaQuestao(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11">[acoes]</tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="15%"><b>&nbsp;Número:</b></td>
        <td width="85%"><input name="numero" id="numero" value="[numero]" type="text" style="width:95px" class="inputBrancoAzulEscuro11" size="50" maxlength="80"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Conteúdo:</b></td>
        <td><textarea name="conteudo" id="conteudo" class="textBrancoAzulEscuro11" style="width:100%" rows="3" cols="70">[conteudo]</textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Tipo:</b></td>
        <td><select id="tipo" name="tipo" class="inputBrancoAzulEscuro11" onchange="mudaTipo()" style="height:20px;width:200px"><option></option>[tipos]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Variável:</b></td>
        <td><select id="variavel" name="variavel" class="inputBrancoAzulEscuro11" onchange="mudaVariavel()" style="height:20px;width:200px"><option></option>[variavel]</select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Limites:</b></td>
        <td>
        	<table width="200" border="0" cellspacing="0" cellpadding="0">
	      		<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
	      		  <td width="105"><input name="minimo" id="minimo" value="[minimo]" type="text" style="width:95px" class="inputBrancoAzulEscuro11" size="50" onKeyPress="return formataAlternativaLimite(this, event)" onblur="return formataAlternativaLimite(this, event)"></td>
	      		  <td width="95"><input name="maximo" id="maximo" value="[maximo]" type="text" style="width:95px" class="inputBrancoAzulEscuro11" size="50" onKeyPress="return formataAlternativaLimite(this, event)" onblur="return formataAlternativaLimite(this, event)"></td>
        		</tr>
        	</table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Alternativas:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="3">
          <table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="listagem_alternativas">[listagem_alternativas]</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="15%"><b>&nbsp;Máximo:</b></td>
        <td width="85%"><input name="maximo_alternativas" id="maximo_alternativas" value="[maximo_alternativas]" type="text" style="width:95px" class="inputBrancoAzulEscuro11" size="50" maxlength="80"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
<input type="hidden" name="lista_alternativas" id="lista_alternativas" value='[lista_alternativas]'>
</form>

<script type="text/javascript">
	if (window.opener && window.opener.buscaQuestoes) {
		window.opener.buscaQuestoes([pesquisa_id]);
	}
</script>
