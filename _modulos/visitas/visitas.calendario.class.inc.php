<?

class visitas_calendario {

  var $permissoes;
  var $cor_livre = array("#BFC6CD", "#8F959C");
  var $cor_livre_casa = array("#BFC6CD", "#8F959C");
  var $cor_livre_condominio = array("#81C3BF", "#4286B2");
  var $cor_agendado = array("#4444E2", "#313190");
  var $cor_reagendado = array("#2929AB", "#000059");
  var $cor_instalado = array("#11B511", "#297C29");
  var $cor_bloqueado = array("#DC0000", "#810000");
  var $cor_suspensao = array("#A56900", "#6C4500");
  var $cor_reservado = array("#FF69B4", "#AC4792");
  var $cor_ativo = array("#779077", "#516251");
  var $cor_selecionado = array("#FFBA00", "#916A00");
  var $cor_finalizado = array("#779077", "#516251");
  
  var $lacunas_classes = array();
  
  var $visitas_tecnicas = array();
  var $bancodedados;
  
  function __construct ($bancodedados, $permissoes) {
    $this->bancodedados = $bancodedados;
    $this->permissoes = $permissoes;
  }

  function getDayNames() {
    return $this->dayNames;
  }

  function setDayNames($names) {
    $this->dayNames = $names;
  }

  function getMonthNames() {
    return $this->monthNames;
  }

  function setMonthNames($names) {
    $this->monthNames = $names;
  }

  function getStartDay() {
    return $this->startDay;
  }

  function setStartDay($day) {
    $this->startDay = $day;
  }

  function getStartMonth() {
    return $this->startMonth;
  }

  function setStartMonth($month) {
    $this->startMonth = $month;
  }

  function setDados($dados) {
    $this->dados = $dados;
  }

  function getCalendarLink($month, $year) {
    return "";
  }

  function getDateLink($day, $month, $year) {
    return "";
  }

  function getCurrentMonthView() {
    $d = getdate(time());
    return $this->getMonthView($d["mon"], $d["year"]);
  }

  function getCurrentYearView() {
    $d = getdate(time());
    return $this->getYearView($d["year"]);
  }

  function getMonthView($month, $year) {
    return $this->getMonthHTML($month, $year);
  }

  function getYearView($year) {
    return $this->getYearHTML($year);
  }

  function getDaysInMonth($month, $year) {
    return intval(date("t", strtotime("$year-$month-01")), 10);
  }

  function getMonthHTML($m, $y) {
    $s = "";

    $a = $this->adjustDate($m, $y);
    $month = $a[0];
    $year = $a[1];

    $daysInMonth = $this->getDaysInMonth($month, $year);
    $date = getdate(strtotime("{$year}-{$month}-01 12:00:00"));

    $first = $date["wday"];
    $monthName = $this->monthNames[$month - 1];

    $prev = $this->adjustDate($month - 1, $year);
    $next = $this->adjustDate($month + 1, $year);

    $s .= '<table border="0" cellspacing="1" cellpadding="0" width="100%"><tr><td>';
    $s .= '<table width="100%" border="0" cellspacing="1" cellpadding="3" bgcolor="#505050">';
    $s .= '<tr bgcolor="#505050">';
    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[($this->startDay) % 7].'</b></td>';
    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[($this->startDay + 1) % 7].'</b></td>';
    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[($this->startDay + 2) % 7].'</b></td>';
    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[($this->startDay + 3) % 7].'</b></td>';
    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[($this->startDay + 4) % 7].'</b></td>';
    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[($this->startDay + 5) % 7].'</b></td>';
    $s .= "</tr>";

    // We need to work out what date to start at so that the first appears in the correct column
    $d = $this->startDay + 1 - $first;
    while ($d > 1) {
      $d -= 7;
    }
    //$d = $d == -5 ? 2 : -3;
    $d = $d == -5 ? 2 : $d;
    // Make sure we know when today is, so that we can use a different CSS style
    $today = getdate(time());
    $hoje = date("Ymd");

    while ($d <= $daysInMonth) {
      $s .= '<tr bgcolor="#E8E8E8">';
      for ($i = 0; $i < 6; $i++) {
        $data_atual = $year.((intval($month) < 10) ? "0".intval($month) : intval($month)).(($d < 10) ? "0".$d : $d);
        $s .= '<td class="txtAzulEscuro11" align="left" valign="top"';
        if ($d > 0 && $d <= $daysInMonth) {
        	$feriado = $this->ehFeriado($data_atual);
          if ($feriado) {
            $s .= ' bgcolor="#D7D7D7">';
          } elseif ($i == 5) {
            $s .= ' bgcolor="#D7D7D7">';
          } elseif ($data_atual == date("Ymd")) {
            $s .= ' bgcolor="#B4BEC9">';
          } else {
            $s .= '>';
          }
          $s .= "<span style=\"color:#232323\">".sprintf("%02s", $d).($feriado ? " -</span> <span style=\"cursor:pointer\" onmouseover=\"hintShow('<center>".sprintf("%02s", $d)." / $monthName<br>$feriado</center>', 150)\" onmouseout=\"hintHide()\">".$this->cortaNome($feriado, 15)."</span>" : "</span>")."";
          $s .= $this->geraPainelVisitas($data_atual);
        } else {
          $s .= ' bgcolor="#FFFFFF">';
        }
        $s .= "</td>";
        $d++;
      }
      $s .= "</tr>";
      $d++;
    }
    $s .= '<tr>';
    $s .= '<td colspan="6" bgcolor="#D7D7D7">';

    $s .= '<table border="0" cellspacing="0" cellpadding="0"><tr>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_livre[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_livre[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="80px">&nbsp;Horário Livre &nbsp;</td>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_agendado[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_agendado[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="70px">&nbsp;Agendado &nbsp;</td>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_reagendado[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_reagendado[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="80px">&nbsp;Reagendado &nbsp;</td>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_instalado[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_instalado[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="70px">&nbsp;Instalado &nbsp;</td>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_bloqueado[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_bloqueado[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="80px">&nbsp;Bloqueado &nbsp;</td>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_suspensao[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_suspensao[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="80px">&nbsp;Susp. Temp. &nbsp;</td>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_ativo[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_ativo[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="90px">&nbsp;Contrato Ativo &nbsp;</td>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_selecionado[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_selecionado[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="80px">&nbsp;Selecionado &nbsp;</td>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_reservado[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_reservado[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="80px">&nbsp;Reservado &nbsp;</td>';
    $s .= '</tr></table>';

    $s .= '</td>';
    $s .= '</tr>';

    $s .= "</table>";
    $s .= '</td></tr></table>';

    return $s;
  }
  
function getMonthHTMLMigracao($m, $y) {
    $s = "";

    $a = $this->adjustDate($m, $y);
    $month = $a[0];
    $year = $a[1];

    $daysInMonth = $this->getDaysInMonth($month, $year);
    $date = getdate(strtotime("{$year}-{$month}-01 12:00:00"));
    
    $first = $date["wday"];
    $monthName = $this->monthNames[$month - 1];

    $prev = $this->adjustDate($month - 1, $year);
    $next = $this->adjustDate($month + 1, $year);

    $s .= '<table border="0" cellspacing="1" cellpadding="0" width="100%"><tr><td>';
    $s .= '<table width="100%" border="0" cellspacing="1" cellpadding="3" bgcolor="#505050">';
    $s .= '<tr bgcolor="#505050">';
    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[($this->startDay) % 7].'</b></td>';
    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[($this->startDay + 1) % 7].'</b></td>';
    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[($this->startDay + 2) % 7].'</b></td>';
    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[($this->startDay + 3) % 7].'</b></td>';
    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[($this->startDay + 4) % 7].'</b></td>';
    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[($this->startDay + 5) % 7].'</b></td>';
    $s .= "</tr>";

    // We need to work out what date to start at so that the first appears in the correct column
    $d = $this->startDay + 1 - $first;
    while ($d > 1) {
      $d -= 7;
    }
    //$d = $d == -5 ? 2 : -3;
    $d = $d == -5 ? 2 : $d;
    // Make sure we know when today is, so that we can use a different CSS style
    $today = getdate(time());
    $hoje = date("Ymd");

    while ($d <= $daysInMonth) {
      $s .= '<tr bgcolor="#E8E8E8">';
      for ($i = 0; $i < 6; $i++) {
        $data_atual = $year.((intval($month) < 10) ? "0".intval($month) : intval($month)).(($d < 10) ? "0".$d : $d);
        $s .= '<td class="txtAzulEscuro11" align="left" valign="top"';
        if ($d > 0 && $d <= $daysInMonth) {
        	$feriado = $this->ehFeriado($data_atual);
          if ($feriado) {
            $s .= ' bgcolor="#D7D7D7">';
          } elseif ($i == 5) {
            $s .= ' bgcolor="#D7D7D7">';
          } elseif ($data_atual == date("Ymd")) {
            $s .= ' bgcolor="#B4BEC9">';
          } else {
            $s .= '>';
          }
          $s .= "<span style=\"color:#232323\">".sprintf("%02s", $d).($feriado ? " -</span> <span style=\"cursor:pointer\" onmouseover=\"hintShow('<center>".sprintf("%02s", $d)." / $monthName<br>$feriado</center>', 150)\" onmouseout=\"hintHide()\">".$this->cortaNome($feriado, 15)."</span>" : "</span>")."";
          $s .= $this->geraPainelVisitasMigracao($data_atual);
        } else {
          $s .= ' bgcolor="#FFFFFF">';
        }
        $s .= "</td>";
        $d++;
      }
      $s .= "</tr>";
      $d++;
    }
    $s .= '<tr>';
    $s .= '<td colspan="6" bgcolor="#D7D7D7">';

    $s .= '<table border="0" cellspacing="0" cellpadding="0"><tr>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_livre[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_livre[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="90px">&nbsp;Horário Livre &nbsp;</td>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_agendado[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_agendado[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="90px">&nbsp;Agendado &nbsp;</td>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_reagendado[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_reagendado[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="90px">&nbsp;Reagendado &nbsp;</td>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_instalado[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_instalado[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="90px">&nbsp;Instalado &nbsp;</td>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_bloqueado[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_bloqueado[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="90px">&nbsp;Bloqueado &nbsp;</td>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_suspensao[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_suspensao[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="90px">&nbsp;Susp. Temp. &nbsp;</td>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_ativo[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_ativo[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="90px">&nbsp;Contrato Ativo &nbsp;</td>';
    $s .= '<td class="txtAzulEscuro10"><div style="border:1px solid '.$this->cor_selecionado[1].';width:6px;height:8px"><div style="font-size:1px;width:6px;height:8px;background-color:'.$this->cor_selecionado[0].'"></div></div></td>';
    $s .= '<td class="txtAzulEscuro10" width="90px">&nbsp;Selecionado &nbsp;</td>';
    $s .= '</tr></table>';

    $s .= '</td>';
    $s .= '</tr>';

    $s .= "</table>";
    $s .= '</td></tr></table>';

    return $s;
  }

  function cortaNome($nome, $tamanho) {
    if (strlen($nome) <= $tamanho) {
      return $nome;
    } else {
      return substr($nome, 0, $tamanho - 3)."...";
    }
  }

  function ehFeriado($data) {
    return funcoes::ehFeriado(substr($data, 6, 2), substr($data, 4, 2), substr($data, 0, 4));
  }

	function getMonthHTMLSuporteTecnico($visita_tipo_id, $m, $y, $tipo_listar, $cliente_tipo, $visita_id) {
	    $s = "";
	
	    $a = $this->adjustDate($m, $y);
	    $month = $a[0];
	    $year = $a[1];
	
	    $daysInMonth = $this->getDaysInMonth($month, $year);
   	  $date = getdate(strtotime("{$year}-{$month}-01 12:00:00"));
	    	
	    $first = $date["wday"];
	    $monthName = $this->monthNames[$month - 1];
	
	    $prev = $this->adjustDate($month - 1, $year);
	    $next = $this->adjustDate($month + 1, $year);
	
	    $s .= '<table border="0" cellspacing="1" cellpadding="0" width="100%"><tr><td>';
	    $s .= '<table width="100%" border="0" cellspacing="1" cellpadding="3" bgcolor="#505050">';
	    $s .= '<tr bgcolor="#505050">';
	    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[(0) % 7].'</b></td>';
	    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[(1) % 7].'</b></td>';
	    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[(2) % 7].'</b></td>';
	    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[(3) % 7].'</b></td>';
	    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[(4) % 7].'</b></td>';
	    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[(5) % 7].'</b></td>';
	    $s .= '<td class="txtBranco11" align="center"><b>'.$this->dayNames[(6) % 7].'</b></td>';
	    
	    $s .= "</tr>";
	
	    // We need to work out what date to start at so that the first appears in the correct column
	    $d = 1 - $first;
	    while ($d > 1) {
	      $d -= 7;
	    }
	    //$d = $d == -5 ? 2 : -3;
	    $d = $d == -6 ? 2 : $d;
	    // Make sure we know when today is, so that we can use a different CSS style
	    $today = getdate(time());
	    $hoje = date("Ymd");
	
	    while ($d <= $daysInMonth) {
	      $s .= '<tr bgcolor="#E8E8E8">';
	      for ($i = 0; $i < 7; $i++) {
	        $data_atual = $year.((intval($month) < 10) ? "0".intval($month) : intval($month)).(($d < 10) ? "0".$d : $d);
	        $s .= '<td width="16%" class="txtAzulEscuro11" align="left" valign="top"';
	        if ($d > 0 && $d <= $daysInMonth) {
	        	$feriado = $this->ehFeriado($data_atual);
	          if ($feriado) {
	            $s .= ' bgcolor="#D7D7D7">';
	          } elseif ($i == 6 || $i == 0) {
	            $s .= ' bgcolor="#D7D7D7">';
	          } elseif ($data_atual == date("Ymd")) {
	            $s .= ' bgcolor="#B4BEC9">';
	          } else {
	            $s .= '>';
	          }
	          $s .= "<span style=\"color:#232323\">".sprintf("%02s", $d).($feriado ? " -</span> <span style=\"cursor:pointer\" onmouseover=\"hintShow('<center>".sprintf("%02s", $d)." / $monthName<br>$feriado</center>', 150)\" onmouseout=\"hintHide()\">".$this->cortaNome($feriado, 15)."</span>" : "</span>")."";
	          $s .= $this->geraPainelVisitaTecnica($visita_tipo_id, $data_atual, $tipo_listar, $cliente_tipo, $visita_id, $i);
	        } else {
	          $s .= ' bgcolor="#FFFFFF">';
	        }
	        $s .= "</td>";
	        $d++;
	      }
	      $s .= "</tr>";
	      //$d++;
	    }
	    $s .= '<tr>';
	    $s .= '<td colspan="7" bgcolor="#D7D7D7">';
	
	    $s .= '<table border="0" cellspacing="0" cellpadding="0"><tr>';
	    $s .= '<td class="txtAzulEscuro10"><div class="lacunaVisita residencial"></div></td>';
	    $s .= '<td class="txtAzulEscuro10" width="100px">&nbsp;Horário Livre Casa&nbsp;</td>';
	    $s .= '<td class="txtAzulEscuro10"><div class="lacunaVisita condominial"></div></td>';
	    $s .= '<td class="txtAzulEscuro10" width="140px">&nbsp;Horário Livre Condominio&nbsp;</td>';
	    $s .= '<td class="txtAzulEscuro10"><div class="lacunaVisita agendado residencial"></div></td>';
	    $s .= '<td class="txtAzulEscuro10" width="100px">&nbsp;Visita Agendada &nbsp;</td>';
	    $s .= '<td class="txtAzulEscuro10"><div class="lacunaVisita agendado residencial ativa"></div></td>';
	    $s .= '<td class="txtAzulEscuro10" width="90px">&nbsp;Selecionado&nbsp;</td>';
	    $s .= '<td class="txtAzulEscuro10"><div class="lacunaVisita finalizado residencial"></div></td>';
	    $s .= '<td class="txtAzulEscuro10" width="90px">&nbsp;Finalizado&nbsp;</td>';
	    $s .= '</tr></table>';
	
	    $s .= '</td>';
	    $s .= '</tr>';
	
	    $s .= "</table>";
	    $s .= '</td></tr></table>';
	
	    return $s;
  }
  
  function geraPainelVisitas($dia) {
    $visitas_por_turno = VISITAS_TURNO_INSTALACAO;
    $hoje = date("Ymd");
    $hora = date("His");
    $conteudo .= '<br>';
    // Manhã
    $visitas = array();
    $conteudo .= '<table border="0" cellspacing="0" cellpadding="1" align="center">';
    $conteudo .= '<tr>';
    $conteudo .= '<td class="txtAzulEscuro10" valign="middle" rowspan="2"><div onMouseOver="hintShow(\'[manha]\', 300)" onMouseOut="hintHide()" class="txtAzulEscuro10" style="cursor:'.($this->dados[$dia][1] ? 'pointer' : 'default').'">M</div></td>';
    for ($i = 0; $i < $visitas_por_turno; $i++) {
      $conteudo .= '<td valign="middle">';
      if (array_key_exists($dia, $this->dados) && array_key_exists(1, $this->dados[$dia]) && array_key_exists($i, $this->dados[$dia][1])) {
	      if (array_key_exists("reservado", $this->dados[$dia][1][$i]) && $this->dados[$dia][1][$i]["reservado"]) {
	      	$conteudo .= '<div style="border:1px solid '. $this->cor_reservado[1].'"><div style="font-size:1px;width:6px;height:6px;background-color:'. $this->cor_reservado[0] . ';cursor:pointer" id="agenda_'.$this->dados[$dia][1][$i]["id"].'" onClick="desreservarVisita('.$this->dados[$dia][1][$i]["id"].')" ondblclick="resetaGatilho(); verVisita('.$this->dados[$dia][1][$i]["id"].');"';
	        if ($this->dados[$dia][1][$i]["ultimo_comentario"] && $this->dados[$dia][1][$i]["ultimo_comentario"] <> "Nenhum Comentário Disponível") {
	        	$conteudo .= 'onMouseOver="hintShow(\'&raquo; Último Comentário:<br>'.preg_replace("/[\r\n\t ]+/"," ",$this->dados[$dia][1][$i]["ultimo_comentario"]);
	        	$conteudo .= '\', 300)" onMouseOut="hintHide()"';
	        }
	        $conteudo .= ' ></div>';
	      } else {
	        $conteudo .= '<div style="border:1px solid '.($this->dados[$dia][1][$i]["reservado"] ? $this->cor_reservado[1] : ($this->dados[$dia][1][$i]["status_id"] == 2 ? ($this->dados[$dia][1][$i]["instalado"] ? ($this->dados[$dia][1][$i]["bloqueado"] ? ($this->dados[$dia][1][$i]["suspensao"] ? $this->cor_suspensao[1] : $this->cor_bloqueado[1]) : $this->cor_instalado[1]) : ($this->dados[$dia][1][$i]["agendamentos"] > 1 ? $this->cor_reagendado[1] : $this->cor_agendado[1])) : $this->cor_ativo[1])).'"><div style="font-size:1px;width:6px;height:6px;background-color:'.($this->dados[$dia][1][$i]["reservado"] ? $this->cor_reservado[0] : ($this->dados[$dia][1][$i]["status_id"] == 2 ? ($this->dados[$dia][1][$i]["instalado"] ? ($this->dados[$dia][1][$i]["bloqueado"] ? ($this->dados[$dia][1][$i]["suspensao"] ? $this->cor_suspensao[0] : $this->cor_bloqueado[0]) : $this->cor_instalado[0]) : ($this->dados[$dia][1][$i]["agendamentos"] > 1 ? $this->cor_reagendado[0] : $this->cor_agendado[0])) : $this->cor_ativo[0])).(($this->permissoes["visitas"]["ver"] || $this->permissoes["visitas"]["realocar"]) ? ';cursor:pointer' : '').'" id="agenda_'.$this->dados[$dia][1][$i]["id"].'" onMouseOver="hintShow(\'';
	        $visita  = '&raquo; Tipo: '.addslashes($this->dados[$dia][1][$i]["tipo"]).'<br>';
	        $visita .= '&raquo; Cliente: '.addslashes($this->dados[$dia][1][$i]["cliente"]).'<br>';
	        $visita .= '&raquo; Endereço: '.addslashes($this->dados[$dia][1][$i]["local"]).'<br>';
	        $visita .= '&raquo; Telefone: '.addslashes($this->dados[$dia][1][$i]["telefone"]);
	        if ($this->dados[$dia][1][$i]["ultimo_comentario"] && $this->dados[$dia][1][$i]["ultimo_comentario"] <> "Nenhum Comentário Disponível") $visita .= '<br>&raquo; Último Comentário:<br>'.preg_replace("/[\r\n\t ]+/"," ",$this->dados[$dia][1][$i]["ultimo_comentario"]);
	        $conteudo .= $visita;
	        $conteudo .= '\', 300)" onMouseOut="hintHide()"';
	        if($this->permissoes["visitas"]["realocar"] && $this->dados[$dia][1][$i]["status_id"] == 2){
	        	$conteudo .= ' onClick="definirVisita('.$this->dados[$dia][1][$i]["id"].')"';
	        }
	        if ($this->permissoes["visitas"]["ver"]) $conteudo .= ' onDblClick="resetaGatilho(); verVisita('.$this->dados[$dia][1][$i]["id"].')"';
	        $conteudo .= '>';
	        if (in_array($this->dados[$dia][1][$i]["servico_id"], array(28, 33))) {
	          $conteudo .= '<img src="imagens/faixa.png" width="6" height="6" border="0">';
	        }
	        $conteudo .= '</div></div>';
	        $visitas[] = $visita;
	      }
      } else {
        $conteudo .= '<div style="border:1px solid '.$this->cor_livre[1].'"><div style="font-size:1px;width:6px;height:6px;background-color:'.$this->cor_livre[0].'"';
        if ((($hoje == $dia && $hora < "120000") || ($hoje < $dia)) && (($this->permissoes["visitas"]["alocar"] && $i < ($visitas_por_turno - 1)) || $this->permissoes["visitas"]["realocar"])) $conteudo .= " onClick=\"alocarVisita('".substr($dia, 0, 4)."-".substr($dia, 4, 2)."-".substr($dia, 6, 2)."', 1)\" onmouseover=\"this.style.cursor='pointer'\" onmouseout=\"this.style.cursor='default'\"";
        $conteudo .= '></div></div>';
      }
      $conteudo .= '</td>';
      if ($i + 1 == floor($visitas_por_turno / 2)) {
        $conteudo .= '</tr>';
        $conteudo .= '<tr>';
      }
    }
    $conteudo .= '</tr>';
    $conteudo = str_replace("[manha]", @implode('<hr style=\\\'height:1;color:#FFFFFF\\\'>', $visitas), $conteudo);
    // Separador
    $conteudo .= '<tr><td></td><td colspan="'.($visitas_por_turno - 1).'" height="2px"><table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td height="1" bgcolor="#505050"></td></tr></table></td></tr>';
    // Tarde
    $visitas = array();
    $conteudo .= '<tr>';
    $conteudo .= '<td class="txtAzulEscuro10" valign="middle" rowspan="2"><div onMouseOver="hintShow(\'[tarde]\', 300)" onMouseOut="hintHide()" class="txtAzulEscuro10" style="cursor:'.($this->dados[$dia][2] ? 'pointer' : 'default').'">T</div></td>';
    for ($i = 0; $i < $visitas_por_turno; $i++) {
      $conteudo .= '<td valign="middle">';
      if (array_key_exists($dia, $this->dados) && array_key_exists(2, $this->dados[$dia]) && array_key_exists($i, $this->dados[$dia][2])) {
	      if (array_key_exists("reservado", $this->dados[$dia][2][$i]) && $this->dados[$dia][2][$i]["reservado"]) {
	       	$conteudo .= '<div style="border:1px solid '. $this->cor_reservado[1].'"><div style="font-size:1px;width:6px;height:6px;background-color:'. $this->cor_reservado[0] . ';cursor:pointer" id="agenda_'.$this->dados[$dia][2][$i]["id"].'" onClick="desreservarVisita('.$this->dados[$dia][2][$i]["id"].')" ondblclick="resetaGatilho(); verVisita('.$this->dados[$dia][2][$i]["id"].');"';
	        if ($this->dados[$dia][2][$i]["ultimo_comentario"] && $this->dados[$dia][2][$i]["ultimo_comentario"] <> "Nenhum Comentário Disponível") {
	        	$conteudo .= ' onMouseOver="hintShow(\'&raquo; Último Comentário:<br>'.preg_replace("/[\r\n\t ]+/", " ", $this->dados[$dia][2][$i]["ultimo_comentario"]);
	        	$conteudo .= '\', 300)" onMouseOut="hintHide()"';
	        }
	        $conteudo .= ' ></div>';
	      } else {
	        $conteudo .= '<div style="border:1px solid '.($this->dados[$dia][2][$i]["reservado"] ? $this->cor_reservado[1] : ($this->dados[$dia][2][$i]["status_id"] == 2 ? ($this->dados[$dia][2][$i]["instalado"] ? ($this->dados[$dia][2][$i]["bloqueado"] ? ($this->dados[$dia][2][$i]["suspensao"] ? $this->cor_suspensao[1] : $this->cor_bloqueado[1]) : $this->cor_instalado[1]) : ($this->dados[$dia][2][$i]["agendamentos"] > 1 ? $this->cor_reagendado[1] : $this->cor_agendado[1])) : $this->cor_ativo[1])).'"><div style="font-size:1px;width:6px;height:6px;background-color:'.($this->dados[$dia][2][$i]["reservado"] ? $this->cor_reservado[0] : ($this->dados[$dia][2][$i]["status_id"] == 2 ? ($this->dados[$dia][2][$i]["instalado"] ? ($this->dados[$dia][2][$i]["bloqueado"] ? ($this->dados[$dia][2][$i]["suspensao"] ? $this->cor_suspensao[0] : $this->cor_bloqueado[0]) : $this->cor_instalado[0]) : ($this->dados[$dia][2][$i]["agendamentos"] > 1 ? $this->cor_reagendado[0] : $this->cor_agendado[0])) : $this->cor_ativo[0])).(($this->permissoes["visitas"]["ver"] || $this->permissoes["visitas"]["realocar"]) ? ';cursor:pointer' : '').'" id="agenda_'.$this->dados[$dia][2][$i]["id"].'" onMouseOver="hintShow(\'';
	  			$visita  = '&raquo; Tipo: '.addslashes($this->dados[$dia][2][$i]["tipo"]).'<br>';
	        $visita .= '&raquo; Cliente: '.addslashes($this->dados[$dia][2][$i]["cliente"]).'<br>';
	        $visita .= '&raquo; Endereço: '.addslashes($this->dados[$dia][2][$i]["local"]).'<br>';
	        $visita .= '&raquo; Telefone: '.addslashes($this->dados[$dia][2][$i]["telefone"]);
	        if ($this->dados[$dia][2][$i]["ultimo_comentario"] && $this->dados[$dia][2][$i]["ultimo_comentario"] <> "Nenhum Comentário Disponível") $visita .= '<br>&raquo; Último Comentário:<br>'.preg_replace("/[\r\n\t ]+/"," ",$this->dados[$dia][2][$i]["ultimo_comentario"]);
	        $conteudo .= $visita;
	        $conteudo .= '\', 300)" onMouseOut="hintHide()"';
	        if ($this->permissoes["visitas"]["realocar"] && $this->dados[$dia][2][$i]["status_id"] == 2) $conteudo .= ' onClick="definirVisita('.$this->dados[$dia][2][$i]["id"].')"';
	        if ($this->permissoes["visitas"]["ver"]) $conteudo .= ' onDblClick="resetaGatilho(); verVisita('.$this->dados[$dia][2][$i]["id"].')"';
	        $conteudo .= '>';
	        if (in_array($this->dados[$dia][2][$i]["servico_id"], array(28, 33))) {
	          $conteudo .= '<img src="imagens/faixa.png" width="6" height="6" border="0">';
	        }
	        $conteudo .= '</div></div>';
	        $visitas[] = $visita;
	      }      	
      } else {
        $conteudo .= '<div style="border:1px solid '.$this->cor_livre[1].'"><div style="font-size:1px;width:6px;height:6px;background-color:'.$this->cor_livre[0].'"';
        if ((($hoje == $dia && $hora < "180000") || ($hoje < $dia)) && (($this->permissoes["visitas"]["alocar"] && $i < ($visitas_por_turno - 1)) || $this->permissoes["visitas"]["realocar"])) $conteudo .= "onClick=\"alocarVisita('".substr($dia, 0, 4)."-".substr($dia, 4, 2)."-".substr($dia, 6, 2)."', 2)\" onmouseover=\"this.style.cursor='pointer'\" onmouseout=\"this.style.cursor='default'\"";
        $conteudo .= '></div></div>';
      }
      $conteudo .= '</td>';
      if ($i + 1 == floor($visitas_por_turno / 2)) {
        $conteudo .= '</tr>';
        $conteudo .= '<tr>';
      }
    }
    $conteudo .= '</tr>';
    $conteudo .= '<tr><td height="3"></td></tr>';
    $conteudo = str_replace("[tarde]", implode('<hr style=\\\'height:1;color:#FFFFFF\\\'>', $visitas), $conteudo);
    $conteudo .= '</table>';
    return $conteudo;
  }

	function geraPainelVisitaTecnica($visita_tipo_id, $data, $tipo_listar, $cliente_tipo, $visita_id, $diaSemana) {
		
	    $conteudo .= '<br>';
	    $conteudo .= '<table border="0" cellspacing="0" cellpadding="1" align="center">';
	    
	    if ($diaSemana == 6) {
		    // cotas sabado
			$cotas_manha = $this->buscaVisitaCota($visita_tipo_id, $data, 1, VISITAS_CASAS_SABADO_MANHA, VISITAS_CONDOMINIO_SABADO_MANHA);
		    $cotas_tarde = $this->buscaVisitaCota($visita_tipo_id, $data, 2, VISITAS_CASAS_SABADO_TARDE, VISITAS_CONDOMINIO_SABADO_TARDE);
		    $cotas_noite = $this->buscaVisitaCota($visita_tipo_id, $data, 3, VISITAS_CASAS_SABADO_NOITE, VISITAS_CONDOMINIO_SABADO_NOITE);
	    } elseif ($diaSemana == 0) {
			//cotas Domingo
		    $cotas_manha = $this->buscaVisitaCota($visita_tipo_id, $data, 1, VISITAS_CASAS_DOMINGO_MANHA, VISITAS_CONDOMINIO_DOMINGO_MANHA);
		    $cotas_tarde = $this->buscaVisitaCota($visita_tipo_id, $data, 2, VISITAS_CASAS_DOMINGO_TARDE, VISITAS_CONDOMINIO_DOMINGO_TARDE);
		    $cotas_noite = $this->buscaVisitaCota($visita_tipo_id, $data, 3, VISITAS_CASAS_DOMINGO_NOITE, VISITAS_CONDOMINIO_DOMINGO_NOITE);
	    } else {
			//cotas dias uteis   
		    $cotas_manha = $this->buscaVisitaCota($visita_tipo_id, $data, 1, VISITAS_CASAS_MANHA, VISITAS_CONDOMINIO_MANHA);
		    $cotas_tarde = $this->buscaVisitaCota($visita_tipo_id, $data, 2, VISITAS_CASAS_TARDE, VISITAS_CONDOMINIO_TARDE);
		    $cotas_noite = $this->buscaVisitaCota($visita_tipo_id, $data, 3, VISITAS_CASAS_NOITE, VISITAS_CONDOMINIO_NOITE);
	    }
        
		$cotas_max = max($cotas_manha["casa"], $cotas_manha["condominio"], $cotas_tarde["casa"], $cotas_tarde["condominio"], $cotas_noite["casa"], $cotas_noite["condominio"]);
		// Manhã
		$conteudo .= $this->geraTurno($data, 1, "M", $tipo_listar, $cliente_tipo, $visita_id , $cotas_manha);
		// Separador
		$conteudo .= '<tr><td colspan="'.($cotas_max + 1).'" height="2px"><table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td height="1" bgcolor="#505050"></td></tr></table></td></tr>';
		// Tarde
		$conteudo .= $this->geraTurno($data, 2, "T", $tipo_listar, $cliente_tipo, $visita_id , $cotas_tarde);
		// Separador
		$conteudo .= '<tr><td colspan="'.($cotas_max + 1).'" height="2px"><table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td height="1" bgcolor="#505050"></td></tr></table></td></tr>';
		// Noite
		$conteudo .= $this->geraTurno($data, 3, "N", $tipo_listar, $cliente_tipo, $visita_id, $cotas_noite);
		$conteudo .= '</table>';
		
		return $conteudo;
	
  }
  
	function geraPainelVisitasMigracao($dia) {
		  
		$visitas_por_turno = VISITAS_TURNO_MIGRACAO;
		$hoje = date("Ymd");
		$hora = date("His");
		$visitas = array();

		$conteudo .= '<br>';

		// Manhã
		$conteudo .= '<table border="0" cellspacing="0" cellpadding="1" align="center">';
		$conteudo .= '<tr>';
		$conteudo .= '<td class="txtAzulEscuro10" valign="middle" rowspan="2"><div onMouseOver="hintShow(\'[manha]\', 300)" onMouseOut="hintHide()" class="txtAzulEscuro10" style="cursor:'.($this->dados[$dia][1] ? 'pointer' : 'default').'">M</div></td>';

		for ($i = 0; $i < $visitas_por_turno; $i++) {
		  $conteudo .= '<td valign="middle">';
		  if ($this->dados[$dia][1][$i]) {
			$conteudo .= '<div style="border:1px solid '.($this->dados[$dia][1][$i]["status_id"] == 2 ? ($this->dados[$dia][1][$i]["instalado"] ? ($this->dados[$dia][1][$i]["bloqueado"] ? ($this->dados[$dia][1][$i]["suspensao"] ? $this->cor_suspensao[1] : $this->cor_bloqueado[1]) : $this->cor_instalado[1]) : ($this->dados[$dia][1][$i]["agendamentos"] > 1 ? $this->cor_reagendado[1] : $this->cor_agendado[1])) : $this->cor_ativo[1]).'"><div style="font-size:1px;width:6px;height:6px;background-color:'.($this->dados[$dia][1][$i]["status_id"] == 2 ? ($this->dados[$dia][1][$i]["instalado"] ? ($this->dados[$dia][1][$i]["bloqueado"] ? ($this->dados[$dia][1][$i]["suspensao"] ? $this->cor_suspensao[0] : $this->cor_bloqueado[0]) : $this->cor_instalado[0]) : ($this->dados[$dia][1][$i]["agendamentos"] > 1 ? $this->cor_reagendado[0] : $this->cor_agendado[0])) : $this->cor_ativo[0]).(($this->permissoes["visitas"]["ver"] || $this->permissoes["visitas"]["realocar"]) ? ';cursor:pointer' : '').'" id="agenda_'.$this->dados[$dia][1][$i]["id"].'" onMouseOver="hintShow(\'[manha]';
			$visita  = '&raquo; Tipo: '.addslashes($this->dados[$dia][1][$i]["tipo"]).'<br>';
			$visita .= '&raquo; Cliente: '.addslashes($this->dados[$dia][1][$i]["cliente"]).'<br>';
			$visita .= '&raquo; Endereço: '.addslashes($this->dados[$dia][1][$i]["local"]).'<br>';
			$visita .= '&raquo; Telefone: '.addslashes($this->dados[$dia][1][$i]["telefone"]);
			if ($this->dados[$dia][1][$i]["ultimo_comentario"] && $this->dados[$dia][1][$i]["ultimo_comentario"] <> "Nenhum Comentário Disponível") $visita .= '<br>&raquo; Último Comentário:<br>'.preg_replace("/[\r\n\t ]+/"," ",$this->dados[$dia][1][$i]["ultimo_comentario"]);
			$conteudo .= $visita;
			$conteudo .= '\', 300)" onMouseOut="hintHide()"';
			if ($this->permissoes["visitas"]["realocar_migracao"] && $this->dados[$dia][1][$i]["status_id"] == 2) $conteudo .= ' onClick="definirVisita('.$this->dados[$dia][1][$i]["id"].')"';
			if ($this->permissoes["visitas"]["ver"]) $conteudo .= ' onDblClick="resetaGatilho(); verVisita('.$this->dados[$dia][1][$i]["id"].')"';
			$conteudo .= '>';
			if (in_array($this->dados[$dia][1][$i]["servico_id"], array(28, 33))) {
			  $conteudo .= '<img src="imagens/faixa.png" width="6" height="6" border="0">';
			}
			$conteudo .= '</div></div>';
			$visitas[] = $visita;
		  } else {
			$conteudo .= '<div style="border:1px solid '.$this->cor_livre[1].'"><div style="font-size:1px;width:6px;height:6px;background-color:'.$this->cor_livre[0].'"';
			if ((($hoje == $dia && $hora < "120000") || ($hoje < $dia)) && (($this->permissoes["visitas"]["alocar_migracao"] && $i < ($visitas_por_turno - 1)) || $this->permissoes["visitas"]["realocar_migracao"])) $conteudo .= " onClick=\"alocarVisitaMigracao('".substr($dia, 0, 4)."-".substr($dia, 4, 2)."-".substr($dia, 6, 2)."', 1)\" onmouseover=\"if (visita_ativa) this.style.cursor='pointer'\" onmouseout=\"this.style.cursor='default'\"";
			$conteudo .= '></div></div>';
		  }
		  $conteudo .= '</td>';
		  if ($i + 1 == floor($visitas_por_turno / 2)) {
			$conteudo .= '</tr>';
			$conteudo .= '<tr>';
		  }
		}
		$conteudo .= '</tr>';
		$conteudo = str_replace("[manha]", @implode('<hr style=\\\'height:1;color:#FFFFFF\\\'>', $visitas), $conteudo);

		// Separador
		$conteudo .= '<tr><td></td><td colspan="'.($visitas_por_turno - 1).'" height="2px"><table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td height="1" bgcolor="#505050"></td></tr></table></td></tr>';

		// Tarde
		$visitas = array();
		$conteudo .= '<tr>';
		$conteudo .= '<td class="txtAzulEscuro10" valign="middle" rowspan="2"><div onMouseOver="hintShow(\'[tarde]\', 300)" onMouseOut="hintHide()" class="txtAzulEscuro10" style="cursor:'.($this->dados[$dia][2] ? 'pointer' : 'default').'">T</div></td>';

		for ($i = 0; $i < $visitas_por_turno; $i++) {
			$conteudo .= '<td valign="middle">';
			if (!empty($this->dados[$dia][2][$i])) {
				$conteudo .= '<div style="border:1px solid '.($this->dados[$dia][2][$i]["status_id"] == 2 ? ($this->dados[$dia][2][$i]["instalado"] ? ($this->dados[$dia][2][$i]["bloqueado"] ? ($this->dados[$dia][2][$i]["suspensao"] ? $this->cor_suspensao[1] : $this->cor_bloqueado[1]) : $this->cor_instalado[1]) : ($this->dados[$dia][2][$i]["agendamentos"] > 1 ? $this->cor_reagendado[1] : $this->cor_agendado[1])) : $this->cor_ativo[1]).'"><div style="font-size:1px;width:6px;height:6px;background-color:'.($this->dados[$dia][2][$i]["status_id"] == 2 ? ($this->dados[$dia][2][$i]["instalado"] ? ($this->dados[$dia][2][$i]["bloqueado"] ? ($this->dados[$dia][2][$i]["suspensao"] ? $this->cor_suspensao[0] : $this->cor_bloqueado[0]) : $this->cor_instalado[0]) : ($this->dados[$dia][2][$i]["agendamentos"] > 1 ? $this->cor_reagendado[0] : $this->cor_agendado[0])) : $this->cor_ativo[0]).(($this->permissoes["visitas"]["ver"] || $this->permissoes["visitas"]["realocar"]) ? ';cursor:pointer' : '').'" id="agenda_'.$this->dados[$dia][2][$i]["id"].'" onMouseOver="hintShow(\'';
				$visita  = '&raquo; Tipo: '.addslashes($this->dados[$dia][2][$i]["tipo"]).'<br>';
				$visita .= '&raquo; Cliente: '.addslashes($this->dados[$dia][2][$i]["cliente"]).'<br>';
				$visita .= '&raquo; Endereço: '.addslashes($this->dados[$dia][2][$i]["local"]).'<br>';
				$visita .= '&raquo; Telefone: '.addslashes($this->dados[$dia][2][$i]["telefone"]);
				if ($this->dados[$dia][2][$i]["ultimo_comentario"] && $this->dados[$dia][2][$i]["ultimo_comentario"] <> "Nenhum Comentário Disponível") $visita .= '<br>&raquo; Último Comentário:<br>'.preg_replace("/[\r\n\t ]+/"," ",$this->dados[$dia][2][$i]["ultimo_comentario"]);
				$conteudo .= $visita;
				$conteudo .= '\', 300)" onMouseOut="hintHide()"';
				if ($this->permissoes["visitas"]["realocar"] && $this->dados[$dia][2][$i]["status_id"] == 2) $conteudo .= ' onClick="definirVisita('.$this->dados[$dia][2][$i]["id"].')"';
				if ($this->permissoes["visitas"]["ver"]) $conteudo .= ' onDblClick="resetaGatilho(); verVisita('.$this->dados[$dia][2][$i]["id"].')"';
				$conteudo .= '>';
				if (in_array($this->dados[$dia][2][$i]["servico_id"], array(28, 33))) {
					$conteudo .= '<img src="imagens/faixa.png" width="6" height="6" border="0">';
				}
				$conteudo .= '</div></div>';
				$visitas[] = $visita;
			} else {
				$conteudo .= '<div style="border:1px solid '.$this->cor_livre[1].'"><div style="font-size:1px;width:6px;height:6px;background-color:'.$this->cor_livre[0].'"';
				if ((($hoje == $dia && $hora < "180000") || ($hoje < $dia)) && (($this->permissoes["visitas"]["alocar"] && $i < ($visitas_por_turno - 1)) || $this->permissoes["visitas"]["realocar"])) $conteudo .= "onClick=\"alocarVisitaMigracao('".substr($dia, 0, 4)."-".substr($dia, 4, 2)."-".substr($dia, 6, 2)."', 2)\" onmouseover=\"if (visita_ativa) this.style.cursor='pointer'\" onmouseout=\"this.style.cursor='default'\"";
				$conteudo .= '></div></div>';
			}
			$conteudo .= '</td>';
			if ($i + 1 == floor($visitas_por_turno / 2)) {
				$conteudo .= '</tr>';
				$conteudo .= '<tr>';
			}
		}

		$conteudo .= '</tr>';
		$conteudo .= '<tr><td height="3"></td></tr>';
		$conteudo = str_replace("[tarde]", implode('<hr style=\\\'height:1;color:#FFFFFF\\\'>', $visitas), $conteudo);
		$conteudo .= '</table>';

		return $conteudo;

	}
  
	function buscaVisitaCota($visita_tipo_id, $data, $turno, $cotas_casa, $cotas_condominio) {
	  
		$data = substr($data, 0, 4)."-".substr($data, 4, 2)."-".substr($data, 6, 2);

		$query = "
			SELECT
				vic_cota_residencia AS casa,
				vic_cota_condominio AS condominio
			FROM
				tab_visitas_cotas
			WHERE
				vit_id = {$visita_tipo_id} AND
				vic_data = '$data' AND 
				vic_periodo = $turno ";
				
		$query = $this->bancodedados->query($query);
		$dados = $this->bancodedados->resultadoArray($query);

		if ($dados) {
			$dados["casa"] = intval(is_numeric($dados["casa"]) ? $dados["casa"] : $cotas_casa);
			$dados["condominio"] = intval(is_numeric($dados["condominio"]) ? $dados["condominio"] : $cotas_condominio);
		}else{
			$dados["casa"] = $cotas_casa;
			$dados["condominio"]  = $cotas_condominio;
		}

		return $dados;

	}
  
	function geraTurno($data, $turno, $titulo, $tipo_listar, $cliente_tipo, $visita_id, $cotas) {
		  
		$chamados = array();

		if ($this->dados[$data][$turno]) {
			foreach ($this->dados[$data][$turno] as $visitas) {
				if ($visitas) {
					foreach ($visitas as $visita) {
						$chamados[] = $visita["chamado_id"];
					}
				}
			}
		}

		$chamados = implode(",", $chamados);

		$hoje = date("Ymd");
		$hora = date("His");
		$this->visitas_tecnicas = array();

		$conteudo .= '<tr>';
		$conteudo .= '<td class="txtAzulEscuro10" valign="middle" rowspan="2"><div '.($this->permissoes["visitas"]["alterar_turno"] && ($hoje < $data || ($hoje == $data && (($turno == 1 && $hora < 120000) || ($turno == 2 && $hora < 180000) || ($turno == 3)))) ? 'onclick="alterarTurno('.$data.', '.$turno.');"' : '').'  onMouseOver="hintShow(\'[hint]\', 300)" onMouseOut="hintHide()" class="txtAzulEscuro10" style="cursor:pointer">'.$titulo.'</div>'.($chamados ? '<img border="0" width="10" height="10" style="cursor:pointer" onclick="imprimeChamados(\''.$chamados.'\')" title="Imprimir" src="imagens/imprimir_pequeno.png">' : '').'</td>';
		for ($i = 0; $i < ($cotas["casa"]); $i++) {
			$conteudo .= $this->geraLacuna($data, $visita_id, $tipo_listar, $cliente_tipo, "residencial", $cotas["casa"], $this->cor_livre_casa, $turno, $this->dados[$data][$turno]["casa"][$i]);
		}
		$conteudo .= '</tr>';
		$conteudo .= '<tr>';
		for ($i = 0; $i < ($cotas["condominio"]); $i++) {
			$conteudo .= $this->geraLacuna($data, $visita_id, $tipo_listar, $cliente_tipo, "condominial", $cotas["condominio"], $this->cor_livre_condominio, $turno, $this->dados[$data][$turno]["condominio"][$i]);
		}
		$conteudo .= '</tr>';
		$conteudo = str_replace("[hint]", @implode('<hr style=\\\'height:1;color:#FFFFFF\\\'>', $this->visitas_tecnicas), $conteudo);

		return $conteudo;
		
	}
  
  function geraLacuna($dia, $visita_id, $tipo_listar, $cliente_tipo, $lacuna_tipo, $visitas, $cor_lacuna, $turno, $dados) {
  	$visitas = array();
  	$hoje = date("Ymd");
    $hora = date("His");
  	$conteudo .= '<td valign="middle">';
  	if ($dados) {
      	if ($dados["id"] == $visita_id) {
      		$class = "ativa";
      	} elseif($dados["status_id"] == 2){
      	  	$class = "agendado";
      	}else {
      		$class = "finalizado";
      	}
      	$class .= " ".$lacuna_tipo;
  			if (in_array($dados["cidade_id"], array(13, 26, 153))) {
      		$class .= " garopaba";
  			}
      	$conteudo .= '<div class="lacunaVisita '.$class.'" style="'.(($this->permissoes["visitas"]["ver"] || $this->permissoes["visitas"]["realocar"]) ? ';cursor:pointer' : '').'" id="agenda_'.$dados["id"].'" onMouseOver="hintShow(\'';
      	if ($dados["cliente_id"]) {
        	$visita .= '&raquo; Cliente: '.addslashes($dados["cliente"]).'<br>';
      	} else {
        	$visita .= '&raquo; Condomínio: '.addslashes($dados["condominio"]).'<br>';
      	}
        $visita .= '&raquo; Endereço: '.addslashes($dados["local"]).'<br>';
        $visita .= '&raquo; Telefone: '.addslashes($dados["telefone"]);
        if ($dados["ultimo_comentario"] && $dados["ultimo_comentario"] <> "Nenhum Comentário Disponível") $visita .= '<br>&raquo; Último Comentário:<br>'.preg_replace("/[\r\n\t ]+/"," ",$dados["ultimo_comentario"]);
        $conteudo .= $visita;
        $conteudo .= '\', 300)" onMouseOut="hintHide()"';
        if ($this->permissoes["visitas"]["realocar"] && $dados["status_id"] == 2 && $tipo_listar) $conteudo .= ' onClick="definirVisita('.$dados["id"].', \''.$lacuna_tipo.'\')"';
        if ($this->permissoes["visitas"]["ver"]) $conteudo .= ' onDblClick="resetaGatilho(); verVisita('.$dados["id"].')"';
        $conteudo .= '>';
        $conteudo .= '</div>';
        $this->visitas_tecnicas[] = $visita;
      } else {
        $conteudo .= '<div class="lacunaVisita '.($tipo_listar || ($cliente_tipo == $lacuna_tipo) ? $lacuna_tipo : "").'"';
        if (($tipo_listar || ($cliente_tipo == $lacuna_tipo)) && (($hoje == $dia && (($turno == 1 && $hora < 120000) || ($turno == 2 && $hora < 180000) || ($turno == 3))) || ($hoje < $dia)) && ($this->permissoes["visitas"]["alocar"] || $this->permissoes["visitas"]["realocar"])) $conteudo .= " onClick=\"".($tipo_listar ? "if (visita_ativa && visita_ativa_cliente_tipo == '$lacuna_tipo')" : "")." alocarVisitaTecnica('".substr($dia, 0, 4)."-".substr($dia, 4, 2)."-".substr($dia, 6, 2)."', ".$turno.($_GET["i"] ? ", {$_GET["i"]}" : "").")\" onmouseover=\" ".($tipo_listar ? "if (visita_ativa && visita_ativa_cliente_tipo == '$lacuna_tipo')" : "")." this.style.cursor='pointer'\" onmouseout=\"this.style.cursor='default'\"";
        $conteudo .= '></div>';
      }
      $conteudo .= '</td>';
      return $conteudo;
  }
  
  function getYearHTML($year) {
    $s = "";
    $prev = $this->getCalendarLink(0, $year - 1);
    $next = $this->getCalendarLink(0, $year + 1);

    $s .= "<table class=\"calendar\" border=\"0\">\n";
    $s .= "<tr>";
    $s .= "<td align=\"center\" valign=\"top\" align=\"left\">".(($prev == "") ? "&nbsp;" : "<a href=\"$prev\">&lt;&lt;</a>") ."</td>\n";
    $s .= "<td class=\"calendarHeader\" valign=\"top\" align=\"center\">".(($this->startMonth > 1) ? $year." - ".($year + 1) : $year) ."</td>\n";
    $s .= "<td align=\"center\" valign=\"top\" align=\"right\">".(($next == "") ? "&nbsp;" : "<a href=\"$next\">&gt;&gt;</a>") ."</td>\n";
    $s .= "</tr>\n";
    $s .= "<tr>";
    $s .= "<td class=\"calendar\" valign=\"top\">".$this->getMonthHTML(0 + $this->startMonth, $year, 0) ."</td>\n";
    $s .= "<td class=\"calendar\" valign=\"top\">".$this->getMonthHTML(1 + $this->startMonth, $year, 0) ."</td>\n";
    $s .= "<td class=\"calendar\" valign=\"top\">".$this->getMonthHTML(2 + $this->startMonth, $year, 0) ."</td>\n";
    $s .= "</tr>\n";
    $s .= "<tr>\n";
    $s .= "<td class=\"calendar\" valign=\"top\">".$this->getMonthHTML(3 + $this->startMonth, $year, 0) ."</td>\n";
    $s .= "<td class=\"calendar\" valign=\"top\">".$this->getMonthHTML(4 + $this->startMonth, $year, 0) ."</td>\n";
    $s .= "<td class=\"calendar\" valign=\"top\">".$this->getMonthHTML(5 + $this->startMonth, $year, 0) ."</td>\n";
    $s .= "</tr>\n";
    $s .= "<tr>\n";
    $s .= "<td class=\"calendar\" valign=\"top\">".$this->getMonthHTML(6 + $this->startMonth, $year, 0) ."</td>\n";
    $s .= "<td class=\"calendar\" valign=\"top\">".$this->getMonthHTML(7 + $this->startMonth, $year, 0) ."</td>\n";
    $s .= "<td class=\"calendar\" valign=\"top\">".$this->getMonthHTML(8 + $this->startMonth, $year, 0) ."</td>\n";
    $s .= "</tr>\n";
    $s .= "<tr>\n";
    $s .= "<td class=\"calendar\" valign=\"top\">".$this->getMonthHTML(9 + $this->startMonth, $year, 0) ."</td>\n";
    $s .= "<td class=\"calendar\" valign=\"top\">".$this->getMonthHTML(10 + $this->startMonth, $year, 0) ."</td>\n";
    $s .= "<td class=\"calendar\" valign=\"top\">".$this->getMonthHTML(11 + $this->startMonth, $year, 0) ."</td>\n";
    $s .= "</tr>\n";
    $s .= "</table>\n";

    return $s;
  }

  function adjustDate($month, $year) {
    $a = array();
    $a[0] = $month;
    $a[1] = $year;

    while ($a[0] > 12) {
      $a[0] -= 12;
      $a[1]++;
    }

    while ($a[0] <= 0) {
      $a[0] += 12;
      $a[1]--;
    }

    return $a;
  }

  /*
  The start day of the week. This is the day that appears in the first column
  of the calendar. Sunday = 0.
  */
  var $startDay = 1;

  /*
  The start month of the year. This is the month that appears in the first slot
  of the calendar in the year view. January = 1.
  */
  var $startMonth = 1;

  /*
  The labels to display for the days of the week. The first entry in this array
  represents Sunday.
  */
  var $dayNames = array("Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sábado");

  /*
  The labels to display for the months of the year. The first entry in this array
  represents January.
  */
  var $monthNames = array("Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");


  /*
  The number of days in each month. You're unlikely to want to change this...
  The first entry in this array represents January.
  */
  var $daysInMonth = array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

}

?>
