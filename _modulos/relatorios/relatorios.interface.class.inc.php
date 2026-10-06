<?

class relatorios_interface {

  var $bancodedados;

  function __construct ($bancodedados) {
    $this->bancodedados = $bancodedados;
  }

  #############
  ### Geral ###
  #############
  function geraBotoes() {
    $botoes[] = '<input type="submit" name="gerar" class="buttonAzulEscuroBranco11" value="Gerar">';
    $botoes[] = '<input type="reset" name="limpar" class="buttonAzulEscuroBranco11" value="Limpar">';
    $botoes[] = '<input type="button" name="voltar" class="buttonAzulEscuroBranco11" value="Voltar" onClick="history.back()">';
    return implode(" ", $botoes);
  }

  function geraSelect($itens, $selecionado = null, $item_nulo = false) {
    $select = $item_nulo ? "<option></option>" : "";
    if ($itens) {
      foreach ($itens as $valor => $item) {
        $select .= "<option value=\"$valor\"".($valor == $selecionado ? " selected" : "").">$item</option>";
      }
    }
    return $select;
  }
  
  function geraSelectMultiple($itens, $selecionado = null, $item_nulo = false) {
    $select = $item_nulo ? "<option></option>" : "";
    $ArraySelecionados = explode(",", $selecionado);
    if ($itens) {
      foreach ($itens as $valor => $item) {
          if (in_array($valor, $ArraySelecionados)) { 
              $select .= '<option value="'.$valor.'" selected>'.$item.'</option>';
          } else {
              $select .= '<option value="'.$valor.'">'.$item.'</option>';
          }
      }
    }
    return $select;
  }
  function geraCheckList($nome, $itens, $selecionados, $status) {
    $i = 1;
    foreach ($itens as $valor => $item) {
      $checkboxes[] = "<input type=\"checkbox\" name=\"{$nome}[]\" id=\"{$nome}_{$i}\" value=\"$valor\"".(in_array($valor, $selecionados) || $status == "checked" ? " checked" : "")." style=\"width:14px;height:14px\">&nbsp;<label for=\"{$nome}_{$i}\">$item</label>";
      $i++;
    }
    return @implode("<br>", $checkboxes);
  }

  function geraCheckBox($campos, $selecionados = "") {
    if ($campos) {
      $checkbox = '<table width="100%" border="0" cellspacing="1" cellpadding="1">';
      $i = 0;
      foreach ($campos as $categoria => $lista_campos) {
        $checkbox .= '<tr class="txtAzulEscuro11"><td colspan="4"><strong>'.$categoria.'</strong></td></tr>';
        $colunas = 0;
        foreach ($lista_campos as $campo) {
          if ($colunas == 0) $checkbox .= '<tr class="txtAzulEscuro11">';
          $checkbox .= '<td width="25%" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr class="txtAzulEscuro11"><td valign="top" width="22"><input name="campos['.$campo["campo"].']" id="campos_'.$i.'" type="checkbox"';
          if ($campo["onclick"]) {
            $checkbox .= ' onclick="'.$campo["onclick"].'"';
          }
          if ($selecionados[$campo["campo"]]) {
            $checkbox .= " checked";
          }
          $checkbox .= '></td><td><label for="campos_'.$i.'"> '.$campo["nome"].'</label></td></tr></table></td>';
          $colunas++;
          if ($colunas == 4) {
            $checkbox .= "</tr>";
            $colunas = 0;
          }
          $i++;
        }
      }
      $checkbox .= '</td></tr></table>';
    }
    return $checkbox;
  }

  function geraCamposTitulos($campos_ativos, $todos_campos) {
    $campos["contador"] = "#";
    for ($i = 0; $i < sizeof($todos_campos); $i++) {
      $agrupamento = current($todos_campos);
      for ($j = 0; $j < sizeof($agrupamento); $j++) {
        while (($campo = current($agrupamento)) != false) {
          if ($campos_ativos && key($campos_ativos) == $campo["campo"]) {
            $campos[key($campos_ativos)] = key($todos_campos)." » ".$campo["nome"];
            next($campos_ativos);
          }
          next($agrupamento);
        }
      }
      next($todos_campos);
    }
    return $campos;
  }

  function geraFiltrosTemplate($filtros, $instancia) {
    if (!$filtros) return "";
    $eval = null;
    $template = '<table width="100%" border="0" cellspacing="1" cellpadding="3">
    		<tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
          <td height="30" colspan="2"><strong>&nbsp;Dados:</strong></td>
        </tr>';
    foreach ($filtros as $filtro) {
      if ($filtro["tipo"] == "select" || $filtro['tipo'] == "selectText") {
        $template .= '
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td width="25%"><strong>&nbsp;&raquo; '.$filtro["nome"].':</strong></td>
            <td><select name="'.$filtro["name"].'" id="'.$filtro["name"].'"'.($filtro["onchange"] ? " onchange=\"{$filtro["onchange"]}\"" : "").' class="inputBrancoAzulEscuro11" style="width: 300px"><option></option>['.$filtro["name"].']</select></td>
          </tr>';
        if (!empty($filtro["funcao"])) {
          $argumento = $_POST[$filtro["name"]];
          eval('$eval = $this->geraSelect($instancia->'.$filtro["funcao"].', "'.$argumento.'", "");');
          $template = str_replace('['.$filtro["name"].']', $eval, $template);
        }
      } elseif ($filtro["tipo"] == "selectMultiple") {
        $name = str_replace("[]", '', $filtro["name"]);
        $template .= '
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td width="25%"><strong>&nbsp;&raquo; '.$filtro["nome"].':</strong></td>
            <td>
                <select multiple name="'. $name .'[]" id="'. $name .'"'.($filtro["onchange"] ? " onchange=\"{$filtro["onchange"]}\"" : "").' class="inputBrancoAzulEscuro11" style="width:300px;float:left;height:100px"><option></option>['. $name .']</select>
                <div style="float:left;padding:10px;width:calc(100% - 300px);box-sizing:border-box">» Para selecionar mais de uma opção:<br/><br/>» Selecionar tudo: CTRL + A<br/><br/>» Selecionar vários: CTRL e clicar na opção</div>                    
            </td>
          </tr>';
        if (!empty($filtro["funcao"])) {
          $argumento = implode(",", $_POST[$name]);
          eval('$eval = $this->geraSelectMultiple($instancia->'.$filtro["funcao"].', "'.$argumento.'", "");');;
          $template = str_replace('['. $name .']', $eval, $template);
        }
      } elseif ($filtro["tipo"] == "selectAoLado") {
        $template .= '
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td width="25%"><strong>&nbsp;&raquo; '.$filtro["nome"].':</strong></td>
            <td><select name="'.$filtro["name1"].'" id="'.$filtro["name1"].'" class="inputBrancoAzulEscuro11" style="width: 230px"><option></option>['.$filtro["name1"].']</select> <select name="'.$filtro["name2"].'" id="'.$filtro["name2"].'" class="inputBrancoAzulEscuro11" style="width: 66px"><option></option>['.$filtro["name2"].']</select></td>
          </tr>';
        if (!empty($filtro["funcao1"])) {
          $argumento = $_POST[$filtro["name1"]];
          if (empty($_POST[$filtro["name1"]])) $argumento = '""';
          eval('$eval = $this->geraSelect($instancia->'.$filtro["funcao1"].','.$argumento.',"");');
          $template = str_replace('['.$filtro["name1"].']', $eval, $template);
        }
        if (!empty($filtro["funcao2"])) {
          $argumento = $_POST[$filtro["name2"]];
          if (empty($_POST[$filtro["name2"]])) $argumento = '""';
          eval('$eval = $this->geraSelect($instancia->'.$filtro["funcao2"].','.$argumento.',"");');
          $template = str_replace('['.$filtro["name2"].']', $eval, $template);
        }
      } elseif ($filtro["tipo"] == "input") {
        $template .= '
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11" height="30">
            <td width="25%"><strong>&nbsp;&raquo; '.$filtro["nome"].':</strong></td>
            <td><input name="'.$filtro["name"].'" id="'.$filtro["name"].'" type="text" class="inputBrancoAzulEscuro11" style="width: 300px" value="['.$filtro["name"].']"'.($filtro["formatacao"] ? " onKeyPress=\"return {$filtro["formatacao"]}\" onBlur=\"return {$filtro["formatacao"]}\"" : "").'></td>
          </tr>';
        $template = str_replace('['.$filtro["name"].']', stripslashes($_POST[$filtro["name"]]), $template);
      } elseif ($filtro["tipo"] == "checklist") {
        $template .= '
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td width="25%"><strong>&nbsp;&raquo; '.$filtro["nome"].':</strong></td>
            <td>['.$filtro["name"].']</td>
          </tr>';
        if (!empty($filtro["funcao"])) {
          if (!$_POST[$filtro["name"]]) {
            $argumento = $filtro["selecionados"] ? implode(", ", $filtro["selecionados"]) : '11, 12, 13, 14, 15';
          } else {
            $argumento = @implode(',', $_POST[$filtro["name"]]);
          }
          $filtro["status"] = $_POST || $filtro["selecionados"] ? null : $filtro["status"];
          eval('$eval = $this->geraCheckList('.$filtro["name"].', $instancia->'.$filtro["funcao"].', array('.$argumento.'), "'.$filtro["status"].'");');
          $template = str_replace('['.$filtro["name"].']', $eval, $template);
        }
      }
    }
    if ($template) {
    	$template .= '
    		</table>';
      return $template;
    }
  }

  function geraPeriodosTemplate($periodos) {
    $template .= '<table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Período:</strong></td>
      </tr>';
    foreach ($periodos as $periodo) {
      if ($periodo["tipo"] == "periodo") {
        $template .= '
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td width="25%"><strong>&nbsp;&raquo; '.$periodo["nome"].':</strong></td>
            <td><input name="'.$periodo["name1"].'" id="'.$periodo["name1"].'" type="text" class="inputBrancoAzulEscuro11" size="7" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="['.$periodo["name1"].']" style="width:80px"> até <input name="'.$periodo["name2"].'" id="'.$periodo["name2"].'" type="text" class="inputBrancoAzulEscuro11" size="7" maxlength="10" onKeyPress="return formataData(this,event);" onBlur="return formataData(this,event);" value="['.$periodo["name2"].']" style="width:80px"> (dd/mm/aaaa)</td>
          </tr>';
        $template = str_replace('['.$periodo["name1"].']', $_POST[$periodo["name1"]], $template);
        $template = str_replace('['.$periodo["name2"].']', $_POST[$periodo["name2"]], $template);
      } elseif ($periodo["tipo"] == "periodoPorMes") {
        $template .= '
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td width="25%"><strong>&nbsp;&raquo; '.$periodo["nome"].':</strong></td>
            <td><input name="'.$periodo["name1"].'" id="'.$periodo["name1"].'" type="text" class="inputBrancoAzulEscuro11" size="7" maxlength="7" onKeyPress="return formataCompetencia(this,event);" onBlur="return formataCompetencia(this,event);" value="['.$periodo["name1"].']" style="width:80px"> até <input name="'.$periodo["name2"].'" id="'.$periodo["name2"].'" type="text" class="inputBrancoAzulEscuro11" size="7" maxlength="7" onKeyPress="return formataCompetencia(this,event);" onBlur="return formataCompetencia(this,event);" value="['.$periodo["name2"].']" style="width:80px"> (mm/aaaa)</td>
          </tr>';
        $template = str_replace('['.$periodo["name1"].']', $_POST[$periodo["name1"]], $template);
        $template = str_replace('['.$periodo["name2"].']', $_POST[$periodo["name2"]], $template);
      } elseif ($periodo["tipo"] == "MesAno") {
        $template .= '
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td width="25%"><strong>&nbsp;&raquo; '.$periodo["nome"].':</strong></td>
            <td><input name="'.$periodo["name"].'" id="'.$periodo["name"].'" type="text" class="inputBrancoAzulEscuro11" size="7" maxlength="7" onKeyPress="return formataCompetencia(this, event)" onBlur="return formataCompetencia(this, event)" value="['.$periodo["name"].']" style="width:80px"> (mm/aaaa)</td>
          </tr>';
        $template = str_replace('['.$periodo["name"].']', $_POST[$periodo["name"]], $template);
      } elseif ($periodo["tipo"] == "hora") {
        $template .= '
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td width="25%"><strong>&nbsp;&raquo; '.$periodo["nome"].':</strong></td>
            <td>
              <select name="'.$periodo["name1"].'" id="'.$periodo["name1"].'" style="width:80px" class="inputBrancoAzulEscuro11"><option></option>'.$this->geraSelect($this->buscaHorasInicio(), $_POST ? $_POST[$periodo["name1"]] : $periodo["padrao1"]).'</select> até
              <select name="'.$periodo["name2"].'" id="'.$periodo["name2"].'" style="width:80px" class="inputBrancoAzulEscuro11"><option></option>'.$this->geraSelect($this->buscaHorasTermino(), $_POST ? $_POST[$periodo["name2"]] : $periodo["padrao2"]).'</select>
            </td>
          </tr>';
        $template = str_replace('['.$periodo["name"].']', $_POST[$periodo["name"]], $template);
      }
    }
    $template .= '
    		</table>';
    return $template;
  }

  function buscaHorasInicio() {
    for ($hora = 0; $hora < 24; $hora++) {
      $horas[sprintf("%02s", $hora).":00"] = sprintf("%02s", $hora).":00";
    }
    return $horas;
  }

  function buscaHorasTermino() {
    for ($hora = 0; $hora < 24; $hora++) {
      $horas[sprintf("%02s", $hora).":59"] = sprintf("%02s", $hora).":59";
    }
    return $horas;
  }

  function geraIntervalosTemplate($intervalos) {
    $template .= '<table width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
        <td height="30" colspan="2"><strong>&nbsp;Intervalos:</strong></td>
      </tr>';
    foreach ($intervalos as $intervalo) {
      if ($intervalo["tipo"] == "dinheiro") {
        $template .= '
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td width="25%"><strong>&nbsp;&raquo; '.$intervalo["nome"].':</strong></td>
            <td><input name="'.$intervalo["name1"].'" id="'.$intervalo["name1"].'" type="text" class="inputBrancoAzulEscuro11" size="7" maxlength="10" value="['.$intervalo["name1"].']" onkeypress="return formataDinheiro(this,event)" onblur="return formataDinheiro(this,event)" style="width:80px"> até <input name="'.$intervalo["name2"].'" id="'.$intervalo["name2"].'" type="text" class="inputBrancoAzulEscuro11" size="7" maxlength="10" value="['.$intervalo["name2"].']" onkeypress="return formataDinheiro(this,event)" onblur="return formataDinheiro(this,event)" style="width:80px"></td>
          </tr>';
        $template = str_replace('['.$intervalo["name1"].']', $_POST[$intervalo["name1"]], $template);
        $template = str_replace('['.$intervalo["name2"].']', $_POST[$intervalo["name2"]], $template);
      } else if ($intervalo["tipo"] == "quantidade") {
        $template .= '
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td width="25%"><strong>&nbsp;&raquo; '.$intervalo["nome"].':</strong></td>
            <td><input name="'.$intervalo["name1"].'" id="'.$intervalo["name1"].'" type="text" class="inputBrancoAzulEscuro11" size="7" maxlength="10" value="['.$intervalo["name1"].']" onkeypress="return formataNumero(this,event)" onblur="return formataNumero(this,event)" style="width:80px"> até <input name="'.$intervalo["name2"].'" id="'.$intervalo["name2"].'" type="text" class="inputBrancoAzulEscuro11" size="7" maxlength="10" value="['.$intervalo["name2"].']" onkeypress="return formataNumero(this,event)" onblur="return formataNumero(this,event)" style="width:80px"></td>
          </tr>';
        $template = str_replace('['.$intervalo["name1"].']', $_POST[$intervalo["name1"]], $template);
        $template = str_replace('['.$intervalo["name2"].']', $_POST[$intervalo["name2"]], $template);
      } else if ($intervalo["tipo"] == "dias") {
        $template .= '
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td width="25%"><strong>&nbsp;&raquo; '.$intervalo["nome"].':</strong></td>
            <td><input name="'.$intervalo["name1"].'" id="'.$intervalo["name1"].'" type="text" class="inputBrancoAzulEscuro11" size="7" maxlength="10" value="['.$intervalo["name1"].']" onkeypress="return formataNumero(this,event)" onblur="return formataNumero(this,event)" style="width:80px"> até <input name="'.$intervalo["name2"].'" id="'.$intervalo["name2"].'" type="text" class="inputBrancoAzulEscuro11" size="7" maxlength="10" value="['.$intervalo["name2"].']" onkeypress="return formataNumero(this,event)" onblur="return formataNumero(this,event)" style="width:80px"></td>
          </tr>';
        $template = str_replace('['.$intervalo["name1"].']', $_POST[$intervalo["name1"]], $template);
        $template = str_replace('['.$intervalo["name2"].']', $_POST[$intervalo["name2"]], $template);
      } else if ($intervalo["tipo"] == "velocidade") {
        $template .= '
          <tr bgcolor="#E8E8E8" class="txtAzulEscuro11">
            <td width="25%"><strong>&nbsp;&raquo; '.$intervalo["nome"].':</strong></td>
            <td><input name="'.$intervalo["name1"].'" id="'.$intervalo["name1"].'" type="text" class="inputBrancoAzulEscuro11" size="7" maxlength="10" value="['.$intervalo["name1"].']" onkeypress="return formataNumero(this,event)" onblur="return formataNumero(this,event)" style="width:80px"> até <input name="'.$intervalo["name2"].'" id="'.$intervalo["name2"].'" type="text" class="inputBrancoAzulEscuro11" size="7" maxlength="10" value="['.$intervalo["name2"].']" onkeypress="return formataNumero(this,event)" onblur="return formataNumero(this,event)" style="width:80px"> (Kbps)</td>
          </tr>';
        $template = str_replace('['.$intervalo["name1"].']', $_POST[$intervalo["name1"]], $template);
        $template = str_replace('['.$intervalo["name2"].']', $_POST[$intervalo["name2"]], $template);
      }
    }
    $template .= '
    		</table>';
    return $template;
  }

  ################
  ### Consulta ###
  ################
  function constroiConsulta($campos, $campos_selecionados, $consulta, $dinamicos, $dados) {
    $consulta = $this->insereRetornos($campos, $campos_selecionados, $consulta);
    $consulta = $this->insereFiltros($campos, $campos_selecionados, $dinamicos, $dados, $consulta);
    return $consulta;
  }

  function insereRetornos($campos, $ativos, $consulta) {
    if ($campos && $ativos) {
      current($ativos);
      $campos_atuais = current($campos);
      $retorno = "";
      for ($i = 0; $i < sizeof($ativos); $i++) {
        if (key($ativos)) {
          for ($j = 0; $j < sizeof($campos); $j++) {
            for ($k = 0; $k < sizeof($campos_atuais); $k++) {
              if (key($ativos) == $campos_atuais[$k]["campo"]) {
                if ($campos_atuais[$k]["tabela"] && strpos($retorno, $campos_atuais[$k]["tabela"]) === false) {
                  $retorno .= ($retorno ? "\n        " : "").$campos_atuais[$k]["tabela"];
                }
                next($ativos);
              }
            }
            $campos_atuais = next($campos);
          }
        }
        next($ativos);
      }
      return str_replace('[campos_retorno]', substr($retorno, 0, -2), $consulta);
    } else {
      return $consulta;
    }
  }

  function insereFiltros($campos, $campos_selecionados, $todos_filtros, $filtros, $consulta) {
    unset ($filtros["campos"], $filtros["exportar"], $filtros["gerar"], $filtros["periodo"]);
    $filtros_restantes = $filtros;
    foreach ($filtros as $chave => $filtro) {
      if (empty($filtros_restantes[$chave])) unset($filtros_restantes[$chave]);
    }
    if ($campos_selecionados) {
      foreach ($campos_selecionados as $campo_selecionado => $valor) {
        if ($campos) {
	      	foreach ($campos as $campos_categoria) {
	          foreach ($campos_categoria as $campo_categoria) {
	            if ($campo_categoria["campo"] == $campo_selecionado) {
	              if ($campo_categoria["joins"]) {
	                if (is_array($campo_categoria["joins"])) {
	                  $joins = funcoes::mescla_arrays($joins, $campo_categoria["joins"]);
	                } else {
	                  $joins[] = $campo_categoria["joins"];
	                }
	              }
	            }
	          }
	        }
        }
      }
    }
    if ($filtros_restantes) {
      foreach ($todos_filtros as $filtro_atual) {
        if ($filtro_atual["tipo"] == "select") {
          if ($filtros_restantes[$filtro_atual["name"]]) {
            $where[] = "(".($filtro_atual["where"] ? $filtro_atual["where"]." = " : "")."{$filtros_restantes[$filtro_atual["name"]]})";
          }
        } elseif ($filtro_atual["tipo"] == "selectMultiple") {
          if ($filtros_restantes[str_replace("[]", '', $filtro_atual["name"])]) {
            $itens = implode(",", array_filter($filtros_restantes[str_replace("[]", '', $filtro_atual["name"])]));
            if($itens) {
                $where[] = "(".$filtro_atual["where"]." IN (". $itens ."))";
            }
          }
        } elseif ($filtro_atual["tipo"] == "checklist") {
          if ($filtros_restantes[$filtro_atual["name"]]) {
            $where[] = "(".$filtro_atual["where"]." IN (".implode(",", $filtros_restantes[$filtro_atual["name"]])."))";
          }
        } elseif ($filtro_atual["tipo"] == "selectAoLado") {
          if ($filtros_restantes[$filtro_atual["name1"]]) {
            $where[] = "(".$filtro_atual["where1"]." = {$filtros_restantes[$filtro_atual["name1"]]})";
          }
          if ($filtros_restantes[$filtro_atual["name2"]]) {
            $where[] = "(".$filtro_atual["where2"]." = {$filtros_restantes[$filtro_atual["name2"]]})";
          }
        } elseif ($filtro_atual["tipo"] == "extra") {
          if ($filtros_restantes[$filtro_atual["name"]]) {
            $where[] = "(".$filtro_atual["where"]." {$filtros_restantes[$filtro_atual["name"]]})";
          }
        } elseif ($filtro_atual["tipo"] == "input") {
          if (!empty($filtros_restantes[$filtro_atual["name"]])) {
            if ($filtro_atual["where"]) {
              $where[] = "(".$filtro_atual["where"]." LIKE '%{$filtros_restantes[$filtro_atual["name"]]}%')";
            } else {
              $where[] = "({$filtros_restantes[$filtro_atual["name"]]})";
            }
          }
        } elseif ($filtro_atual["tipo"] == "selectText") {
          if (!empty($filtros_restantes[$filtro_atual["name"]])) {
            if ($filtro_atual["where"]) {
              $where[] = "(".$filtro_atual["where"]." LIKE '%{$filtros_restantes[$filtro_atual["name"]]}%')";
            } else {
              $where[] = "({$filtros_restantes[$filtro_atual["name"]]})";
            }
          }
        } elseif ($filtro_atual["tipo"] == "periodo") {
          if ($filtro_atual["where"]) {
            if ($filtros_restantes[$filtro_atual["name1"]]) {
              $where[] = "(".$filtro_atual["where"]." >= '".substr($filtros_restantes[$filtro_atual["name1"]], 6, 4)."-".substr($filtros_restantes[$filtro_atual["name1"]], 3, 2)."-".substr($filtros_restantes[$filtro_atual["name1"]], 0, 2)."')";
            }
            if ($filtros_restantes[$filtro_atual["name2"]]) {
              $where[] = "(".$filtro_atual["where"]." <= '".substr($filtros_restantes[$filtro_atual["name2"]], 6, 4)."-".substr($filtros_restantes[$filtro_atual["name2"]], 3, 2)."-".substr($filtros_restantes[$filtro_atual["name2"]], 0, 2)."')";
            }
          }
        } elseif ($filtro_atual["tipo"] == "periodoPorMes") {
          if ($filtros_restantes[$filtro_atual["name1"]]) {
            $where[] = "({$filtro_atual["where"]} >= '".substr($filtros_restantes[$filtro_atual["name1"]], 3, 4)."-".substr($filtros_restantes[$filtro_atual["name1"]], 0, 2)."-01')";
          }
          if ($filtros_restantes[$filtro_atual["name2"]]) {
            $where[] = "({$filtro_atual["where"]} <= '".substr($filtros_restantes[$filtro_atual["name2"]], 3, 4)."-".substr($filtros_restantes[$filtro_atual["name2"]], 0, 2)."-".date('t', strtotime(substr($filtros_restantes[$filtro_atual["name2"]], 3, 4)."-".substr($filtros_restantes[$filtro_atual["name2"]], 0, 2)."-01"))."')";
          }
        } elseif ($filtro_atual["tipo"] == "dinheiro") {
          if ($filtros_restantes[$filtro_atual["name1"]]) {
            $valor = preg_replace("/[^0-9]/", "", $filtros_restantes[$filtro_atual["name1"]]);
            $where[] = "({$filtro_atual["where"]} >= $valor)";
          }
          if ($filtros_restantes[$filtro_atual["name2"]]) {
            $valor = preg_replace("/[^0-9]/", "", $filtros_restantes[$filtro_atual["name2"]]);
            $where[] = "({$filtro_atual["where"]} <= $valor)";
          }
        } elseif ($filtro_atual["tipo"] == "quantidade") {
          if ($filtros_restantes[$filtro_atual["name1"]]) {
            $valor = preg_replace("/[^0-9]/", "", $filtros_restantes[$filtro_atual["name1"]]);
            $where[] = $filtro_atual["where"];
            $having[] = "({$filtro_atual["having"]} >= $valor)";
          }
          if ($filtros_restantes[$filtro_atual["name2"]]) {
            $valor = preg_replace("/[^0-9]/", "", $filtros_restantes[$filtro_atual["name2"]]);
            $where[] = $filtro_atual["where"];
            $having[] = "({$filtro_atual["having"]} <= $valor)";
          }
        } elseif ($filtro_atual["tipo"] == "velocidade") {
          if ($filtros_restantes[$filtro_atual["name1"]]) {
            $valor = intval(preg_replace("/[^0-9]/", "", $filtros_restantes[$filtro_atual["name1"]]), 10) * 1024;
            $where[] = "({$filtro_atual["where"]} >= $valor)";
          }
          if ($filtros_restantes[$filtro_atual["name2"]]) {
            $valor = intval(preg_replace("/[^0-9]/", "", $filtros_restantes[$filtro_atual["name2"]]), 10) * 1024;
            $where[] = "({$filtro_atual["where"]} <= $valor)";
          }
        } elseif ($filtro_atual["tipo"] == "dias") {
          if ($filtros_restantes[$filtro_atual["name1"]] && $filtros_restantes[$filtro_atual["name1"]] == $filtros_restantes[$filtro_atual["name2"]]) {
            $valor = intval(preg_replace("/[^0-9]/", "", $filtros_restantes[$filtro_atual["name1"]]), 10);
            $where[] = "({$filtro_atual["where"]} = $valor)";
          } else {
            if ($filtros_restantes[$filtro_atual["name1"]]) {
              $valor = intval(preg_replace("/[^0-9]/", "", $filtros_restantes[$filtro_atual["name1"]]), 10);
              $where[] = "({$filtro_atual["where"]} >= $valor)";
            }
            if ($filtros_restantes[$filtro_atual["name2"]]) {
              $valor = intval(preg_replace("/[^0-9]/", "", $filtros_restantes[$filtro_atual["name2"]]), 10);
              $where[] = "({$filtro_atual["where"]} <= $valor)";
            }
          }
        }
        // Joins
        if ($filtro_atual["joins"]) {
          if ($filtros_restantes[str_replace("[]", '', $filtro_atual["name"])] || $filtros_restantes[$filtro_atual["name1"]] || $filtros_restantes[$filtro_atual["name2"]]) {
            if (is_array($filtro_atual["joins"])) {
              $joins = funcoes::mescla_arrays($joins, $filtro_atual["joins"]);
            } else {
              $joins[] = $filtro_atual["joins"];
            }
          }
        }
      }
    }
    $consulta = str_replace('[joins_filtro]', $joins ? implode("\n        ", array_unique($joins)) : '', $consulta);
    $consulta = str_replace('[HAVING]', $having ? "HAVING\n        ".implode(" AND\n        ", array_unique($having)) : '', $consulta);
    return str_replace('[campos_filtro]', $where ? implode(" AND\n        ", array_unique($where)) : 1, $consulta);
  }

}

?>