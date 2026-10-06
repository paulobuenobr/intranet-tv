<script type="text/javascript" src="rds.js.php?m=contratos"></script>
<script type="text/javascript" src="rds.js.php?m=cancelamentos"></script>
<table border="0" cellspacing="1" cellpadding="3" width="100%">
  <tr align="center" class="txtBranco11"><td bgcolor="#505050"><b>&nbsp;Adicionar&nbsp;</b></td></tr>
</table>
<form name="adicionar" method="POST" onSubmit="return validaCancelamentoAdicionar(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Cliente:</strong></td>
        <td width="80%">[cliente]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Endereço:</b></td>
        <td>[endereco]</td>
      </tr>
      <tbody style="display:[mensagem_inadimplente]">
        <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td colspan="2" style="color:#FF0000;text-align:center"><br>
            Atenção!<br>
            Este cliente possui [cobrancas] cobrança(s) em atraso e o cancelamento / alteração<br>
            de seus contratos só será possível após a quitação destes débitos.<br>
            <br>
          </td>
        </tr>
      </tbody>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Origem:</strong></td>
        <td>[etapa_conteudo_1_comentario]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Nível de Stress:</strong></td>
        <td>[etapa_nivel]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Contratos:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[contratos]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Comentário:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><textarea name="etapa_conteudo_1_comentario" id="etapa_conteudo_1_comentario" class="textBrancoAzulEscuro11" rows="5" cols="75" style="width: 100%"></textarea></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
