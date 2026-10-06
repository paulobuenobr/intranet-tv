<script type="text/javascript" src="rds.js.php?m=clientes_autenticacoes"></script>
<form name="editar" method="POST" onSubmit="return validaErroVerificacao(this)" action="[action]">
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Alterar POP&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Cliente:</strong></td>
        <td width="80%" id="cliente">[cliente_nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" style="color:red; padding: 15px 0; text-align: center">
          <b>Atenção:</b><br />
          O ponto deste cliente não obteve os padrões mínimos de qualidade.<br />
          Para prosseguir, preencha o questionário abaixo, que será enviado
          para análise técnica.
        </td>
      </tr>
      
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>Técnico</b></td>
        <td>[tecnico]</td>
      </tr>
      
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Pergunta 1 &raquo; Testou com um equipamento de maior potência?</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">
        	<label><input type="radio" name="pergunta_1" id="pergunta_1_1" value="Sim"> Sim</label><br>
        	<label><input type="radio" name="pergunta_1" id="pergunta_1_2" value="Não"> Não</label>
        </td>
      </tr>
      
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Pergunta 2 &raquo; Se não testou com um equipamento melhor, por que não?</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">
        	<label><input type="radio" name="pergunta_2" id="pergunta_2_1" value="Não tinha um"> Não tinha um</label><br>
        	<label><input type="radio" name="pergunta_2" id="pergunta_2_2" value="O que tinha ia para outro cliente"> O que tinha ia para outro cliente</label><br>
        	<label><input type="radio" name="pergunta_2" id="pergunta_2_3" value="Estava com defeito"> Estava com defeito</label>
        </td>
      </tr>
      
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Pergunta 3 &raquo; Que tipo de equipamento será necessário para este cliente?</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
      	<td colspan="2">
        	<label><input type="radio" name="pergunta_3" id="pergunta_3_1" value="Nano Station 5"> Nano Station 5</label><br>
        	<label><input type="radio" name="pergunta_3" id="pergunta_3_2" value="Bullet5"> Bullet5</label><br>
        	<label><input type="radio" name="pergunta_3" id="pergunta_3_3" value="Nano Station M5"> Nano Station M5</label><br>
        	<label><input type="radio" name="pergunta_3" id="pergunta_3_4" value="Bullet M5"> Bullet M5</label><br>
        	<label><input type="radio" name="pergunta_3" id="pergunta_3_5" value="AirGrid 23dB"> AirGrid 23dB</label><br>
        	<label><input type="radio" name="pergunta_3" id="pergunta_3_6" value="AirGrid 27dB"> AirGrid 27dB</label>
				</td>
      </tr>
      
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Pergunta 4 &raquo; Há algum tipo de problema com a visada para o POP?</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">
          <textarea name="pergunta_4" cols="40" rows="2" class="textBrancoAzulEscuro11" style="width:100%"></textarea>
        </td>
      </tr>
      
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Pergunta 5 &raquo; Tem visada para algum outro POP?</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">
          <textarea name="pergunta_5" cols="40" rows="2" class="textBrancoAzulEscuro11" style="width:100%"></textarea>
        </td>
      </tr>
      
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">
        	[botoes]
        </td>
      </tr>
    </table></td>
  </tr>
</table>
</form>
