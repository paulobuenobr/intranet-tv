<form action="./?m=voip&a=login" name="login" method="POST" onsubmit="return validaVoipLogin(this)">
  <table border="0" cellspacing="1" cellpadding="3" align="center" width="320">
    <tr>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="2" style="font-size:14px;font-weight:bold;text-align:center">Login</td>
    </tr>
    <tr>
      <td colspan="2" style="text-align:justify">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="2">
        <p style="text-align:justify">Caro cliente,</p>
        <p style="text-align:justify">Para visualizar seu extrato de chamadas, preencha os campos abaixo e clique em Continuar.</p>
      </td>
    </tr>
    <tr>
      <td colspan="2">[mensagem]</td>
    </tr>
    <tr>
      <td width="100">&raquo; Usuário:</td>
      <td width="320"><input name="usuario" id="usuario" maxlength="20" type="text" class="formInput" style="width:100%"></td>
    </tr>
    <tr>
      <td>&raquo; Senha:</td>
      <td><input name="senha" id="senha" maxlength="32" type="password" class="formInput" style="width:100%"></td>
    </tr>
    <tr>
      <td colspan="2" style="text-align:justify">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="2" align="center"><input type="submit" class="formButton" value="Continuar" style="width:75px">
    </tr>
    <tr>
      <td colspan="2" style="text-align:justify">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="2">
        <p>Em caso de dúvidas, entre em contato através do telefone <b>(48) 3381-0800</b>.</p>
        <p>Atenciosamente,</p>
        <p>Wave System Internet</p>
      </td>
    </tr>
    <tr>
	    <td colspan="2">&nbsp;</td>
    </tr>
  </table>
</form>
<script type="text/javascript">
  if (document.login) document.login.usuario.focus();
</script>
