<script type="text/javascript" src="rds.js.php?m=visitas"></script>
<script type="text/javascript" src="rds.js.php?m=contratos"></script>
<form name="editar" method="POST" onSubmit="return validaVisita(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="25%"><b>&nbsp;[nome_titulo]:</b></td>
        <td width="75%">[nome_valor]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Tipo:</b></td>
        <td>[tipo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Data/Período:</b></td>
        <td>[data_periodo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Vendedor:</b></td>
        <td>[vendedor]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Técnico:</b></td>
        <td>[instalador]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Comentários:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[comentarios]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Novo Comentário:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">
          <b>Categoria:</b><br>
          [comentario_categoria]<br>
          <b>&nbsp;Conteúdo:</b><br>
          <textarea name="comentario_conteudo" id="comentario_conteudo" class="textBrancoAzulEscuro11" rows="5" cols="50" style="width: 100%"></textarea>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Status:</b></td>
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
  var alterado = '[alterado]';
  var cliente = '[cliente_id]';
  if (alterado != '0' && cliente != "" && window.opener && window.opener.buscaVisitas) {
    window.opener.buscaVisitas(cliente);
  }
  if (alterado != '0' && window.opener && window.opener.atualizaVisitas) {
    window.opener.atualizaVisitas('[tipo_id]');
  }
</script>
