<script type="text/javascript">
  var etapas_altura = "[etapas_altura]";
</script>

<table width="100%" class="margem" style="[separacao]">
  <tr>
    <td class="margem_td" colspan="4" style="border-bottom: 0px; text-align: left;">
      <table width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
          <td width="100%">
            <table class="quadro" width="100%">
              <tr>
                <td colspan="2" class="titulo"><b>[titulo]</b></td>
              </tr>
              <tr>
                <td width="90"><div style="width:90px"><b>Código:</b></div></td>
                <td width="100%">[codigo]</td>
              </tr>
              <tr>
                <td><b>Solicitante:</b></td>
                <td>[solicitante]</td>
              </tr>
              <tr>
                <td><b>Categoria:</b></td>
                <td>[categoria_nome]</td>
              </tr>
              <tr>
                <td><b>Abertura:</b></td>
                <td>[abertura]</td>
              </tr>
              <tr>
                <td><b>Fechamento:</b></td>
                <td>[fechamento]</td>
              </tr>
            </table>
          </td>
        </tr>
        <tbody id="grupo_dados_cliente">
	        <tr>
	          <td colspan="2" height="10"></td>
	        </tr>
	        <tr>
	          <td colspan="2">
	            <table class="quadro" width="100%">
	              <tr>
	                <td colspan="4" class="titulo"><b>DADOS DO CLIENTE ([cliente_id])</b></td>
	              </tr>
	              <tr>
	                <td width="90"><div style="width:90px"><b>Nome:</b></div></td>
	                <td>[cliente_nome]</td>
	                <td width="10%"><b>CPF/CNPJ:</b></td>
	                <td width="20%">[cliente_cpf_cnpj]</td>
	              </tr>
	              <tr>
	                <td><b>Endereço:</b></td>
	                <td colspan="3">[cliente_endereco_completo]</td>
	              </tr>
	              <tr>
	                <td><b>Ponto de Ref.:</b></td>
	                <td colspan="3">[cliente_ponto_referencia]</td>
	              </tr>
	              <tr>
	                <td><b>Telefone(s):</b></td>
	                <td colspan="3">[cliente_telefones]</td>
	              </tr>
	              <tr>
	                <td><b>E-mail(s):</b></td>
	                <td colspan="3">[cliente_emails]</td>
	              </tr>
	              <tr>
	                <td><b>Serviço(s):</b></td>
	                <td colspan="3">[cliente_servicos]</td>
	              </tr>
	              <tr>
	                <td><b>[cliente_tipo_ponto_acesso]</b></td>
	                <td>[cliente_ponto_de_acesso]</td>
	                <td width="20%"><b>Olé Garantia:</b></td>
	                <td>[cliente_garantia]</td>
	              </tr>
	            </table>
	          </td>
	        </tr>
        </tbody>
        <tbody id="grupo_dados_condominio">
	        <tr>
	          <td colspan="2" height="10"></td>
	        </tr>
	        <tr>
	          <td colspan="2">
	            <table class="quadro">
	              <tr>
	                <td colspan="2" class="titulo"><b>DADOS DO CONDOMÍNIO</b></td>
	              </tr>
	              <tr>
	                <td width="90"><div style="width:90px"><b>Nome:</b></div></td>
	                <td width="100%">[condominio_nome]</td>
	              </tr>
	              <tr>
	                <td><b>Endereço:</b></td>
	                <td>[condominio_endereco]</td>
	              </tr>
	            </table>
	          </td>
	        </tr>
        </tbody>
        <tr>
          <td colspan="2" height="10"></td>
        </tr>
        <tr>
          <td colspan="2" id="etapas_[chamado]"><div id="chamados_etapas" style="height:[etapas_altura];overflow:hidden;border:solid 1px #000000;">[etapas]</div></td>
        </tr>
        <tr>
          <td colspan="2">
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td class="margem_td" style="height:33px;padding:3px;text-align:left" colspan="3">
       Eu ____________________________________, autorizo o técnico responsável a verificar o problema nos meus
       equipamentos, sendo que, se resolvido, será cobrada visita técnica no conforme especificado no campo abaixo,
	   a ser paga na próxima fatura.
    </td>
    <td class="margem_td" style="height:33px;padding:3px;text-align:left;vertical-align:top"><b>Assinatura:</b></td>
  </tr>
  <tr>
    <td class="margem_td" style="height:33px;padding:3px;text-align:left" colspan="3">
      Eu ____________________________________, declaro que os serviços solicitados foram realizados e os equipamentos
      encontram-se em perfeito estado de funcionamento.
    </td>
    <td class="margem_td" style="height:33px;padding:3px;text-align:left;vertical-align:top"><b>Assinatura:</b></td>
  </tr>
  <tr>
    <td class="margem_td" style="width:23%;padding:3px;height:33px;text-align:left;vertical-align:top"><b>Valor:</b></td>
    <td class="margem_td" style="width:23%;padding:3px;height:33px;text-align:left;vertical-align:top"><b>Entrada:</b></td>
    <td class="margem_td" style="width:23%;padding:3px;height:33px;text-align:left;vertical-align:top"><b>Saída:</b></td>
    <td class="margem_td" style="width:31%;padding:3px;height:33px;text-align:left;vertical-align:top"><b>Técnico:</b></td>
  </tr>
</table>
