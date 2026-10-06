<script type="text/javascript" src="rds.js.php?m=crons" id="crons.js"></script>
<form name="adicionar" method="POST" action="[action]" onSubmit="return validaCron(this);">
    <table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
        <tr>
            <td>
                <table width="100%" border="0" cellspacing="1" cellpadding="3">
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td width="30%"><b>&nbsp;Classe:</b></td>
                        <td><input type="text" name="cro_classe" id="cro_classe" class="inputBrancoAzulEscuro11" style="width:350px" maxlength="35"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td width="30%"><b>&nbsp;Ação:</b></td>
                        <td><input type="text" name="cro_acao" id="cro_acao" class="inputBrancoAzulEscuro11" style="width:350px" maxlength="35"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Dias da semana:</b></td>
                        <td><input type="text" name="cro_dias_semana" id="cro_dias_semana" class="inputBrancoAzulEscuro11" style="width:350px" maxlength="20"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Dias do mês:</b></td>
                        <td><input type="text" name="cro_dias_mes" id="cro_dias_mes" class="inputBrancoAzulEscuro11" style="width:350px" maxlength="20"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Intervalo (minutos):</b></td>
                        <td><input type="text" name="cro_intervalo_minutos" id="cro_intervalo_minutos" class="inputBrancoAzulEscuro11" style="width:350px" maxlength="5"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Tempo real:</b></td>
                        <td>[cro_tempo_real]</td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Em execução:</b></td>
                        <td>[cro_em_execucao]</td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Ativo:</b></td>
                        <td>[cro_ativo]</td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td height="40" colspan="2" align="center">[botoes]</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>
