<script type="text/javascript" src="rds.js.php?m=clientes_origens"></script>
<script type="text/javascript" src="rds.js.php?m=ceps"></script>
<script type="text/javascript" src="rds.js.php?m=enderecos"></script>
<script type="text/javascript" src="js/sha1.js"></script>
<form name="editar" method="POST" action="[action]" enctype="multipart/form-data" onSubmit="return validaClienteOrigem(this)">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td>[acoes]</td>
  </tr>
  <tr>
    <td>
    <table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="100"><b>&nbsp;Nome:</b></td>
        <td><input type="text" name="nome" id="nome" class="inputBrancoAzulEscuro11" value="[nome]" style="width:100%" maxlength="64"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="100"><b>&nbsp;Razão Social:</b></td>
        <td><input type="text" name="razao_social" id="razao_social" class="inputBrancoAzulEscuro11" value="[razao_social]" style="width:100%" maxlength="120"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="100"><b>&nbsp;CNPJ:</b></td>
        <td><input name="cnpj" id="cnpj" type="text" style="width:250px" class="inputBrancoAzulEscuro11" maxlength="20" onKeyPress="return formataCNPJ(this, event)" onBlur="return formataCNPJ(this, event)" value="[cnpj]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Endereço:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; CEP: </strong></td>
        <td><input name="cep" id="cep" type="text" style="width:250px" class="inputBrancoAzulEscuro11" maxlength="9" onKeyPress="return formataCEP(this, event)" onBlur="return formataCEP(this, event)" value="[cep]"> [adicionar_cep] [pesquisar_cep]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Estado:</strong></td>
        <td><input name="estado" id="estado" type="text" style="width:250px" class="inputBrancoAzulEscuro11" value="[estado]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Cidade:</strong></td>
        <td><input name="cidade" id="cidade" type="text" style="width:250px" class="inputBrancoAzulEscuro11" value="[cidade]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Bairro:</strong></td>
        <td><input name="bairro" id="bairro" type="text" style="width:250px" class="inputBrancoAzulEscuro11" value="[bairro]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Logradouro:</strong></td>
        <td><input name="logradouro" id="logradouro" type="text" style="width:400px" class="inputBrancoAzulEscuro11" value="[logradouro]" disabled></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Número:</strong></td>
        <td><input name="numero" id="numero" type="text" style="width:400px" class="inputBrancoAzulEscuro11" maxlength="20" value="[numero]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Complemento:</strong></td>
        <td><input name="complemento" id="complemento" type="text" style="width:400px" class="inputBrancoAzulEscuro11" maxlength="80" value="[complemento]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Contatos:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Telefone:</strong></td>
        <td><input name="telefone" id="telefone" type="text" style="width:400px" class="inputBrancoAzulEscuro11" maxlength="15" value="[telefone]" onKeyPress="return formataTelefone(this, event)" onBlur="return formataTelefone(this, event)"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; E-mail:</strong></td>
        <td><input name="email" id="email" type="text" style="width:400px" class="inputBrancoAzulEscuro11" maxlength="100" value="[email]"></td>
      </tr>

      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;API:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Chave: </strong></td>
        <td><input name="apihash" id="apihash" type="hidden" value="[apihash]">
            <input name="apikey" id="apikey" type="text" style="width:400px" class="inputBrancoAzulEscuro11" maxlength="9" disabled value="[apikey]"> [gerar_key]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Login:</strong></td>
        <td><input name="apilogin" id="apilogin" type="text" style="width:250px" class="inputBrancoAzulEscuro11" value="[apilogin]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Senha:</strong></td>
        <td><input name="apipass" id="apipass" type="text" style="width:250px" class="inputBrancoAzulEscuro11" value="[apipass]"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; ERP:</strong></td>
        <td><input name="erp" id="erp" type="text" style="width:250px" class="inputBrancoAzulEscuro11" value="[erp]"></td>
      </tr>

      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Logo:</strong></td>
        <td>
          <div style="float: left;">
            <input name="logo" id="logo" type="file" style="width:250px; height: 30px;float: left;" class="inputBrancoAzulEscuro11" accept="image/*">
            
            [logo_preview]

            <div style="margin-top: 0;float: left;[remover_logo_disabled]">
              <input type="checkbox" name="remover_logo" id="remover_logo" value="1" onclick="toggleRemoverLogo(this)" /> <label for="remover_logo">Remover logo?</label>
            </div>
          </div>

        </td>
      </tr>

      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Clientes:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Mailings:</b></td>
        <td>[mailings]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Cancelados:</b></td>
        <td>[cancelados]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Prospects:</b></td>
        <td>[prospects]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Ativos:</b></td>
        <td>[ativos]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Cidades:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="cidades_listagem">[cidades_listagem]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[comentarios]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Status:</b></td>
        <td>[status]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
<input type="hidden" name="cidades_lista" id="cidades_lista" value='[cidades_lista]'>
</form>
