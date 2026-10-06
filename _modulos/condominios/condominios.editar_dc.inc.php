<script type="text/javascript" src="rds.js.php?m=enderecos"></script>
<script type="text/javascript" src="rds.js.php?m=condominios_blocos"></script>
<script type="text/javascript" src="rds.js.php?m=condominios_contatos"></script>
<script type="text/javascript" src="rds.js.php?m=condominios.alertas"></script>
<script type="text/javascript" src="rds.js.php?m=ceps"></script>
<form name="editar_dc" method="POST" onSubmit="return validaCondominioDadosCadastrais(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td>[acoes]</td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Nome:</strong></td>
        <td>[nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo:</strong></td>
        <td>[tipos]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Classe Social:</strong></td>
        <td>[classe]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Alerta(s):</strong></td>
        <td id="alertas">[alertas]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Endere&ccedil;o:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; CEP:</strong></td>
        <td>[cep] [pesquisar_cep]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Estado:</strong></td>
        <td><input name="estado" id="estado" value="[estado]" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Cidade:</strong></td>
        <td><input name="cidade" id="cidade" value="[cidade]" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Bairro:</strong></td>
        <td><input name="bairro" id="bairro" value="[bairro]" type="text" style="width:250px" class="inputBrancoAzulEscuro11" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Logradouro:</strong></td>
        <td><input name="logradouro" id="logradouro" value="[logradouro]" type="text" style="width:400px" class="inputBrancoAzulEscuro11" size="50" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Número:</strong></td>
        <td>[numero]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Comentários:</strong></td>
        <td><textarea name="comentarios" id="comentarios" class="textBrancoAzulEscuro11" style="width:400px" rows="7" cols="51">[comentarios]</textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Blocos:</strong> [blocos_adicionar] <input type="checkbox" name="bloco_unico" id="bloco_unico" onClick="mudaBlocoUnico(this.checked)"><label for="bloco_unico">Único</label></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="blocos">[blocos]</td>
            </tr>
          </table></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Contatos:</strong> [contatos_adicionar]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><table width="100%" border="0" cellspacing="1" cellpadding="3">
          <tr>
            <td class="txtAzulEscuro11" id="condominios_contatos">[condominios_contatos]</td>
          </tr>
        </table></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Data de Cadastro:</strong></td>
        <td>[data_cadastro]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Data de Ativação:</strong></td>
        <td>[data_ativacao]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Responsável:</strong></td>
        <td>[responsavel]</td>
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
</form>

<script type="text/javascript">
  var condominio_id = [condominio_id];
</script>
