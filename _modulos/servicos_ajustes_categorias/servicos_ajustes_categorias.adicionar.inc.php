<form name="adicionar" method="post" onSubmit="return validaCategoria(this)" action="[action]">
<script type="text/javascript" src="rds.js.php?m=servicos_ajustes_categorias"></script>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Adicionar&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
   <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="15%"><strong>&nbsp;Descrição:</strong></td>
        <td width="85%"><input name="descricao" id="descricao" type="text" class="inputBrancoAzulEscuro11" style="width:100%" size="8"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Regras:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="listagem_regras">[listagem_regras]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Duração:</b></td>
        <td><input name="duracao" id="duracao" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataNumero(this, event)" onBlur="return formataNumero(this, event)" style="width:100px"> dia(s)</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Valor:</b></td>
        <td><input name="valor" id="valor" type="text" class="inputBrancoAzulEscuro11" onKeyPress="return formataDinheiro(this, event)" onBlur="return formataDinheiro(this, event)" style="width:100px"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
         <td><strong>&nbsp;Editável:</strong></td>
        <td>[editavel]</td>
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
<input type="hidden" name="lista_regras" id="lista_regras">
</form>
