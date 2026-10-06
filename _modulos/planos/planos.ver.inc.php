<script type="text/javascript" src="rds.js.php?m=descontos_concessoes"></script>
<table border="0" cellpadding="0" cellspacing="0" align="center" width="100%">
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="30%"><b>&nbsp;Nome:</b></td>
        <td>[nome]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Serviço:</b></td>
        <td>[servico]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Tipo:</b></td>
        <td>[tipo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" id="aplicavel_linha" style="display:[aplicavel_display]">
        <td><b>&nbsp;Aplicável à:</b></td>
        <td>[aplicavel]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Sistema:</b></td>
        <td>[sistema]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Ajuste:</b></td>
        <td>[ajuste_categoria]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Condomínio:</b></td>
        <td>[condominio]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Origens:</strong></td>
        <td>[origens]</td>
      </tr>

      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;NFS-e:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Gerar NFS-e:</b></td>
        <td>[gerar_nfse]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Código de serviço:</b></td>
        <td>[codigo_servico]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; CNAE:</b></td>
        <td>[cnae]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; ISS Retido:</b></td>
        <td>[issRetido]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Alíquota:</b></td>
        <td>[aliquota]</td>
      </tr>

      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;Parceiro:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Tipo de Limitação:</strong></td>
        <td>[tipo_limitacao]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Plano:</b></td>
        <td>[parceiro_plano]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Máximo:</b></td>
        <td>[parceiro_max]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Valor Máximo:</b></td>
        <td>[valor_max]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2"><b>&nbsp;&raquo; Planos/Valores Vinculados:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="planos_limitacao_listagem">[planos_limitacao_listagem]</td>
      </tr>

      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;Valores:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Instalação:</b></td>
        <td>[instalacao]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Mensalidade:</b></td>
        <td>[mensalidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Periodicidade:</b></td>
        <td>[periodicidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Parcelamento Máximo:</b></td>
        <td>[parcelamento_maximo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Formas de Pagamento (Instalação):</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="formas_pagamento_listagem">[formas_pagamento_listagem]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display: [mostrar_multiservice]">
        <td height="30" colspan="2"><b>&nbsp;Multiservice:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" style="display: [mostrar_multiservice]">
        <td colspan="2" id="multiservice_listagem">[multiservice_listagem]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;IPTV:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Pacotes:</b></td>
        <td>[iptv_pacotes]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; STBs:</b></td>
        <td>[iptv_stb_quantidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Aplicativos:</b></td>
        <td>[iptv_aplicativos_quantidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Gravação (horas):</b></td>
        <td>[iptv_gravacao_horas]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Streaming:</b></td>
        <td>[iptv_streaming]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Apps:</b></td>
        <td>[iptv_apps]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;VOD:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Pacotes:</b></td>
        <td>[vod_pacotes]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Telas:</b></td>
        <td>[vod_telas]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;Pontos:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Quantidade:</b></td>
        <td>[pontos_quantidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Velocidade Download:</b></td>
        <td>[pontos_velocidade_download]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Velocidade Upload:</b></td>
        <td>[pontos_velocidade_upload]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;E-Mails:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Quantidade:</b></td>
        <td>[emails_quantidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Capacidade:</b></td>
        <td>[emails_capacidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;Hospedagens:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Quantidade:</b></td>
        <td>[hospedagens_quantidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Capacidade:</b></td>
        <td>[hospedagens_capacidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Transferência:</b></td>
        <td>[hospedagens_transferencia]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;Base de Dados:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Quantidade:</b></td>
        <td>[basesdedados_quantidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;&raquo; Capacidade:</b></td>
        <td>[basesdedados_capacidade]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Olé Total:</b></td>
        <td>[ole_total]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Equipamentos em Comodato:</b></td>
        <td id="equipamentos_comodatos">[equipamentos_comodatos]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Valor Comodato:</b></td>
        <td>[valor_comodato]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Opções:</b></td>
        <td>[aplicacoes]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Tipo de Contrato:</b></td>
        <td>[tipo_contrato]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Descontos:</b> [descontos_adicionar]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="descontos_plano">[descontos_plano]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><b>&nbsp;Vínculos:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="vinculos_listagem">[vinculos_listagem]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" height="30"><b>&nbsp;Comissões:</b></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2">[comissoes]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Premiação &raquo; Pontos:</b></td>
        <td>[premiacao_pontos]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><b>&nbsp;Status:</b></td>
        <td>[status]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
