<script type="text/javascript" src="rds.js.php?m=cidades"></script>
<form name="editar" method="POST" action="[action]" onSubmit="return validaCidade(this);">
    <table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
        <tr>
            <td>
                <table width="100%" border="0" cellspacing="1" cellpadding="3">
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td width="30%"><b>&nbsp;Nome:</b></td>
                        <td><input type="text" name="cid_nome" id="cid_nome" class="inputBrancoAzulEscuro11" value="[cid_nome]" style="width:350px" maxlength="64"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Estado:</b></td>
                        <td>[est_id]</td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;IBGE:</b></td>
                        <td><input type="text" name="cid_ibge" id="cid_ibge" class="inputBrancoAzulEscuro11" value="[cid_ibge]" style="width:350px" maxlength="10"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;TOM:</b></td>
                        <td><input type="text" name="cid_tom" id="cid_tom" class="inputBrancoAzulEscuro11" value="[cid_tom]" style="width:350px" maxlength="10"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;CEP geral:</b></td>
                        <td><input type="text" name="cid_cep_geral" id="cid_cep_geral" class="inputBrancoAzulEscuro11" value="[cid_cep_geral]" style="width:350px" minlength="8" maxlength="8"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Origem:</b></td>
                        <td>[clo_id]</td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td height="40" colspan="2" align="center">[botoes]</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>
