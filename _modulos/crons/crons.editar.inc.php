<script type="text/javascript" src="rds.js.php?m=crons"></script>
<form name="editar" method="POST" action="[action]" onSubmit="return validaCron(this);">
    <table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
        <tr>
            <td>
                <table width="100%" border="0" cellspacing="1" cellpadding="3">
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td width="30%"><b>&nbsp;Classe:</b></td>
                        <td><input type="text" name="cro_classe" id="cro_classe" class="inputBrancoAzulEscuro11" value="[cro_classe]" style="width:350px" maxlength="35"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td width="30%"><b>&nbsp;Ação:</b></td>
                        <td><input type="text" name="cro_acao" id="cro_acao" class="inputBrancoAzulEscuro11" value="[cro_acao]" style="width:350px" maxlength="35"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Dias da semana:</b></td>
                        <td><input type="text" name="cro_dias_semana" id="cro_dias_semana" class="inputBrancoAzulEscuro11" value="[cro_dias_semana]" style="width:350px" maxlength="20"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Dias do mês:</b></td>
                        <td><input type="text" name="cro_dias_mes" id="cro_dias_mes" class="inputBrancoAzulEscuro11" value="[cro_dias_mes]" style="width:350px" maxlength="20"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Intervalo (minutos):</b></td>
                        <td><input type="text" name="cro_intervalo_minutos" id="cro_intervalo_minutos" class="inputBrancoAzulEscuro11" value="[cro_intervalo_minutos]" style="width:350px" maxlength="5"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Última execução real:</b></td>
                        <td><input type="datetime-local" name="cro_ultima_execucao_real" id="cro_ultima_execucao_real" class="inputBrancoAzulEscuro11" value="[cro_ultima_execucao_real]" style="width:350px" maxlength="60"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Última execução início:</b></td>
                        <td><input type="datetime-local" name="cro_ultima_execucao_inicio" id="cro_ultima_execucao_inicio" class="inputBrancoAzulEscuro11" value="[cro_ultima_execucao_inicio]" style="width:350px" maxlength="60"></td>
                    </tr>
                    <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
                        <td><b>&nbsp;Última execução término:</b></td>
                        <td><input type="datetime-local" name="cro_ultima_execucao_termino" id="cro_ultima_execucao_termino" class="inputBrancoAzulEscuro11" value="[cro_ultima_execucao_termino]" style="width:350px" maxlength="60"></td>
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
