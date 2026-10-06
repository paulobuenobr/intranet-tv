<script type="text/javascript" src="rds.js.php?m=servidores_servicos"></script>
<form name="editar" method="POST" onSubmit="return validaServico(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Editar&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
   <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Servidor:</strong></td>
        <td>[servidor_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
         <td><strong>&nbsp;Serviço:</strong></td>
        <td>[servicos]</td>
      </tr>
	    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
  	    <td height="30" colspan="2"><strong>&nbsp;Parâmetros:</strong></td>
	    </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><table width="100%" border="0" cellspacing="1" cellpadding="3">
            <tr>
              <td class="txtAzulEscuro11" id="parametros"></td>
            </tr>
          </table></td>
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
<input type="hidden" name="servicos" id="servicos" value="[servico]">
</form>

<script type="text/javascript">

  if (window.opener && window.opener.buscaServidoresServicos) {
  	window.opener.buscaServidoresServicos([sei_id]);
  }

</script>

<div id="parametros_1" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
	<table width="100%" border="0" cellspacing="1" cellpadding="3" bgcolor="#505050">
    <tr bgcolor="#505050">
      <td class="txtBranco11" align="center" width="30%"><b>Nome</b></td>
      <td class="txtBranco11" align="center" width="70%"><b>Valor</b></td>
    </tr>
    <tr>
 	  	<td bgcolor="#E8E8E8" class="txtAzulEscuro11">&nbsp;<strong>Publicar:</strong></td>
  		<td bgcolor="#E8E8E8" class="txtAzulEscuro11"><input name="parametros_1[publicar]" id="publicar" value="[parametro_1_publicar]" type="text" class="inputBrancoAzulEscuro11" size="50" style="width:305px"></td>
  	</tr>
  	<tr>
	 		<td bgcolor="#E8E8E8" class="txtAzulEscuro11">&nbsp;<strong>Reiniciar:</strong></td>
  		<td bgcolor="#E8E8E8" class="txtAzulEscuro11"><input name="parametros_1[reiniciar]" id="reiniciar" value="[parametro_1_reiniciar]" type="text" class="inputBrancoAzulEscuro11" size="50" style="width:305px"></td>
  	</tr>
  	<tr>
	 		<td bgcolor="#E8E8E8" class="txtAzulEscuro11">&nbsp;<strong>Publicar e Reiniciar:</strong></td>
  		<td bgcolor="#E8E8E8" class="txtAzulEscuro11"><input name="parametros_1[publicar_reiniciar]" id="publicar_reiniciar" value="[parametro_1_publicar_reiniciar]" type="text" class="inputBrancoAzulEscuro11" size="50" style="width:305px"></td>
  	</tr>
	</table>
</div>

<div id="parametros_2" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
	<table width="100%" border="0" cellspacing="1" cellpadding="3" bgcolor="#505050">
    <tr bgcolor="#505050">
      <td class="txtBranco11" align="center" width="30%"><b>Nome</b></td>
      <td class="txtBranco11" align="center" width="70%"><b>Valor</b></td>
    </tr>
	 	<tr>
 	  	<td bgcolor="#E8E8E8" class="txtAzulEscuro11">&nbsp;<strong>Publicar:</strong></td>
  		<td bgcolor="#E8E8E8" class="txtAzulEscuro11"><input name="parametros_2[publicar]" id="publicar" value="[parametro_2_publicar]" type="text" class="inputBrancoAzulEscuro11" size="50" style="width:305px"></td>
  	</tr>
  	<tr>
	 		<td bgcolor="#E8E8E8" class="txtAzulEscuro11">&nbsp;<strong>Reiniciar:</strong></td>
  		<td bgcolor="#E8E8E8" class="txtAzulEscuro11"><input name="parametros_2[reiniciar]" id="reiniciar" value="[parametro_2_reiniciar]" type="text" class="inputBrancoAzulEscuro11" size="50" style="width:305px"></td>
  	</tr>
  	<tr>
	 		<td bgcolor="#E8E8E8" class="txtAzulEscuro11">&nbsp;<strong>Publicar e Reiniciar:</strong></td>
  		<td bgcolor="#E8E8E8" class="txtAzulEscuro11"><input name="parametros_2[publicar_reiniciar]" id="publicar_reiniciar" value="[parametro_2_publicar_reiniciar]" type="text" class="inputBrancoAzulEscuro11" size="50" style="width:305px"></td>
  	</tr>
	</table>
</div>

<div id="parametros_3" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
	<table width="100%" border="0" cellspacing="1" cellpadding="3" bgcolor="#505050">
    <tr bgcolor="#505050">
      <td class="txtBranco11" align="center" width="30%"><b>Nome</b></td>
      <td class="txtBranco11" align="center" width="70%"><b>Valor</b></td>
    </tr>
	 	<tr>
 	  	<td bgcolor="#E8E8E8" class="txtAzulEscuro11">&nbsp;<strong>Publicar:</strong></td>
  		<td bgcolor="#E8E8E8" class="txtAzulEscuro11"><input name="parametros_3[instalar]" id="instalar" value="[parametro_3_instalar]" type="text" class="inputBrancoAzulEscuro11" style="width:305px"></td>
  	</tr>
	</table>
</div>

<div id="parametros_8" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
 	Nenhum parâmetro definido
</div>

<div id="parametros_9" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
 	Nenhum parâmetro definido
</div>

<div id="parametros_10" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
 	Nenhum parâmetro definido
</div>

<div id="parametros_12" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
 	Nenhum parâmetro definido
</div>

<div id="parametros_13" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
 	Nenhum parâmetro definido
</div>

<div id="parametros_14" style="position:absolute; left:0; top:0; width:0; height:0; z-index:0; visibility: hidden;">
 	Nenhum parâmetro definido
</div>

<script type="text/javascript">
  mudaServico([servico]);
</script>
