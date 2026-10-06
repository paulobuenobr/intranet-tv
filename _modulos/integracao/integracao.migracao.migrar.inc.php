  <form action="?m=migracao&i=[codigo]" name="buscar" method="POST">
    <table border="0" cellspacing="1" cellpadding="3" align="center" width="410">
      <tr>
        <td colspan="2" style="font-size:14px;font-weight:bold;text-align:center">Migração de Plano</td>
      </tr>
      <tr>
        <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2" style="text-align:justify">
          Cliente:
        </td>
      </tr>
      <tr>
        <td colspan="2" style="text-align:justify">
          <b>[cliente]</b>
        </td>
      </tr>
      <tr>
        <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2" style="font-size:14px;font-weight:bold;text-align:center">Plano Atual</td>
      </tr>
      <tr>
        <td colspan="2" style="text-align:justify">
          <table border="0" cellspacing="1" cellpadding="3" align="center" width="100%">
            <tr>
              <td style="background-color:#DDDDDD;font-weight:bold;text-align:center">Velocidade Atual</td>
              <td style="background-color:#DDDDDD;font-weight:bold;text-align:center">Mensalidade Atual</td>
            </tr>
            <tr>
              <td style="background-color:#EEEEEE;font-weight:bold;text-align:center">[velocidade_atual]</td>
              <td style="background-color:#EEEEEE;font-weight:bold;text-align:center">[mensalidade_atual]</td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2" style="font-size:14px;font-weight:bold;text-align:center">Novo Plano</td>
      </tr>
      <tr>
        <td colspan="2" style="text-align:justify">
          <table border="0" cellspacing="1" cellpadding="3" align="center" width="100%">
            <tr>
              <td></td>
              <td style="background-color:#DDDDDD;font-weight:bold;text-align:center">Velocidade</td>
              <td style="background-color:#DDDDDD;font-weight:bold;text-align:center">Mensalidade</td>
            </tr>
            <tr>
              <td style="background-color:#EEEEEE;font-weight:bold;text-align:center"><input type="radio" name="plano" id="plano_1" value="1"></td>
              <td style="background-color:#EEEEEE;font-weight:bold;text-align:center"><label for="plano_1">[velocidade_1]</label></td>
              <td style="background-color:#EEEEEE;font-weight:bold;text-align:center"><label for="plano_1">[mensalidade_1]</label></td>
            </tr>
            <tr>
              <td style="background-color:#EEEEEE;font-weight:bold;text-align:center"><input type="radio" name="plano" id="plano_2" value="2"></td>
              <td style="background-color:#EEEEEE;font-weight:bold;text-align:center"><label for="plano_2">[velocidade_2]</label></td>
              <td style="background-color:#EEEEEE;font-weight:bold;text-align:center"><label for="plano_2">[mensalidade_2]</label></td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2" style="text-align:justify">
          Para efetuar a migração do seu plano, selecione uma das opções acima e clique em Migrar.
        </td>
      </tr>
      <tr>
      	<td colspan="2" align="center">
          <br>
      		<input type="submit" id="migrar" class="formButton" value="Migrar" style="width:75px">
     	</tr>
      <tr>
        <td colspan="2">
          <br>
          Em caso de dúvidas, entre em contato através do telefone <b>(48) 3028-0000</b> ou do e-mail <b>comercial@oletelecom.com.br</b>.<br>
          <br>
          Atenciosamente,<br>
          <br>
          Olé Telecom
        </td>
      </tr>
      <tr>
        <td colspan="2">&nbsp;</td>
      </tr>
    </table>
  </form>
