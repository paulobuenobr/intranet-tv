<form name="editar" method="POST" action="[action]">
<script type="text/javascript" src="rds.js.php?m=vendas_comissoes"></script>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td>
      <table width="100%" border="0" cellspacing="1" cellpadding="3">
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td width="25%"><strong>&nbsp;Cliente:</strong></td>
          <td width="75%">[cliente]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;Endereço:</strong></td>
          <td>[endereco]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" height="30"><strong>&nbsp;Dados Venda:</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Contrato:</strong></td>
          <td>[contrato]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Serviço / Plano:</strong></td>
          <td>[plano]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Mensalidade:</strong></td>
          <td>[mensalidade]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Instalação:</strong></td>
          <td>[instalacao]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Pontuação:</strong></td>
          <td><input name="pontuacao" id="pontuacao" type="text" class="inputBrancoAzulEscuro11" value="[pontuacao]" onKeyPress="return formataNumeroReal(this,event);" onBlur="return formataNumeroReal(this,event);"></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Vendedor:</strong></td>
          <td>[vendedor]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" height="30"><strong>&nbsp;Comissões:</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" id="comissoes_listagem">[comissoes]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" height="30"><strong>&nbsp;Datas:</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Fechamento</strong></td>
          <td>[data_fechamento]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td><strong>&nbsp;&raquo; Ativação</strong></td>
          <td>[data_ativacao]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2"><strong>&nbsp;Comentários:</strong></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2"><textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="3" cols="75" style="width:100%"></textarea></td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2">[comentarios]</td>
        </tr>
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="40" colspan="2" align="center">[botoes]</td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>

<script type="text/javascript">
  if (window.opener.buscarVendas) {
    window.opener.buscarVendas([venda_id])
  }
</script>
