<script type="text/javascript" src="rds.js.php?m=multiservice_servicos"></script>
<form name="editar" method="POST" action="[action]" onSubmit="return validaServico(this);">
    <table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
        <tr>
            <td>
                <table width="100%" border="0" cellspacing="1" cellpadding="3">
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td width="30%"><b>&nbsp;Nome:</b></td>
                        <td><input type="text" name="nome" id="nome" class="inputBrancoAzulEscuro11" value="[nome]" style="width:350px" maxlength="60"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td width="30%"><b>&nbsp;Player:</b></td>
                        <td>[player]</td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td width="30%"><b>&nbsp;Código:</b></td>
                        <td><input type="text" name="codigo" id="codigo" class="inputBrancoAzulEscuro11" value="[codigo]" style="width:350px" maxlength="60"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td width="30%"><b>&nbsp;Descrição:</b></td>
                        <td><textarea name="descricao" id="descricao" class="textBrancoAzulEscuro11" style="width:100%" rows="4" cols="51">[descricao]</textarea></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Data de casdastro:</b></td>
                        <td><input type="text" name="data_criacao" id="data_criacao" class="inputBrancoAzulEscuro11" value="[data_criacao]" style="width:350px" disabled="" maxlength="60"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Status:</b></td>
                        <td>[status]</td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td height="40" colspan="2" align="center">[botoes]</td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</form>