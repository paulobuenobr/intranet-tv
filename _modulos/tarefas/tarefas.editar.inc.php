<form name="editar" method="POST" onSubmit="return validaTarefaEditar(this);" action="[action]" enctype="multipart/form-data">
<script type="text/javascript" src="rds.js.php?m=tarefas"></script>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td><table border="0" cellspacing="1" cellpadding="3" width="100%">
      <tr align="center" class="txtBranco11"><td bgcolor="#505050"><strong>&nbsp;Editar&nbsp;</strong></td></tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Protocolo:</strong></td>
        <td><div style="width:630px">[protocolo]</div></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Tipo:</strong></td>
        <td>[tipo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td width="20%"><strong>&nbsp;Título:</strong></td>
        <td>[titulo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Responsável:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Setor:</strong></td>
        <td>[setor]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Função:</strong></td>
        <td>[funcao]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Usuário:</strong></td>
        <td>[usuario]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Datas:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Abertura:</strong></td>
        <td>[abertura]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Prazo:</strong></td>
        <td>[prazo]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Previsão:</strong></td>
        <td>[previsao]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Fechamento:</strong></td>
        <td>[fechamento]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Andamento:</strong></td>
        <td>[andamento]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Etapas:</strong>
          <input type="radio" id="tipo_1" name="tipo_etapa" value="1" onclick="buscarEtapas([id])" checked><label for="tipo_1">Resumida</label>&nbsp;
          <input type="radio" id="tipo_2" name="tipo_etapa" value="2" onclick="buscarEtapas([id])"><label for="tipo_2">Detalhada</label>&nbsp;
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td colspan="2" id="etapas"></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Nova Etapa:</strong></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Origem:</strong></td>
        <td><select name="etapa_origem" id="etapa_origem" class="inputBrancoAzulEscuro11" style="width:100%" onChange="mudaTarefaEtapaOrigem(this)" disabled></select></td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Tipo:</strong></td>
        <td><select name="etapa_tipo" id="etapa_tipo" class="inputBrancoAzulEscuro11" style="width:100%" onChange="mudaTarefaEtapaTipo(this)" disabled></select></td>
      </tr>
      <tr id="etapa_conteudo_0" bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Conteúdo:</strong></td>
        <td></td>
      </tr>
      <tr id="etapa_conteudo_2" style="display:none" bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Conteúdo:</strong></td>
        <td>
          <strong>Tipo:</strong><br>
          <select name="tipo" id="tipo" class="inputBrancoAzulEscuro11" style="width:600" disabled></select>
          <strong>Registrar novo Tipo:</strong><br>
          <input name="novo_tipo_nome" id="novo_tipo_nome" type="text" class="inputBrancoAzulEscuro11" size="50" style="width: 70%"> <select name="novo_tipo_autoaprovado" id="novo_tipo_autoaprovado" class="inputBrancoAzulEscuro11" style="width: 29%"><option></option><option value="1">Aprovação Automática</option><option value="0">Aprovação Manual</option></select>
        </td>
      </tr>
      <tr id="etapa_conteudo_4" style="display:none" bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Conteúdo:</strong></td>
        <td>
          <strong>Início:</strong><br>
          <input name="prazo_inicio" id="prazo_inicio" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="21" style="width:100%;cursor:pointer" onClick="carregaTarefaCalendarioNavegacao(this.name,this.form.etapa_origem.value,this.value);" readonly><br>
          <strong>Término:</strong><br>
          <input name="prazo_termino" id="prazo_termino" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="21" style="width:100%;cursor:pointer" onClick="carregaTarefaCalendarioNavegacao(this.name,this.form.etapa_origem.value,this.value);" readonly><br>
        </td>
      </tr>
      <tr id="etapa_conteudo_5" style="display:none" bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Conteúdo:</strong></td>
        <td>
          <strong>Andamento:</strong><br>
          <select name="andamento" id="andamento" class="inputBrancoAzulEscuro11" style="width:100%">[etapa_conteudo_andamento]</select>
        </td>
      </tr>
      <tr id="etapa_conteudo_7" style="display:none" bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Conteúdo:</strong></td>
        <td>
          <strong>Início:</strong><br>
          <input name="previsao_inicio" id="previsao_inicio" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="21" style="width:100%;cursor:pointer" onClick="carregaTarefaCalendarioNavegacao(this.name,this.form.etapa_origem.value,this.value);" readonly><br>
          <strong>Término:</strong><br>
          <input name="previsao_termino" id="previsao_termino" type="text" class="inputBrancoAzulEscuro11" size="50" maxlength="21" style="width:100%;cursor:pointer" onClick="carregaTarefaCalendarioNavegacao(this.name,this.form.etapa_origem.value,this.value);" readonly><br>
        </td>
      </tr>
      <tr id="etapa_conteudo_8" style="display:none" bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Conteúdo:</strong></td>
        <td>
          <strong>Conteúdo:</strong><br>
          <textarea name="conteudo" id="conteudo" class="textBrancoAzulEscuro11" rows="4" cols="50" style="width:100%"></textarea>
        </td>
      </tr>
      <tr id="etapa_conteudo_9" style="display:none" bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Conteúdo:</strong></td>
        <td>
          <strong>Arquivo:</strong><br>
          <input name="arquivo" id="arquivo" type="file" class="inputBrancoAzulEscuro11" size="110" style="width:100%">
          <strong>Comentário:</strong><br>
          <textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="4" cols="50" style="width:100%"></textarea>
        </td>
      </tr>
      <tr id="etapa_conteudo_11" style="display:none" bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;&raquo; Conteúdo:</strong></td>
        <td>
          <strong>Comentário:</strong><br>
          <textarea name="comentario" id="comentario" class="textBrancoAzulEscuro11" rows="4" cols="50" style="width:100%"></textarea>
        </td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td><strong>&nbsp;Status:</strong></td>
        <td>[status]</td>
      </tr>
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="40" colspan="2" align="center">[botoes]</td>
      </tr>
    </table></td>
  </tr>
</table>
</form>

<script type="text/javascript">
  var tarefa_id = [id];
  buscarTarefaEtapaOrigens([id]);
  buscarEtapas([id]);
</script>
