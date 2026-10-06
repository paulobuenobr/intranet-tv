<script type="text/javascript" src="rds.js.php?m=catalogos_tvs" id="catalogos_tvs.js"></script>
<form name="adicionar" method="POST" action="[action]" onSubmit="return validaCatalogoTv(this);">
    <table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
        <tr>
            <td>
                <table width="100%" border="0" cellspacing="1" cellpadding="3">
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td width="30%"><b>&nbsp;Código:</b></td>
                        <td><input type="text" name="codigo" id="codigo" class="inputBrancoAzulEscuro11" style="width:350px" maxlength="150"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Nome:</b></td>
                        <td><input type="text" name="nome" id="nome" class="inputBrancoAzulEscuro11" style="width:350px" maxlength="150"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Nome Catálogo:</b></td>
                        <td><input type="text" name="nome_catalogo" id="nome_catalogo" class="inputBrancoAzulEscuro11" style="width:350px" maxlength="45"></td>
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
