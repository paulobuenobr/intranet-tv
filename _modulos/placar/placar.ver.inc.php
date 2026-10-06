<script type="text/javascript">
  document.body.style.overflowX = 'auto';
  function atualizaFormularioPlacar() {
    if (document.getElementById('tipo_1').checked) {
      document.getElementById('termino').value = document.getElementById('inicio').value;
      document.getElementById('periodo_termino').style.display = 'none';
      document.getElementById('termino').readOnly = true;
    } else {
      document.getElementById('periodo_termino').style.display = 'inline';
      document.getElementById('termino').readOnly = false;
    }
    if (document.getElementById('tipo_1').checked || document.getElementById('tipo_2').checked) {
      document.getElementById('periodo_data').style.display = ehNetscape() ? 'table-row' : 'block';
      document.getElementById('periodo_competencia').style.display = 'none';
    } else {
      document.getElementById('periodo_data').style.display = 'none';
      document.getElementById('periodo_competencia').style.display = ehNetscape() ? 'table-row' : 'block';
    }
  }
  function validaFormularioPlacar(formulario) {
    var erro_vazio = new Array();
    var erro_invalido = new Array();
    if (formulario.tipo_1.checked || formulario.tipo_2.checked) {
      if (!formulario.inicio.value) {
        erro_vazio[erro_vazio.length] = "Período » Início";
      } else if (!validaData(formulario.inicio.value)) {
        erro_invalido[erro_invalido.length] = "Período » Início";
      } else if (subtraiDatas('01/01/2007', formulario.inicio.value) > 0) {
        erro_invalido[erro_invalido.length] = "Período » Início inferior a 01/01/2007";
      } else if (formulario.termino.value && validaData(formulario.termino.value) && subtraiDatas(formulario.inicio.value, formulario.termino.value) > 0) {
        erro_invalido[erro_invalido.length] = "Período » Início maior que Término";
      }
      if (!formulario.termino.value) {
        erro_vazio[erro_vazio.length] = "Período » Término";
      } else if (!validaData(formulario.termino.value)) {
        erro_invalido[erro_invalido.length] = "Período » Término";
      }
    } else {
      if (!formulario.competencia_inicio.value) {
        erro_vazio[erro_vazio.length] = "Período » Início";
      } else if (!validaCompetencia(formulario.competencia_inicio.value)) {
        erro_invalido[erro_invalido.length] = "Período » Início";
      } else if (subtraiDatas('01/01/2007', '01/' + formulario.competencia_inicio.value) > 0) {
        erro_invalido[erro_invalido.length] = "Período » Início inferior a 01/2007";
      } else if (formulario.competencia_termino.value && validaCompetencia(formulario.competencia_termino.value) && subtraiDatas('01/' + formulario.competencia_inicio.value, '01/' + formulario.competencia_termino.value) > 0) {
        erro_invalido[erro_invalido.length] = "Período » Início maior que Término";
      }
      if (!formulario.competencia_termino.value) {
        erro_vazio[erro_vazio.length] = "Período » Término";
      } else if (!validaCompetencia(formulario.competencia_termino.value)) {
        erro_invalido[erro_invalido.length] = "Período » Término";
      }
    }
    return apresentaErros(erro_vazio, erro_invalido);
  }
</script>
<form name="painel" method="POST" action="[action]" onSubmit="return validaFormularioPlacar(this)">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td align="center" colspan="2">[placar]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" align="right">Última Atualização: [atualizacao]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;Opções:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="15%"><b>&nbsp; » Tipo:</b></td>
        <td>
          <table width="300" border="0" cellspacing="0" cellpadding="0">
            <tr class="txtAzulEscuro11">
              <td width="25"><input type="radio" name="tipo" value="1" id="tipo_1" onclick="atualizaFormularioPlacar()" [tipo_1_checked]></td>
              <td><label for="tipo_1">Geral</label></td>
              <td width="25"><input type="radio" name="tipo" value="2" id="tipo_2" onclick="atualizaFormularioPlacar()" [tipo_2_checked]></td>
              <td><label for="tipo_2">Diário</label></td>
              <td width="25"><input type="radio" name="tipo" value="3" id="tipo_3" onclick="atualizaFormularioPlacar()" [tipo_3_checked]></td>
              <td><label for="tipo_3">Semanal</label></td>
              <td width="25"><input type="radio" name="tipo" value="4" id="tipo_4" onclick="atualizaFormularioPlacar()" [tipo_4_checked]></td>
              <td><label for="tipo_4">Mensal</label></td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" id="periodo_data" style="display:[exibe_periodo]">
        <td><b>&nbsp; » Período:</b></td>
        <td>
          <input type="text" name="inicio" id="inicio" style="width:70px" class="inputBrancoAzulEscuro11" value="[inicio]" onkeyup="atualizaFormularioPlacar()" onkeypress="return formataData(this, event)" onblur="atualizaFormularioPlacar(); return formataData(this, event)">
          <span id="periodo_termino" style="display:[exibe_periodo_termino]">até <input type="text" name="termino" id="termino" style="width:70px" class="inputBrancoAzulEscuro11" value="[termino]" onkeypress="return formataData(this, event)" onblur="return formataData(this, event)" readonly></span> (dd/mm/aaaa)
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" id="periodo_competencia" style="display:[exibe_competencia]">
        <td><b>&nbsp; » Período:</b></td>
        <td>
          <input type="text" name="competencia_inicio" id="competencia_inicio" style="width:70px" class="inputBrancoAzulEscuro11" value="[competencia_inicio]" onkeyup="atualizaFormularioPlacar()" onkeypress="return formataCompetencia(this, event)" onblur="atualizaFormularioPlacar(); return formataCompetencia(this, event)"> até 
          <input type="text" name="competencia_termino" id="competencia_termino" style="width:70px" class="inputBrancoAzulEscuro11" value="[competencia_termino]" onkeypress="return formataCompetencia(this, event)" onblur="return formataCompetencia(this, event)"> (mm/aaaa)
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp; » Formato CVS?</b></td>
        <td>
          <table width="100" border="0" cellspacing="0" cellpadding="0">
            <tr class="txtAzulEscuro11">
              <td width="25"><input type="radio" name="cvs" value="1" id="saida_1"></td>
              <td><label for="saida_1">Sim</label></td>
              <td width="25"><input type="radio" name="cvs" value="0" id="saida_2" checked></td>
              <td><label for="saida_2">Não</label></td>
            </tr>
          </table>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="40" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
