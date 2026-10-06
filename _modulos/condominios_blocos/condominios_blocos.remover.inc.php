<form name="remover" method="POST" action="[action]">
<table width="100%" border="0" cellspacing="1" cellpadding="3">
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center"><strong>Atenção:</strong></td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td align="center">
			<p>Certifique-se que não existam Prospects ou Clientes cadastrados no bloco "<b>[nome]</b>" do condomínio "<b id="condominio_nome">[condominio]</b>". Por questões de segurança, a remoção de um Bloco é permitida somente após o preenchimento destas condições.</p>
    </td>
  </tr>
  <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
    <td height="40" align="center">[botoes]</td>
  </tr>
</table>
</form>

<script type="text/javascript">
  if (!document.getElementById("condominio_nome").innerHTML && window.opener.document.adicionar) {
    document.getElementById("condominio_nome").innerHTML = window.opener.document.getElementById("nome").value;
  }
</script>
