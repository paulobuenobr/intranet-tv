<?

class tarefas_calendario {

  var $meses_nomes = array("01" => "Janeiro", "02" => "Fevereiro", "03" => "Março", "04" => "Abril", "05" => "Maio", "06" => "Junho", "07" => "Julho", "08" => "Agosto", "09" => "Setembro", "10" => "Outubro", "11" => "Novembro", "12" => "Dezembro");
  var $dias_semana_nomes = array("Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado");
  var $dias_semana_abreviaturas = array("Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb");

  var $dia;
  var $semana;
  var $mes;
  var $mes_dias;
  var $ano;

  var $dia_ativo;
  var $mes_ativo;
  var $ano_ativo;

  var $dia_seguinte_dia;
  var $dia_seguinte_mes;
  var $dia_seguinte_ano;
  var $dia_anterior_dia;
  var $dia_anterior_mes;
  var $dia_anterior_ano;

  var $semana_seguinte_dia;
  var $semana_seguinte_mes;
  var $semana_seguinte_ano;
  var $semana_anterior_dia;
  var $semana_anterior_mes;
  var $semana_anterior_ano;

  var $mes_seguinte_dia;
  var $mes_seguinte_mes;
  var $mes_seguinte_ano;
  var $mes_anterior_dia;
  var $mes_anterior_mes;
  var $mes_anterior_ano;

  var $ano_seguinte_dia;
  var $ano_seguinte_mes;
  var $ano_seguinte_ano;
  var $ano_anterior_dia;
  var $ano_anterior_mes;
  var $ano_anterior_ano;

  var $eventos;
  var $feriados;
  var $aniversariantes;
  var $exibicao;

  var $link_dia_seguinte;
  var $link_dia_anterior;
  var $link_mes_seguinte;
  var $link_mes_anterior;
  var $link_ano_seguinte;
  var $link_ano_anterior;

  var $link_on_click;
  var $link_on_mouseover;
  var $link_on_mouseout;

  var $link_parametros = array ("dia" => '$this->getDia()', "mes" => '$this->getMes()', "ano" => '$this->getAno()',
                                "dia_seguinte_dia" => '$this->getDiaSeguinteDia()', "dia_seguinte_mes" => '$this->getDiaSeguinteMes()', "dia_seguinte_ano" => '$this->getDiaSeguinteAno()',
                                "dia_anterior_dia" => '$this->getDiaAnteriorDia()', "dia_anterior_mes" => '$this->getDiaAnteriorMes()', "dia_anterior_ano" => '$this->getDiaAnteriorAno()',
                                "semana_seguinte_dia" => '$this->getSemanaSeguinteDia()', "semana_seguinte_mes" => '$this->getSemanaSeguinteMes()', "semana_seguinte_ano" => '$this->getSemanaSeguinteAno()',
                                "semana_anterior_dia" => '$this->getSemanaAnteriorDia()', "semana_anterior_mes" => '$this->getSemanaAnteriorMes()', "semana_anterior_ano" => '$this->getSemanaAnteriorAno()',
                                "mes_seguinte_dia" => '$this->getMesSeguinteDia()', "mes_seguinte_mes" => '$this->getMesSeguinteMes()', "mes_seguinte_ano" => '$this->getMesSeguinteAno()',
                                "mes_anterior_dia" => '$this->getMesAnteriorDia()', "mes_anterior_mes" => '$this->getMesAnteriorMes()', "mes_anterior_ano" => '$this->getMesAnteriorAno()',
                                "ano_seguinte_dia" => '$this->getAnoSeguinteDia()', "ano_seguinte_mes" => '$this->getAnoSeguinteMes()', "ano_seguinte_ano" => '$this->getAnoSeguinteAno()',
                                "ano_anterior_dia" => '$this->getAnoAnteriorDia()', "ano_anterior_mes" => '$this->getAnoAnteriorMes()', "ano_anterior_ano" => '$this->getAnoAnteriorAno()');

  function tarefas_calendario($dia, $mes, $ano) {
    $this->setData($dia, $mes, $ano);
    $this->setDiaSeguinteData($dia, $mes, $ano);
    $this->setDiaAnteriorData($dia, $mes, $ano);
    $this->setSemanaSeguinteData($dia, $mes, $ano);
    $this->setSemanaAnteriorData($dia, $mes, $ano);
    $this->setMesSeguinteData($dia, $mes, $ano);
    $this->setMesAnteriorData($dia, $mes, $ano);
    $this->setAnoSeguinteData($dia, $mes, $ano);
    $this->setAnoAnteriorData($dia, $mes, $ano);
    $this->exibicao = $_GET["exibicao"] ? $_GET["exibicao"] : ($_SESSION["exibicao"] ? $_SESSION["exibicao"] : 1);
    $_SESSION["exibicao"] = $this->exibicao;
 }

  function setDia($dia) {$this->dia = $dia;}
  function getDia() {return $this->dia;}

  function setSemana($semana) {$this->semana = $semana;}
  function getSemana() {return $this->semana;}

  function setMes($mes) {$this->mes = $mes;}
  function getMes() {return $this->mes;}

  function setMesDias($mes_dias) {$this->mes_dias = $mes_dias;}
  function getMesDias() {return $this->mes_dias;}

  function setAno($ano) {$this->ano = $ano;}
  function getAno() {return $this->ano;}

  function getDiaSemanaNome($dia_semana) {return $this->dias_semana_nomes[$dia_semana];}
  function getDiaSemanaAbreviatura($dia_semana) {return $this->dias_semana_abreviaturas[$dia_semana];}
  function getMesNome($mes) {return $this->meses_nomes[$mes];}

  function setData($dia, $mes, $ano) {
    $this->setDia(date("d", strtotime("$ano-$mes-$dia")));
    $this->setSemana(date("W", strtotime("$ano-$mes-$dia")));
    $this->setMes(date("m", strtotime("$ano-$mes-$dia")));
    $this->setMesDias(date("t", strtotime("$ano-$mes-$dia")));
    $this->setAno(date("Y", strtotime("$ano-$mes-$dia")));
  }

  function setDiaSeguinteData($dia, $mes, $ano) {
    $this->setDiaSeguinteDia(date("d", strtotime("$ano-$mes-$dia tomorrow")));
    $this->setDiaSeguinteMes(date("m", strtotime("$ano-$mes-$dia tomorrow")));
    $this->setDiaSeguinteAno(date("Y", strtotime("$ano-$mes-$dia tomorrow")));
  }

  function setDiaSeguinteDia($dia) {$this->dia_seguinte_dia = $dia;}
  function getDiaSeguinteDia() {return $this->dia_seguinte_dia;}

  function setDiaSeguinteMes($mes) {$this->dia_seguinte_mes = $mes;}
  function getDiaSeguinteMes() {return $this->dia_seguinte_mes;}

  function setDiaSeguinteAno($ano) {$this->dia_seguinte_ano = $ano;}
  function getDiaSeguinteAno() {return $this->dia_seguinte_ano;}

  function setDiaAnteriorData($dia, $mes, $ano) {
    $this->setDiaAnteriorDia(date("d", strtotime("$ano-$mes-$dia yesterday")));
    $this->setDiaAnteriorMes(date("m", strtotime("$ano-$mes-$dia yesterday")));
    $this->setDiaAnteriorAno(date("Y", strtotime("$ano-$mes-$dia yesterday")));
  }

  function setDiaAnteriorDia($dia) {$this->dia_anterior_dia = $dia;}
  function getDiaAnteriorDia() {return $this->dia_anterior_dia;}

  function setDiaAnteriorMes($mes) {$this->dia_anterior_mes = $mes;}
  function getDiaAnteriorMes() {return $this->dia_anterior_mes;}

  function setDiaAnteriorAno($ano) {$this->dia_anterior_ano = $ano;}
  function getDiaAnteriorAno() {return $this->dia_anterior_ano;}

  function setSemanaSeguinteData($dia, $mes, $ano) {
    $this->setSemanaSeguinteDia(date("d", strtotime("$ano-$mes-$dia next week")));
    $this->setSemanaSeguinteMes(date("m", strtotime("$ano-$mes-$dia next week")));
    $this->setSemanaSeguinteAno(date("Y", strtotime("$ano-$mes-$dia next week")));
  }

  function setSemanaSeguinteDia($dia) {$this->semana_seguinte_dia = $dia;}
  function getSemanaSeguinteDia() {return $this->semana_seguinte_dia;}

  function setSemanaSeguinteMes($mes) {$this->semana_seguinte_mes = $mes;}
  function getSemanaSeguinteMes() {return $this->semana_seguinte_mes;}

  function setSemanaSeguinteAno($ano) {$this->semana_seguinte_ano = $ano;}
  function getSemanaSeguinteAno() {return $this->semana_seguinte_ano;}

  function setSemanaAnteriorData($dia, $mes, $ano) {
    $this->setSemanaAnteriorDia(date("d", strtotime("$ano-$mes-$dia previous week")));
    $this->setSemanaAnteriorMes(date("m", strtotime("$ano-$mes-$dia previous week")));
    $this->setSemanaAnteriorAno(date("Y", strtotime("$ano-$mes-$dia previous week")));
  }

  function setSemanaAnteriorDia($dia) {$this->semana_anterior_dia = $dia;}
  function getSemanaAnteriorDia() {return $this->semana_anterior_dia;}

  function setSemanaAnteriorMes($mes) {$this->semana_anterior_mes = $mes;}
  function getSemanaAnteriorMes() {return $this->semana_anterior_mes;}

  function setSemanaAnteriorAno($ano) {$this->semana_anterior_ano = $ano;}
  function getSemanaAnteriorAno() {return $this->semana_anterior_ano;}

  function setMesSeguinteData($dia, $mes, $ano) {
    if (date("t", strtotime("$ano-$mes-01 next month")) < $dia) {
      $dia = date("t", strtotime("$ano-$mes-01 next month"));
    }
    $this->setMesSeguinteDia(date("d", strtotime("$ano-$mes-$dia next month")));
    $this->setMesSeguinteMes(date("m", strtotime("$ano-$mes-$dia next month")));
    $this->setMesSeguinteAno(date("Y", strtotime("$ano-$mes-$dia next month")));
  }

  function setMesSeguinteDia($dia) {$this->mes_seguinte_dia = $dia;}
  function getMesSeguinteDia() {return $this->mes_seguinte_dia;}

  function setMesSeguinteMes($mes) {$this->mes_seguinte_mes = $mes;}
  function getMesSeguinteMes() {return $this->mes_seguinte_mes;}

  function setMesSeguinteAno($ano) {$this->mes_seguinte_ano = $ano;}
  function getMesSeguinteAno() {return $this->mes_seguinte_ano;}

  function setMesAnteriorData($dia, $mes, $ano) {
    if (date("t", strtotime("$ano-$mes-01 previous month")) < $dia) {
      $dia = date("t", strtotime("$ano-$mes-01 previous month"));
    }
    $this->setMesAnteriorDia(date("d", strtotime("$ano-$mes-$dia previous month")));
    $this->setMesAnteriorMes(date("m", strtotime("$ano-$mes-$dia previous month")));
    $this->setMesAnteriorAno(date("Y", strtotime("$ano-$mes-$dia previous month")));
  }

  function setMesAnteriorDia($dia) {$this->mes_anterior_dia = $dia;}
  function getMesAnteriorDia() {return $this->mes_anterior_dia;}

  function setMesAnteriorMes($mes) {$this->mes_anterior_mes = $mes;}
  function getMesAnteriorMes() {return $this->mes_anterior_mes;}

  function setMesAnteriorAno($ano) {$this->mes_anterior_ano = $ano;}
  function getMesAnteriorAno() {return $this->mes_anterior_ano;}

  function setAnoSeguinteData($dia, $mes, $ano) {
    if (date("t", strtotime("$ano-$mes-01 next year")) < $dia) {
      $dia = date("t", strtotime("$ano-$mes-01 next year"));
    }
    $this->setAnoSeguinteDia(date("d", strtotime("$ano-$mes-$dia next year")));
    $this->setAnoSeguinteMes(date("m", strtotime("$ano-$mes-$dia next year")));
    $this->setAnoSeguinteAno(date("Y", strtotime("$ano-$mes-$dia next year")));
  }

  function setAnoSeguinteDia($dia) {$this->ano_seguinte_dia = $dia;}
  function getAnoSeguinteDia() {return $this->ano_seguinte_dia;}

  function setAnoSeguinteMes($mes) {$this->ano_seguinte_mes = $mes;}
  function getAnoSeguinteMes() {return $this->ano_seguinte_mes;}

  function setAnoSeguinteAno($ano) {$this->ano_seguinte_ano = $ano;}
  function getAnoSeguinteAno() {return $this->ano_seguinte_ano;}

  function setAnoAnteriorData($dia, $mes, $ano) {
    if (date("t", strtotime("$ano-$mes-01 previous year")) < $dia) {
      $dia = date("t", strtotime("$ano-$mes-01 previous year"));
    }
    $this->setAnoAnteriorDia(date("d", strtotime("$ano-$mes-$dia previous year")));
    $this->setAnoAnteriorMes(date("m", strtotime("$ano-$mes-$dia previous year")));
    $this->setAnoAnteriorAno(date("Y", strtotime("$ano-$mes-$dia previous year")));
  }

  function setAnoAnteriorDia($dia) {$this->ano_anterior_dia = $dia;}
  function getAnoAnteriorDia() {return $this->ano_anterior_dia;}

  function setAnoAnteriorMes($mes) {$this->ano_anterior_mes = $mes;}
  function getAnoAnteriorMes() {return $this->ano_anterior_mes;}

  function setAnoAnteriorAno($ano) {$this->ano_anterior_ano = $ano;}
  function getAnoAnteriorAno() {return $this->ano_anterior_ano;}

  function setFeriados($dia, $conteudo) {$this->feriados[$dia][] = $conteudo;}
  function getFeriados($dia) {return $this->feriados[$dia];}

  function setAniversariantes($aniversariantes) {$this->aniversariantes = $aniversariantes;}

  function setEventos($dia, $horario = "00:00", $evento) {$this->eventos[$dia][$horario][] = $evento;}
  function getEventos($dia, $horario = "00:00") {return $this->eventos[$dia][$horario];}
  function setEventosEmLote($eventos) { $this->eventos = $eventos; }

  function setLinkOnClick($conteudo) {$this->link_on_click = $conteudo;}
  function getLinkOnClick() {return $this->link_on_click;}

  function setLinkOnMouseOver($conteudo) {$this->link_on_mouseover = $conteudo;}
  function getLinkOnMouseOver() {return $this->link_on_mouseover;}

  function setLinkOnMouseOut($conteudo) {$this->link_on_mouseout = $conteudo;}
  function getLinkOnMouseOut() {return $this->link_on_mouseout;}

  function setLinkParametros($chave,$conteudo) {$this->link_on_parametros[$chave] = $conteudo;}
  function getLinkParametros($chave) {return $this->link_on_parametros[$chave];}

  function setLinkDiaSeguinte($conteudo) {$this->link_dia_seguinte = $conteudo;}
  function getLinkDiaSeguinte() {return $this->link_dia_seguinte;}
  function setLinkDiaAnterior($conteudo) {$this->link_dia_anterior = $conteudo;}
  function getLinkDiaAnterior() {return $this->link_dia_anterior;}

  function setLinkSemanaSeguinte($conteudo) {$this->link_semana_seguinte = $conteudo;}
  function getLinkSemanaSeguinte() {return $this->link_semana_seguinte;}
  function setLinkSemanaAnterior($conteudo) {$this->link_semana_anterior = $conteudo;}
  function getLinkSemanaAnterior() {return $this->link_semana_anterior;}

  function setLinkMesSeguinte($conteudo) {$this->link_mes_seguinte = $conteudo;}
  function getLinkMesSeguinte() {return $this->link_mes_seguinte;}
  function setLinkMesAnterior($conteudo) {$this->link_mes_anterior = $conteudo;}
  function getLinkMesAnterior() {return $this->link_mes_anterior;}

  function setLinkAnoSeguinte($conteudo) {$this->link_ano_seguinte = $conteudo;}
  function getLinkAnoSeguinte() {return $this->link_ano_seguinte;}
  function setLinkAnoAnterior($conteudo) {$this->link_ano_anterior = $conteudo;}
  function getLinkAnoAnterior() {return $this->link_ano_anterior;}

  function verificaDiaSemana($dia, $mes, $ano) {
    return date("w", strtotime("{$ano}-{$mes}-{$dia}"));
  }

  function geraLink($link, $parametros) {
    $parametros = $this->mesclaLinkParametros($parametros);
    if (is_array($parametros)) {
      foreach ($parametros as $chave => $conteudo) {
        $link = str_replace("[$chave]",$conteudo,$link);
      }
    }
    return $link;
  }

  function mesclaLinkParametros($parametros) {
    if (is_array($this->link_parametros)) {
      foreach ($this->link_parametros as $chave => $conteudo) {
        eval("\$parametros[$chave] = $conteudo;");
      }
    }
    return $parametros;
  }

  function ehPassado($dia, $mes, $ano) {
  	return strtotime("{$ano}-{$mes}-{$dia}") < time();
  }

  function geraLinkOnClick($parametros = null) {return $this->geraLink($this->getLinkOnClick(),$parametros);}
  function geraLinkOnMouseOver($parametros = null) {return 'this.style.cursor=\\\'pointer\\\';'.$this->geraLink($this->getLinkOnMouseOver(),$parametros);}
  function geraLinkOnMouseOut($parametros = null) {return 'this.style.cursor=\\\'default\\\';'.$this->geraLink($this->getLinkOnMouseOut(),$parametros);}
  function geraLinkDiaSeguinte($parametros = null) {return $this->geraLink($this->getLinkDiaSeguinte(),$parametros);}
  function geraLinkDiaAnterior($parametros = null) {return $this->geraLink($this->getLinkDiaAnterior(),$parametros);}
  function geraLinkSemanaSeguinte($parametros = null) {return $this->geraLink($this->getLinkSemanaSeguinte(),$parametros);}
  function geraLinkSemanaAnterior($parametros = null) {return $this->geraLink($this->getLinkSemanaAnterior(),$parametros);}
  function geraLinkMesSeguinte($parametros = null) {return $this->geraLink($this->getLinkMesSeguinte(),$parametros);}
  function geraLinkMesAnterior($parametros = null) {return $this->geraLink($this->getLinkMesAnterior(),$parametros);}
  function geraLinkAnoSeguinte($parametros = null) {return $this->geraLink($this->getLinkAnoSeguinte(),$parametros);}
  function geraLinkAnoAnterior($parametros = null) {return $this->geraLink($this->getLinkAnoAnterior(),$parametros);}

  ######################
  ### Calendário Mês ###
  ######################

  function geraCalendarioMes() {
    $calendario  = $this->geraCalendarioMesCabecalho();
    $calendario .= $this->geraCalendarioMesCorpo();
    $calendario .= $this->geraCalendarioMesRodape();
    return $calendario;
  }

  function geraCalendarioMesCabecalho() {
    $cabecalho  = '<table border="0" cellspacing="1" cellpadding="0" height="100%" width="100%"><tr><td>';
  	$cabecalho .= '<table height="100%" width="100%" border="0" cellspacing="1" cellpadding="3" bgcolor="#505050">';
  	$cabecalho .= '<tr bgcolor="#505050">';
    $cabecalho .= '<td class="txtBranco11" align="center" colspan="7">';
    $cabecalho .= '<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td style="width:200px"></td>';
    $cabecalho .= '<td class="txtBranco11" align="center">';
    if ($this->getLinkMesAnterior()) $cabecalho .= '<a class="txtBranco11" href="javascript:'.$this->geraLinkMesAnterior().'"><b>&laquo;</b></a> ';
    $cabecalho .= ' <b>'.$this->getMesNome($this->getMes()).' / '.$this->getAno().'</b> ';
    if ($this->getLinkMesSeguinte()) $cabecalho .= ' <a class="txtBranco11" href="javascript:'.$this->geraLinkMesSeguinte().'"><b>&raquo;</b></a>';
    $cabecalho .= '</td><td  style="width:200px" align="right" class="txtBranco11"><a href="javascript:mudaAgendaExibicao(1)" class="txtBranco11">Agrupado</a> &nbsp;| &nbsp;<a href="javascript:mudaAgendaExibicao(2)" class="txtBranco11">Detalhado</a> &nbsp;';
    $cabecalho .= '</td></tr></table>';
    $cabecalho .= '</td>';
    $cabecalho .= '</tr>';
  	$cabecalho .= '<tr bgcolor="#828282" class="txtBranco11" align="center">';
  	$cabecalho .= '<td><b>'.$this->getDiaSemanaNome(0).'</b></td>';
  	$cabecalho .= '<td><b>'.$this->getDiaSemanaNome(1).'</b></td>';
  	$cabecalho .= '<td><b>'.$this->getDiaSemanaNome(2).'</b></td>';
  	$cabecalho .= '<td><b>'.$this->getDiaSemanaNome(3).'</b></td>';
  	$cabecalho .= '<td><b>'.$this->getDiaSemanaNome(4).'</b></td>';
  	$cabecalho .= '<td><b>'.$this->getDiaSemanaNome(5).'</b></td>';
  	$cabecalho .= '<td><b>'.$this->getDiaSemanaNome(6).'</b></td>';
  	$cabecalho .= '</tr>';
  	return $cabecalho;
  }

  function geraCalendarioMesCorpo() {
    $primeiro_dia_semana = $this->verificaDiaSemana(1, $this->getMes(), $this->getAno());
    $ultimo_dia_semana = $this->verificaDiaSemana($this->getMesDias(),$this->getMes(),$this->getAno());
    $linhas = ($this->getMesDias() + $primeiro_dia_semana - $ultimo_dia_semana + 6) / 7;
    $dia = 0;
    for ($i=0; $i<$linhas; $i++) {
      $corpo .= '<tr height="78" class="txtAzulEscuro11" align="left" valign="top">';
      for ($j = 0; $j < 7; $j++) {
        if (($i == 0 && $j < $primeiro_dia_semana)||($i == $linhas-1 && $j > $ultimo_dia_semana)) { // Mês Anterior ou Seguinte
          $corpo .= '<td bgcolor="#FFFFFF">&nbsp;</td>';
        } else { // Outros Dias
          $dia++;
          if (strtotime($this->getAno()."-".$this->getMes()."-".$dia) >= time())
	          $adicionar_tarefa = '<a href="?m=tarefas&a=adicionar&inicio='.(($dia>=10)?$dia:"0".$dia).'/'.$this->getMes().'/'.$this->getAno().'"><img src="imagens/adicionar.gif" width="9" height="9" align="right" alt="Adicionar Tarefa" vspace="3" border="0"></a>'; else
	          $adicionar_tarefa = '';
	        $calendario_cor = $this->buscaCalendarioMesCor($dia);
          $corpo .= '<td width="14%" bgcolor="'.$calendario_cor["cor"].'" title="'.$calendario_cor["hint"].'" onClick="'.$this->geraLinkOnClick(array("dia_atual" => ($dia >= 10) ? $dia : "0".$dia)).'" onMouseOver="'.$this->geraLinkOnMouseOver(array("dia_atual" => ($dia >= 10) ? $dia : "0".$dia)).'" onMouseOut="'.$this->geraLinkOnMouseOut(array("dia_atual" => ($dia >= 10) ? $dia : "0".$dia)).'">'.$adicionar_tarefa.'<b>'.(($dia >= 10) ? $dia : "0".$dia).'</b><br>'.$this->geraCalendarioMesEventos((($dia >= 10) ? $dia : "0".$dia)).'</td>';
        }
      }
      $corpo .= '</tr>';
    }
    return $corpo;
  }

  function geraCalendarioMesRodape() {
    $rodape .= '</table>';
    $rodape .= '</td></tr></table>';
  	return $rodape;
  }

  function buscaCalendarioMesCor($dia = null) {
    // Hoje
    if ($dia == date("d") && $this->getMes() == date("m") && $this->getAno() == date("Y")) {
      return array("cor" => "#C7D2DE", "hint" => "");
    }
    // Feriado
    $feriado = funcoes::ehFeriado($dia, $this->getMes(), $this->getAno());
    if ($feriado) {
      return array("cor" => "#FFCC66", "hint" => $feriado);
    }
    // Domingo
    if (0 == $this->verificaDiaSemana($dia, $this->getMes(), $this->getAno())) {
      return array("cor" => "#D7D7D7", "hint" => "");
    }
    // Sábado
    if (6 == $this->verificaDiaSemana($dia, $this->getMes(), $this->getAno())) {
      return array("cor" => "#D7D7D7", "hint" => "");
    }
    // Padrão
    return array("cor" => "#E8E8E8", "hint" => "");
  }

  function geraCalendarioMesEventos($dia) {
    $eventos = $this->getEventos($dia);

    if ($this->aniversariantes[$dia]) {
      $eventos_tratados[] = implode("<br>", $this->aniversariantes[$dia]);
    }
    
    if ($this->exibicao == 1) {
      if ($eventos) {
        for ($i = 0; $i < sizeof($eventos); $i++) {
          $eventos_dia[$eventos[$i]["titulo"]][] = $eventos[$i]["conteudo"];
        }
        ksort($eventos_dia);
        foreach ($eventos_dia as $titulo => $conteudo) {
          $eventos_dia_tratados[] = '<span onMouseOver="hintShow(\''.implode('<hr style=\\\'height: 1; color: #FFFFFF\\\'>', $conteudo).'\');" onMouseOut="hintHide();"><b>&raquo; '.$titulo.'</b> ('.sizeof($conteudo).')</span>';
        }
      }
      if ($eventos_dia_tratados) {
        $eventos_tratados[] = implode("<br>", $eventos_dia_tratados);
      }
    } elseif ($this->exibicao == 2) {
      foreach ($eventos AS $evento) {
        $eventos_dia_tratados[] = "<a href=\"{$evento["link"]}\" class=\"txtAzulEscuro11\" onMouseOver=\"hintShow('{$evento["conteudo"]}')\" onMouseOut=\"hintHide()\"><b>&raquo; {$evento["titulo"]}</b><br>{$evento["dados"]["protocolo"]} - {$evento["dados"]["titulo"]}</a>";
      }
      if ($eventos_dia_tratados) {
        $eventos_tratados[] = implode("<hr style=\"height:1px;color:#505050\">", $eventos_dia_tratados);
      }
    }
    return addslashes(@implode("<hr style=\"height:1px;color:#505050\">", $eventos_tratados));;
  }
  
  ############################
  ### Calendário Navegação ###
  ############################

  function geraCalendarioNavegacao() {
    $calendario .= $this->geraCalendarioNavegacaoCabecalho();
    $calendario .= $this->geraCalendarioNavegacaoCorpo();
    $calendario .= $this->geraCalendarioNavegacaoRodape();
    return $calendario;
  }

  function geraCalendarioNavegacaoCabecalho() {
    $cabecalho .= '<table border="0" cellspacing="1" cellpadding="0" height="100%" width="100%"><tr><td>';
  	$cabecalho .= '<table height="100%" width="100%" border="0" cellspacing="1" cellpadding="3" bgcolor="#505050">';
  	$cabecalho .= '<tr bgcolor="#505050">';
    $cabecalho .= '<td class="txtBranco11" align="center" colspan="7">';
    if ($this->getLinkAnoAnterior()) $cabecalho .= '<a class="txtBranco11" href="javascript:'.$this->geraLinkAnoAnterior().'"><b>&laquo;&laquo;</b></a> ';
    if ($this->getLinkMesAnterior()) $cabecalho .= '<a class="txtBranco11" href="javascript:'.$this->geraLinkMesAnterior().'"><b>&laquo;</b></a> ';
    $cabecalho .= ' <b>'.$this->getMesNome($this->getMes()).' / '.$this->getAno().'</b> ';
    if ($this->getLinkMesSeguinte()) $cabecalho .= ' <a class="txtBranco11" href="javascript:'.$this->geraLinkMesSeguinte().'"><b>&raquo;</b></a>';
    if ($this->getLinkAnoSeguinte()) $cabecalho .= ' <a class="txtBranco11" href="javascript:'.$this->geraLinkAnoSeguinte().'"><b>&raquo;&raquo;</b></a>';
    $cabecalho .= '</td>';
  	$cabecalho .= '</tr>';
  	$cabecalho .= '<tr bgcolor="#828282" class="txtBranco11" align="center">';
  	$cabecalho .= '<td><b>'.substr($this->getDiaSemanaNome(0), 0, 1).'</b></td>';
  	$cabecalho .= '<td><b>'.substr($this->getDiaSemanaNome(1), 0, 1).'</b></td>';
  	$cabecalho .= '<td><b>'.substr($this->getDiaSemanaNome(2), 0, 1).'</b></td>';
  	$cabecalho .= '<td><b>'.substr($this->getDiaSemanaNome(3), 0, 1).'</b></td>';
  	$cabecalho .= '<td><b>'.substr($this->getDiaSemanaNome(4), 0, 1).'</b></td>';
  	$cabecalho .= '<td><b>'.substr($this->getDiaSemanaNome(5), 0, 1).'</b></td>';
  	$cabecalho .= '<td><b>'.substr($this->getDiaSemanaNome(6), 0, 1).'</b></td>';
  	$cabecalho .= '</tr>';
  	return $cabecalho;
  }

  function geraCalendarioNavegacaoCorpo() {
    $primeiro_dia_semana = $this->verificaDiaSemana(1, $this->getMes(), $this->getAno());
    $ultimo_dia_semana = $this->verificaDiaSemana($this->getMesDias(), $this->getMes(), $this->getAno());
    $linhas = ($this->getMesDias() + $primeiro_dia_semana - $ultimo_dia_semana + 6) / 7;
    $dia = 0;
    for ($i = 0; $i < $linhas; $i++) {
      $corpo .= '<tr class="txtAzulEscuro11" align="center" valign="absmiddle">';
      for ($j = 0; $j < 7; $j++) {
        if (($i == 0 && $j < $primeiro_dia_semana) || ($i == $linhas-1 && $j > $ultimo_dia_semana)) {
        	// Mês Anterior ou Seguinte
          $corpo .= '<td bgcolor="#FFFFFF">&nbsp;</td>';
        } else {
        	// Outros Dias
          $dia++;
          if ($this->ehPassado($dia, $this->getMes(), $this->getAno()))
          	$eventos = ""; else
          	$eventos = '" onClick="'.$this->geraLinkOnClick(array("dia_atual" => sprintf("%02s", $dia))).'" onMouseOver="'.$this->geraLinkOnMouseOver(array("dia_atual" => sprintf("%02s", $dia))).'" onMouseOut="'.$this->geraLinkOnMouseOut(array("dia_atual" => sprintf("%02s", $dia)));
          $corpo .= '<td bgcolor="'.$this->buscaCalendarioNavegacaoCor(sprintf("%02s", $dia)).$eventos.'">'.(sprintf("%02s", $dia)).'</td>';
        }
      }
      $corpo .= '</tr>';
    }
    return $corpo;
  }

  function geraCalendarioNavegacaoRodape() {
    $rodape .= '</table>';
    $rodape .= '</td></tr></table>';
  	return $rodape;
  }

  function buscaCalendarioNavegacaoCor($dia = null) {
    // Dia Ativo
    if ($dia == $this->getDia()) return "#FF9999";
    // Hoje
    if ($dia == date("d") && $this->getMes() == date("m") && $this->getAno() == date("Y")) return "#FFCAFE";
    // Feriado
    if ($this->getFeriados($dia)) return "#FFCC66";
    // Domingo
    if (0 == $this->verificaDiaSemana($dia, $this->getMes(), $this->getAno())) return "#CCCCCC";
    // Com Tarefa
    if (sizeof($this->getEventos($dia)) > 5) return "#FFCCCC";
    // Com Tarefa
    if (sizeof($this->getEventos($dia)) > 0) return "#FFFFCC";
    // Passado
    if ($this->ehPassado($dia, $this->getMes(), $this->getAno())) return "#F0F0F0";
    return "#E8E8E8";
  }

  #########################
  ### Calendário Semana ###
  #########################

  function geraCalendarioSemana() {
    $calendario .= $this->geraCalendarioSemanaCabecalho();
    $calendario .= $this->geraCalendarioSemanaCorpo();
    $calendario .= $this->geraCalendarioSemanaRodape();
    return $calendario;
  }

  function geraCalendarioSemanaCabecalho() {
    $cabecalho .= '<table border="0" cellspacing="1" cellpadding="0" height="100%" width="100%"><tr><td>';
  	$cabecalho .= '<table height="100%" width="100%" border="0" cellspacing="1" cellpadding="3" bgcolor="#505050">';
  	$cabecalho .= '<tr bgcolor="#505050">';
    $cabecalho .= '<td class="txtBranco11" align="center" colspan="8">';
    if ($this->getLinkMesAnterior()) $cabecalho .= '<a class="txtBranco11" href="javascript:'.$this->geraLinkMesAnterior().'"><b>&laquo;&laquo;</b></a> ';
    if ($this->getLinkSemanaAnterior()) $cabecalho .= '<a class="txtBranco11" href="javascript:'.$this->geraLinkSemanaAnterior().'"><b>&laquo;</b></a> ';
    $cabecalho .= ' <b>Semana ' . $this->getSemana() . ' / ' . $this->getMesNome($this->getMes()) . ' / ' . $this->getAno() . '</b> ';
    if ($this->getLinkSemanaSeguinte()) $cabecalho .= ' <a class="txtBranco11" href="javascript:'.$this->geraLinkSemanaSeguinte().'"><b>&raquo;</b></a>';
    if ($this->getLinkMesSeguinte()) $cabecalho .= ' <a class="txtBranco11" href="javascript:'.$this->geraLinkMesSeguinte().'"><b>&raquo;&raquo;</b></a>';
    $cabecalho .= '</td>';
    $cabecalho .= '</tr>';
	$dia_atual_semana = $this->verificaDiaSemana($this->getDia(),$this->getMes(),$this->getAno());
	for ($i = 0; $i < 7; $i++) {
		$semana_atual[$i] = date("d", strtotime($this->getAno()."-".$this->getMes()."-".$this->getDia()." -".($dia_atual_semana - $i)." days"));
	}
  	$cabecalho .= '<tr bgcolor="#828282" class="txtBranco11" align="center">';
  	$cabecalho .= '<td width="27"><b>&nbsp;</b></td>';
  	for ($i = 0; $i < 7; $i++) $cabecalho .= '<td width="14%"><b>'.$this->getDiaSemanaAbreviatura($i).' ('.$semana_atual[$i].')</b></td>';
  	$cabecalho .= '</tr>';
  	return $cabecalho;
  }

  function geraCalendarioSemanaCorpo() {
		$dia_atual_semana = $this->verificaDiaSemana($this->getDia(), $this->getMes(), $this->getAno());
		for ($i = 0; $i < 7; $i++) {
			$semana_atual[$i] = date("d", strtotime($this->getAno()."-".$this->getMes()."-".$this->getDia()." -".($dia_atual_semana - $i)." days"));
		}
	  for ($hora = 0; $hora < 24; $hora++) {
      $horario = (($hora >= 10) ? $hora : "0".$hora).":00";
	  	$corpo .= '<tr bgcolor="#E8E8E8" class="txtAzulEscuro11" align="left" valign="top">';
	  	$corpo .= '<td><b>'.$horario.'</b></td>';
  		for ($i = 0; $i < 7; $i++) {
        if (strtotime($this->getAno()."-".$this->getMes()."-".$this->getDia()." ".$hora.":00:00 -".($dia_atual_semana + $i)." days") >= time())
          $adicionar_tarefa = '<a href="?m=tarefas&a=adicionar&inicio='.sprintf("%02s", $this->getDia() - $dia_atual_semana + $i).'/'.$this->getMes().'/'.$this->getAno().' ('.$horario.')'.'"><img src="imagens/adicionar.gif" width="9" height="9" align="right" alt="Adicionar Tarefa" vspace="3" border="0"></a>'; else
          $adicionar_tarefa = '';
  			$corpo .= '<td bgcolor="'.(($i == 0) ? "#CCCCCC" : "#E8E8E8").'" onClick="'.$this->geraLinkOnClick(array("dia_atual" => ($semana_atual[$i] >= 10) ? $semana_atual[$i] : "0".$semana_atual[$i])).'" onMouseOver="'.$this->geraLinkOnMouseOver(array("dia_atual" => ($semana_atual[$i] >= 10) ? $semana_atual[$i] : "0".$semana_atual[$i])).'" onMouseOut="'.$this->geraLinkOnMouseOut(array("dia_atual" => ($semana_atual[$i] >= 10) ? $semana_atual[$i] : "0".$semana_atual[$i])).'">'.$adicionar_tarefa.$this->geraCalendarioDiaEventos((($semana_atual[$i] >= 10) ? $semana_atual[$i]: "0".$semana_atual[$i]), $horario).'</td>';
  		}
  	 	$corpo .= '</tr>';
	  }
  	return $corpo;
  }

  function geraCalendarioSemanaRodape() {
    $rodape .= '</table>';
    $rodape .= '</td></tr></table>';
  	return $rodape;
  }

  ######################
  ### Calendário Dia ###
  ######################

  function geraCalendarioDia() {
    $calendario .= $this->geraCalendarioDiaCabecalho();
    $calendario .= $this->geraCalendarioDiaCorpo();
    $calendario .= $this->geraCalendarioDiaRodape();
    return $calendario;
  }

  function geraCalendarioDiaCabecalho() {
    $cabecalho .= '<table border="0" cellspacing="1" cellpadding="0" height="100%" width="100%"><tr><td>';
  	$cabecalho .= '<table height="100%" width="100%" border="0" cellspacing="1" cellpadding="3" bgcolor="#505050">';
  	$cabecalho .= '<tr bgcolor="#505050">';
    $cabecalho .= '<td class="txtBranco11" align="center" colspan="2">';
    if ($this->getLinkMesAnterior()) $cabecalho .= '<a class="txtBranco11" href="javascript:'.$this->geraLinkMesAnterior().'"><b>&laquo;&laquo;&laquo;</b></a> ';
    if ($this->getLinkSemanaAnterior()) $cabecalho .= '<a class="txtBranco11" href="javascript:'.$this->geraLinkSemanaAnterior().'"><b>&laquo;&laquo;</b></a> ';
    if ($this->getLinkDiaAnterior()) $cabecalho .= '<a class="txtBranco11" href="javascript:'.$this->geraLinkDiaAnterior().'"><b>&laquo;</b></a> ';
    $cabecalho .= ' <b>' . $this->getDiaSemanaNome($this->verificaDiaSemana($this->getDia(),$this->getMes(),$this->getAno())) . ' (' . $this->getDia() . ') / ' . $this->getMesNome($this->getMes()) . ' / ' . $this->getAno() . '</b> ';
    if ($this->getLinkDiaSeguinte()) $cabecalho .= ' <a class="txtBranco11" href="javascript:'.$this->geraLinkDiaSeguinte().'"><b>&raquo;</b></a>';
    if ($this->getLinkSemanaSeguinte()) $cabecalho .= ' <a class="txtBranco11" href="javascript:'.$this->geraLinkSemanaSeguinte().'"><b>&raquo;&raquo;</b></a>';
    if ($this->getLinkMesSeguinte()) $cabecalho .= ' <a class="txtBranco11" href="javascript:'.$this->geraLinkMesSeguinte().'"><b>&raquo;&raquo;&raquo;</b></a>';
    $cabecalho .= '</td>';
    $cabecalho .= '</tr>';
  	return $cabecalho;
  }

  function geraCalendarioDiaCorpo() {
  	for ($hora = 0; $hora < 24; $hora++) {
      $horario = (($hora>=10) ? $hora : "0".$hora).":00";
	  	$corpo .= '<tr bgcolor="#E8E8E8" class="txtAzulEscuro11" align="left" valign="top">';
	  	$corpo .= '<td width="10"><b>' . $horario . '</b></td>';
      if (strtotime($this->getAno()."-".$this->getMes()."-".$this->getDia()." ".$hora.":00:00") >= time())
        $adicionar_tarefa = '<a href="?m=tarefas&a=adicionar&inicio='.$this->getDia().'/'.$this->getMes().'/'.$this->getAno().' ('.$horario.')"><img src="imagens/adicionar.gif" width="9" height="9" align="right" alt="Adicionar Tarefa" vspace="3" border="0"></a>'; else
        $adicionar_tarefa = '';
	  	$corpo .= '<td width="100%" bgcolor="'.(($this->verificaDiaSemana($this->getDia(), $this->getMes(), $this->getAno()) == 0) ? "#CCCCCC" : "#E8E8E8").'" onClick="'.$this->geraLinkOnClick(array("dia_atual" => $this->getDia())).'" onMouseOver="'.$this->geraLinkOnMouseOver(array("dia_atual" => $this->getDia())).'" onMouseOut="'.$this->geraLinkOnMouseOut(array("dia_atual" => $this->getDia())).'">'.$adicionar_tarefa.$this->geraCalendarioDiaEventos($this->getDia(), $horario).'</td>';
  	 	$corpo .= '</tr>';
	  }
  	return $corpo;
  }

  function geraCalendarioDiaRodape() {
    $rodape .= '</table>';
    $rodape .= '</td></tr></table>';
  	return $rodape;
  }

  function geraCalendarioDiaEventos($dia, $horario) {
    $eventos = $this->getEventos($dia, $horario);
    for ($i = 0; $i < sizeof($eventos); $i++) {
      $eventos_dia_tratados[] = '<a href="'.$eventos[$i]["link"].'" class="txtAzulEscuro11" onMouseOver="hintShow(\''.$eventos[$i]["conteudo"].'\');" onMouseOut="hintHide();">'.$eventos[$i]["titulo"].'</a>';
    }
    if ($eventos_dia_tratados) {
      return addslashes(implode("<br>", $eventos_dia_tratados));
    }
  }

}

?>
