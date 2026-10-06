<html>
  <head>
    <title>Wave System Internet - Ativação</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<meta http-equiv="Expires" content="Wed, 26 Feb 1997 08:21:57 GMT">
		<meta http-equiv="Cache-Control" content="no-cache, must-revalidate">
		<meta http-equiv="Pragma" content="no-cache">
		<link href="wavesystem.css" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="wavesystem.js"></script>
  </head>
<body alink="White" vlink="White" link="White">

<center><img src="logo_ole.jpg"></center><br>
<form action="logar.php" name="logar" method="POST" onSubmit="return validaFormularioLogin(this)">
<table width="250" border="0" cellspacing="1" cellpadding="3" align="center">
  <tr bgcolor="#777777">
    <td colspan="2"><strong><font class="txtTitulo">LOGIN:</font></strong></td>
  </tr>
  <tr bgcolor="#CCCCCC">
    <td width="20%"><strong><font class="txtNormal">Módulo:</font></strong></td>
    <td width="80%" align="center"><select name="modo" class="formInput" id="modo" style="width:100%">
      <option value="x" selected></option>
<!--
      <option value="administrativo">Administrativo</option>
-->
      <option value="financeiro">Financeiro</option>
      <option value="tecnico">Técnico</option>
    </select></td>
  </tr>
<!--
  <tr bgcolor="#CCCCCC">
    <td width="20%"><strong><font class="txtNormal">Login:</font></strong></td>
    <td width="80%" align="center"><input class="formInput" name="login" type="text" id="login" style="width:100%" maxlength="20"></td>
  </tr>
-->
  <tr bgcolor="#CCCCCC">
    <td width="20%"><strong><font class="txtNormal">Senha:</font></strong></td>
    <td width="80%" align="center"><input class="formInput" name="senha" type="password" id="senha" style="width:100%" maxlength="20"></td>
  </tr>
 <tr bgcolor="#CCCCCC">
  <td colspan="2" align="center"><input name="logar" type="submit" class="formButton" id="logar" value="Logar"><input name="limpar" type="reset" class="formButton" id="limpar" value="Limpar"></td>
 </tr>
</table>
</form>

</body>
</html>
