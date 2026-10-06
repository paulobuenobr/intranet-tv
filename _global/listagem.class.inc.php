<?php

class listagem {
	
	var $cabecalho;
	var $linhas;
	var $permissoes;
	var $inicio;
	var $fim;
	var $conteudo;
	var $agrupamento;
	var $larguras;
	var $selecionado;
	var $alinhamento;
	var $tamanho_texto;
	var $total;
	var $cores;
	var $borda;
	var $show_cabecalho;
	
	function listagem($cabecalho, $linhas, $agrupamento = 0, $total = null, $larguras = null, $selecionado = null, $alinhamento = null, $cores = null, $tamanho_texto = null, $borda = null, $show_cabecalho = true) {
		
		$this->cabecalho = $cabecalho ? $cabecalho : array();
		$this->linhas = $linhas ? $linhas : array();
		$this->largura = "100%";
		$this->larguras = $larguras ? $larguras : array();
		$this->selecionado = $selecionado;
		$this->agrupamento = $agrupamento;
		$this->alinhamento = $alinhamento ? $alinhamento : array();
		$this->tamanho_texto = $tamanho_texto;
		$this->total = $total;
		$this->cores = $cores ? $cores : array();
		$this->borda = $borda;
		$this->show_cabecalho = $show_cabecalho;
		$this->nowrap = array("instalacao", "mensalidade");
		
		$this->conteudo = "<table border=\"0\" cellspacing=\"1\" cellpadding=\"0\" width=\"100%\"><tr><td>";
		
		if ($this->total) {
			$this->computaTotal();
		}
		
		if ($this->agrupamento > 0) {
			$this->geraListagemAgrupamento();
		} else {
			$this->geraListagem();
		}
		
		$this->conteudo .= "</td></tr></table>";
		
	}
	
	function removeAcentos($string) {
		
		$string = preg_replace("/[áàâãäª]/", "a", $string);
		$string = preg_replace("/[ÁÀÂÃÄ]/", "A", $string);
		$string = preg_replace("/[éèêë]/", "e", $string);
		$string = preg_replace("/[ÉÈÊË]/", "E", $string);
		$string = preg_replace("/[íìîï]/", "i", $string);
		$string = preg_replace("/[ÍIÎÏ]/", "I", $string);
		$string = preg_replace("/[óòôõö°º]/", "o", $string);
		$string = preg_replace("/[ÓÒÔÕÖ]/", "O", $string);
		$string = preg_replace("/[úùûü]/", "u", $string);
		$string = preg_replace("/[ÚÙÛÜ]/", "U", $string);
		$string = str_replace("ç", "c", $string);
		$string = str_replace("Ç", "C", $string);
		$string = str_replace("ñ", "n", $string);
		$string = str_replace("Ñ", "N", $string);
		$string = preg_replace("/[\r\n\t ]+/", " ", $string);
		$string = strtoupper($string);
		
		return $string;
		
	}
	
	function formataNome($nome) {
		
		$nome = strtolower($nome);
		$nome = str_replace(array("ç", "ñ"), array("c", "n"), $nome);
		$nome = preg_replace("/[áàâãäª]/", "a", $nome);
		$nome = preg_replace("/[éèêë]/", "e", $nome);
		$nome = preg_replace("/[íìîï]/", "i", $nome);
		$nome = preg_replace("/[óòôõö°º]/", "o", $nome);
		$nome = preg_replace("/[úùûü]/", "u", $nome);
		$nome = preg_replace("/[^a-z0-9]/", " ", $nome);
		$nome = preg_replace("/[ ]+/", " ", $nome);
		$nome = preg_replace("/[\r\n\t ]/", "_", trim($nome));
		
		return $nome;
		
	}
	
	function formataDinheiro($valor, $exibir_nulo = true, $moeda = 'R$') {
		
		if (! $valor && ! $exibir_nulo) return "";
		$valor_formatado = intval(preg_replace("/[^0-9]/", "", strip_tags($valor)));
		$valor_formatado = number_format($valor_formatado / 100, 2, ",", ".");
		if ($moeda) $valor_formatado = "$moeda $valor_formatado";
		if ($valor < 0) $valor_formatado = "<font color=\"red\">- $valor_formatado</font>";
		
		return $valor_formatado;
		
	}
	
	function formataNumero($valor) {
		
		$valor_formatado = intval(preg_replace("/[^0-9]/", "", strip_tags($valor)));
		
		return number_format($valor_formatado, 0, ",", ".");
		
	}
	
	function computaTotal() {
		
		if ($this->linhas) {
			
			$linha_total = array();
			$linha_total_colchetes = array();
			$valor_colchetes = array();
			
			foreach ($this->linhas as $linha) {
				foreach ($this->total as $campo => $formatacao) {
					if (! array_key_exists($campo, $linha) || ! $linha[$campo] || $linha[$campo] == "-") {
						if (! array_key_exists($campo, $linha_total) || ! $linha_total[$campo]) {
							$linha[$campo] = 0;
						}
					}
					$linha[$campo] = strip_tags($linha[$campo]);
					preg_match("/\\[(.*)\\]/U", $linha[$campo], $valor_colchetes);
					$valor_colchetes[$campo] = $valor_colchetes ? intval($valor_colchetes[1], 10) : null;
					if (array_key_exists($campo, $valor_colchetes) && $valor_colchetes[$campo]) {
						$linha[$campo] = preg_replace("/\\[[^\\]]*\\]/", "", $linha[$campo]);
						$linha_total_colchetes[$campo] += intval($valor_colchetes[$campo], 10);
					}
					if ((strpos($linha[$campo], "/") !== false) || (array_key_exists($campo, $linha_total) && is_array($linha_total[$campo]))) {
						if (! array_key_exists($campo, $linha_total) || ! is_array($linha_total[$campo])) {
							$linha_total[$campo] = array();
						}
						$linha[$campo] = explode("/", $linha[$campo]);
						for ($i = 0; $i < count($linha[$campo]); $i++) {
							$linha_total[$campo][$i] = $formatacao ? intval(preg_replace("/[^0-9\\-]/", "", strip_tags($linha_total[$campo][$i]))) + intval(preg_replace("/[^0-9\\-]/", "", strip_tags($linha[$campo][$i])), 10) : intval($linha_total[$campo][$i], 10) + intval($linha[$campo][$i], 10);
						}
					} else {
						if (! array_key_exists($campo, $linha_total)) {
							$linha_total[$campo] = 0;
						}
						$linha_total[$campo] = $formatacao ? intval(preg_replace("/[^0-9\\-]/", "", strip_tags($linha_total[$campo]))) + intval(preg_replace("/[^0-9\\-]/", "", strip_tags($linha[$campo]))) : intval($linha_total[$campo], 10) + intval($linha[$campo], 10);
					}
					if ($formatacao) {
						if (is_array($linha_total[$campo])) {
							for ($i = 0; $i < count($linha_total[$campo]); $i++) {
								$linha_total[$campo][$i] = $this->{$formatacao}($linha_total[$campo][$i]);
							}
						} else {
							$linha_total[$campo] = $this->{$formatacao}($linha_total[$campo]);
						}
					}
				}
			}
			
			foreach ($linha_total as $campo => $valor) {
				if (is_array($valor)) {
					$linha_total[$campo] = implode(" / ", $valor);
				}
				$linha_total[$campo] = $linha_total[$campo] ? $linha_total[$campo] : 0;
				if ($linha_total_colchetes && is_array($linha_total_colchetes) && array_key_exists($campo, $linha_total_colchetes)) {
					$linha_total[$campo] = "{$linha_total[$campo]} [{$linha_total_colchetes[$campo]}]";
				}
			}
			
			$linha_total["cor_linha"] = "#C8D3DF";
			
			$this->linhas[] = $linha_total;
			
			reset($this->linhas);
			
		}
		
	}
	
	function geraListagem() {
		
		$this->conteudo .= "<table width=\"{$this->largura}\" border=\"0\" cellspacing=\"1\" cellpadding=\"3\" bgcolor=\"" . ($this->borda ? $this->borda : "#252525") . "\" align=\"center\">";
		
		if ($this->show_cabecalho) {
			
			$conteudo_cabecalho = "<tr bgcolor=\"#505050\">";
			$primeiro_campo = current($this->cabecalho);
			$sub_cabecalho = array();
			$sub_larguras = array();
			$i = 0;
			
			foreach ($this->cabecalho as $chave => $campo) {
				if (is_array($campo)) {
					$colspan = count($campo);
					$sub_cabecalho = funcoes::mescla_arrays($sub_cabecalho, $campo);
					for ($j = 0; $j <= count($campo); $j++) {
						$sub_larguras[] = array_key_exists($i + $j, $this->larguras) ? $this->larguras[$i + $j] : 0;
					}
					$i += count($campo) - 1;
					$campo = $chave;
				} else {
					$colspan = 0;
				}
				$align = ! $colspan && array_key_exists($i, $this->alinhamento) && $this->alinhamento[$i] ? $this->alinhamento[$i] : ($campo != $primeiro_campo ? "center" : "left");
				$conteudo_cabecalho .= "<td " . ($colspan ? "colspan=\"$colspan\" " : "rowspan=\"2\" ") . ((array_key_exists($i, $this->larguras) && $this->larguras[$i] && ! is_array($campo)) ? "width=\"{$this->larguras[$i]}\" " : "") . "class=\"txtBranco11\" align=\"{$align}\"><b>" . ($campo ? $campo : "&nbsp;") . "</b></td>";
				$i++;
			}
			
			$conteudo_cabecalho .= "</tr>";
			
			if ($sub_cabecalho) {
				$conteudo_cabecalho .= "<tr bgcolor=\"#505050\">";
				$i = 0;
				foreach ($sub_cabecalho as $campo) {
					$conteudo_cabecalho .= "<td class=\"txtBranco11\" align=\"center\"" . (array_key_exists($i, $sub_larguras) && $sub_larguras[$i] ? " width=\"{$sub_larguras[$i]}\" " : "") . "><b>{$campo}</b></td>";
					$i++;
				}
				$conteudo_cabecalho .= "</tr>";
			} else {
				$conteudo_cabecalho .= "<tr></tr>";
			}
			
			$this->conteudo .= "<thead>{$conteudo_cabecalho}</thead>";
			
		}
		
		$this->cabecalho = $this->trataCabecalho($this->cabecalho);
		
		if (sizeof($this->linhas) > 0) {
			
			$chaves = array_keys($this->cabecalho);
			$guarda_agrupador = null;
			$subgrupo_agrupador_id = null;
			$agrupador = null;
			$subgrupo_item_id = 0;
			$subgrupo_item = null;
			$indice_linha_anterior = null;
			
			foreach ($this->linhas as $indice_linha => $linha) {
				
				if (count($this->linhas) >= 30 && $this->total && $indice_linha == count($this->linhas) - 1) {
					$this->conteudo .= $conteudo_cabecalho;
				}
				
				if ($indice_linha_anterior === null || ! (array_key_exists("rowspan", $this->linhas[$indice_linha_anterior]) && array_key_exists($chaves[0], $this->linhas[$indice_linha_anterior]["rowspan"]) && $this->linhas[$indice_linha_anterior]["rowspan"][$chaves[0]])) {
					
					if ($guarda_agrupador && array_key_exists("subgrupo_agrupador_id", $linha) && $linha["subgrupo_agrupador_id"] && $subgrupo_agrupador_id != $linha["subgrupo_agrupador_id"]) {
						$subgrupo_agrupador_id = $linha["subgrupo_agrupador_id"];
						$this->conteudo .= "<tbody id=\"agrupador_{$guarda_agrupador}_{$subgrupo_agrupador_id}\" style=\"display:none\">";
						$this->conteudo .= "<tr bgcolor=\"" . (array_key_exists("cor_linha", $linha) && $linha["cor_linha"] ? $linha["cor_linha"] : "#E8E8E8") . "\" onclick=\"clicaGrupo('{$guarda_agrupador}_{$subgrupo_agrupador_id}')\" style=\"cursor:pointer\">";
						$this->conteudo .= "<td class=\"txtAzulEscuro11\" colspan=\"" . sizeof($this->cabecalho) . "\">";
						$this->conteudo .= "<img src=\"imagens/adicionar.gif\" id=\"botao_agrupador_{$guarda_agrupador}_{$subgrupo_agrupador_id}\" width=\"9\" height=\"9\" border=\"0\"> &nbsp;{$linha["subgrupo_agrupador_nome"]}</td>";
						$this->conteudo .= "</tr>";
						$agrupador = $guarda_agrupador;
					}
					
					if ($agrupador) {
						$this->conteudo .= "<tbody id=\"$agrupador" . ($subgrupo_agrupador_id ? "_{$subgrupo_agrupador_id}" : "") . "\" style=\"display:none\">";
						$agrupador = "";
					} elseif (array_key_exists("agrupador", $linha) && $linha["agrupador"]) {
						$linha["descricao"] = "<img src=\"imagens/adicionar.gif\" id=\"botao_agrupador_{$linha["agrupador"]}\" width=\"9\" height=\"9\" border=\"0\"> {$linha["descricao"]}";
						$this->conteudo .= "<tbody>";
						$agrupador = $guarda_agrupador = $linha["agrupador"];
						$subgrupo_agrupador_id = "";
					}
					
					if ($guarda_agrupador) {
						$this->conteudo .= "<tr";
					} else {
						$this->conteudo .= "<tbody";
					}
					
					$this->conteudo .= " bgcolor=\"" . (array_key_exists("cor_linha", $linha) && $linha["cor_linha"] ? $linha["cor_linha"] : "#E8E8E8") . "\" ";
					
					if (array_key_exists("subgrupo_agrupador", $linha) && $linha["subgrupo_agrupador"]) {
						$subgrupo_item = $linha["subgrupo_agrupador"];
						$subgrupo_item_id = 0;
					}
					
					if ($subgrupo_item && array_key_exists("subgrupo_item", $linha) && ($subgrupo_item == $linha["subgrupo_item"])) {
						$linha["id"] = ($guarda_agrupador != $subgrupo_item ? "{$guarda_agrupador}_" : "") . $subgrupo_item . "_" . (array_key_exists("subgrupo_agrupador_nome", $linha) && $linha["subgrupo_agrupador_nome"] ? $this->formataNome($linha["subgrupo_agrupador_nome"]) . "_" : "") . ($subgrupo_item_id++);
						$this->conteudo .= " style=\"display:none\"";
					}
					
					if (array_key_exists("subgrupo_agrupador", $linha) && $linha["subgrupo_agrupador"] && array_key_exists("itens", $linha) && $linha["itens"]) {
						$this->conteudo .= " style=\"cursor:pointer\"";
						$this->conteudo .= " onclick=\"clicaSubGrupo('" . ($guarda_agrupador != $linha["subgrupo_agrupador"] ? "{$guarda_agrupador}_" : "") . $linha["subgrupo_agrupador"] . (array_key_exists("subgrupo_agrupador_nome", $linha) && $linha["subgrupo_agrupador_nome"] ? "_" . $this->formataNome($linha["subgrupo_agrupador_nome"]) : "") . "')\"";
					}
					
					if (array_key_exists("agrupador", $linha) && $linha["agrupador"]) {
						$this->conteudo .= " style=\"cursor:pointer\"";
						if (array_key_exists("agrupador_subgrupo", $linha) && $linha["agrupador_subgrupo"]) {
							$this->conteudo .= " onclick=\"clicaGrupo('agrupador_{$linha["agrupador"]}')\"";
						} else {
							$this->conteudo .= " onclick=\"clicaGrupo('{$linha["agrupador"]}')\"";
						}
					}
					
					if (array_key_exists("link_dc", $linha) && $linha["link_dc"]) {
						if (! is_int((strpos($linha["link_dc"], "javascript:")))) {
							$linha["link_dc"] = "window.location='{$linha["link_dc"]}'";
						}
						$this->conteudo .= " ondblclick=\"{$linha["link_dc"]}\"";
					}
					
					if (array_key_exists("link", $linha) && $linha["link"]) {
						$link = ! is_int((strpos($linha["link"], "javascript:"))) ? 'window.location=\'' . $linha["link"] . '\'' : "return " . substr($linha["link"], 11);
						$this->conteudo .= " onmouseover=\"" . (array_key_exists("hint", $linha) && $linha["hint"] ? "hintShow('{$linha["hint"]["conteudo"]}'" . (array_key_exists("largura", $linha["hint"]) && $linha["hint"]["largura"] ? ", {$linha["hint"]["largura"]}" : "") . ");" : "") . "this.style.cursor='pointer'; if (this.style.backgroundColor != '#ffcc66' && this.style.backgroundColor != '#D7D7D7' && this.style.backgroundColor != 'rgb(255, 204, 102)') this.style.backgroundColor='" . (array_key_exists("cor_linha", $linha) && $linha["cor_linha"] ? $this->clareiaCor($linha["cor_linha"]) : "#FFFFFF") . "';\" onmouseout=\"hintHide(); this.style.cursor='default'; if (this.style.backgroundColor != '#ffcc66' && this.style.backgroundColor != '#D7D7D7' && this.style.backgroundColor != 'rgb(255, 204, 102)' && this.style.backgroundColor != 'rgb(215, 226, 238)') this.style.backgroundColor='" . (array_key_exists("cor_linha", $linha) && $linha["cor_linha"] ? $linha["cor_linha"] : "#E8E8E8") . "';\" onclick=\"if (naoEhTagA(event) && naoEhCheckBox(event)) {$link}\"";
					}
					
					if (array_key_exists("id", $linha) && $linha["id"]) {
						$this->conteudo .= " id=\"{$linha["id"]}\"";
						if ($linha["id"] == $this->selecionado) {
							$this->conteudo .= " style=\"background-color:#ffcc66\"";
						}
					}
					
					if (array_key_exists("vertical_align", $linha) && $linha["vertical_align"] && is_string($linha["vertical_align"])) {
						$this->conteudo .= " valign=\"{$linha["vertical_align"]}\"";
					}
					
					$this->conteudo .= ">";
					
				}
				
				if (! $guarda_agrupador) {
					$this->conteudo .= "<tr>";
				}
				
				if (array_key_exists("mesclado", $linha) && $linha["mesclado"]) {
					$this->conteudo .= "<td class=\"txtAzulEscuro11\"" . $this->buscaEstilo(array_key_exists("cor_texto", $linha) ? $linha["cor_texto"] : null) . " colspan=\"" . sizeof($this->cabecalho) . "\">{$linha["mesclado"]}</td>";
				} else {
					for ($i = 0; $i < sizeof($chaves); $i++) {
						$align = array_key_exists($i, $this->alinhamento) && $this->alinhamento[$i] ? $this->alinhamento[$i] : ($i != 0 ? "center" : "left");
						$this->conteudo .= '<td class="txtAzulEscuro11"' . $this->buscaEstilo(array_key_exists("{$chaves[$i]}_cor_texto", $linha) && $linha["{$chaves[$i]}_cor_texto"] ? $linha["{$chaves[$i]}_cor_texto"] : (array_key_exists("cor_texto", $linha) ? $linha["cor_texto"] : null), (array_key_exists("{$chaves[$i]}_hint", $linha) ? $linha["{$chaves[$i]}_hint"] : null)) . " align=\"{$align}\"" . (array_key_exists("{$chaves[$i]}_cor_fundo", $linha) ? " bgcolor=\"{$linha["{$chaves[$i]}_cor_fundo"]}\"" : (array_key_exists($chaves[$i], $this->cores) && $this->cores[$chaves[$i]] && (! array_key_exists("cor_linha", $linha) || ! $linha["cor_linha"] || (array_key_exists("cor_linha", $linha) && $linha["cor_linha"] && array_key_exists($chaves[$i], $this->cores) && in_array(substr($this->cores[$chaves[$i]], 0, 1), array("+", "-")))) ? " bgcolor=\"" . (in_array(substr($this->cores[$chaves[$i]], 0, 1), array("+", "-")) ? $this->somaCores(array_key_exists("cor_linha", $linha) ? $linha["cor_linha"] : null, array_key_exists($chaves[$i], $this->cores) ? $this->cores[$chaves[$i]] : null) : (array_key_exists($chaves[$i], $this->cores) ? $this->cores[$chaves[$i]] : "")) . "\"" : ""));
						if (array_key_exists("{$chaves[$i]}_hint", $linha) && $linha["{$chaves[$i]}_hint"]) {
							$this->conteudo .= " onmouseover=\"hintShow('" . $linha["{$chaves[$i]}_hint"] . "', 120)\" onmouseout=\"hintHide()\"";
						}
						if (array_key_exists("vertical_align", $linha) && $linha["vertical_align"] && is_array($linha["vertical_align"]) && array_key_exists($chaves[$i], $linha["vertical_align"]) && $linha["vertical_align"][$chaves[$i]]) {
							$this->conteudo .= " valign=\"{$linha["vertical_align"][$chaves[$i]]}\"";
						}
						if (array_key_exists("colspan", $linha) && array_key_exists($chaves[$i], $linha["colspan"]) && $linha["colspan"][$chaves[$i]]) {
							$this->conteudo .= " colspan=\"{$linha["colspan"][$chaves[$i]]}\"";
						}
						if (array_key_exists("rowspan", $linha) && array_key_exists($chaves[$i], $linha["rowspan"]) && $linha["rowspan"][$chaves[$i]]) {
							$this->conteudo .= " rowspan=\"{$linha["rowspan"][$chaves[$i]]}\"";
						}
						$this->conteudo .= ">" . (array_key_exists("link", $linha) && $linha["link"] && ! is_int((strpos($linha["link"], "javascript:"))) ? "<a href=\"{$linha["link"]}\" class=\"linkAzulEscuro11\">" : "") . (! array_key_exists($chaves[$i], $linha) || $linha[$chaves[$i]] === "" || $linha[$chaves[$i]] === null ? "&nbsp;" : $linha[$chaves[$i]]) . (array_key_exists("link", $linha) && $linha["link"] && ! is_int((strpos($linha["link"], "javascript:"))) ? "</a>" : "") . "</td>";
						if (array_key_exists("colspan", $linha) && array_key_exists($chaves[$i], $linha["colspan"]) && $linha["colspan"][$chaves[$i]]) {
							$i += $linha["colspan"][$chaves[$i]] - 1;
						}
					}
				}
				
				$this->conteudo .= "</tr>";
				
				if (! $guarda_agrupador && ! (array_key_exists("rowspan", $linha) && array_key_exists($chaves[0], $linha["rowspan"]) && $linha["rowspan"][$chaves[0]])) {
					$this->conteudo .= "</tbody>";
				}
				
				$indice_linha_anterior = $indice_linha;
				
			}
			
		} else {
			$this->conteudo .= "<tr bgcolor=\"#E8E8E8\"><td class=\"txtAzulEscuro11\" align=\"center\" colspan=\"" . sizeof($this->cabecalho) . "\">Nenhum registro disponível</td></tr>";
		}
		
		$this->conteudo .= "</table>";
		
	}
	
	function somaCores($cor, $soma) {
		
		$resultado = array();
		
		if (! $cor) $cor = "#E8E8E8";
		
		for ($i = 0; $i <= 2; $i++) {
			if (substr($soma, 0, 1) == "+") {
				$resultado[$i] = hexdec(substr($cor, ($i * 2) + 1, 2)) + substr($soma, 1);
			} else {
				$resultado[$i] = hexdec(substr($cor, ($i * 2) + 1, 2)) - substr($soma, 1);
			}
			$resultado[$i] = $resultado[$i] < 255 ? $resultado[$i] : 255;
			$resultado[$i] = $resultado[$i] > 0 ? $resultado[$i] : 0;
			$resultado[$i] = sprintf("%02s", dechex($resultado[$i]));
		}
		
		return "#" . implode($resultado);
		
	}
	
	function buscaEstilo($cor_texto = null, $hint = null) {
		
		$estilo = array();
		
		if ($cor_texto) $estilo[] = "color:{$cor_texto}";
		if ($this->tamanho_texto) $estilo[] = "font-size:{$this->tamanho_texto}";
		if ($hint) $estilo[] = "background-image:url('imagens/hint.gif');background-repeat:no-repeat;background-position:right top";
		
		return " style=\"" . implode(";", $estilo) . "\"";
		
	}
	
	function trataCabecalho($cabecalho) {
		
		$cabecalho_tratado = array();
		
		foreach ($cabecalho as $chave => $valor) {
			if (is_array($valor)) {
				$cabecalho_tratado = funcoes::mescla_arrays($cabecalho_tratado, $valor);
			} else {
				$cabecalho_tratado[$chave] = $valor;
			}
		}
		
		return $cabecalho_tratado;
		
	}
	
	function geraListagemAgrupamento() {
		
		$this->conteudo .= "<table width=\"{$this->largura}\" border=\"0\" cellspacing=\"1\" cellpadding=\"3\" bgcolor=\"#252525\">";
		
		if ($this->show_cabecalho) {
			
			$sub_cabecalho = null;
			$this->conteudo .= "<tr bgcolor=\"#505050\">";
			$primeiro_campo = current($this->cabecalho);
			$i = 0;
			
			foreach ($this->cabecalho as $chave => $campo) {
				if (is_array($campo)) {
					$colspan = count($campo);
					$sub_cabecalho = funcoes::mescla_arrays($sub_cabecalho, $campo);
					$campo = $chave;
				} else {
					$colspan = 0;
				}
				$align = ! $colspan && array_key_exists($i, $this->alinhamento) && $this->alinhamento[$i] ? $this->alinhamento[$i] : ($campo != $primeiro_campo ? "center" : "left");
				$this->conteudo .= "<td " . ($colspan ? "colspan=\"$colspan\" " : "rowspan=\"2\" ") . (array_key_exists($i, $this->larguras) && $this->larguras[$i] ? "width=\"{$this->larguras[$i]}\" " : "") . "class=\"txtBranco11\" align=\"{$align}\"><b>" . ($campo ? $campo : "&nbsp;") . "</b></td>";
				$i++;
			}
			
			$this->conteudo .= "</tr>";
			
			if ($sub_cabecalho) {
				$this->conteudo .= "<tr bgcolor=\"#505050\">";
				foreach ($sub_cabecalho as $campo) {
					$this->conteudo .= "<td class=\"txtBranco11\" align=\"center\"><b>{$campo}</b></td>";
				}
				$this->conteudo .= "</tr>";
			} else {
				$this->conteudo .= "<tr></tr>";
			}
			
		}
		
		$this->cabecalho = $this->trataCabecalho($this->cabecalho);
		
		$chaves = array_keys($this->cabecalho);
		
		if (count($this->linhas) > 0) {
			$dados = array();
			foreach ($this->linhas as $linha) {
				$comando = "\$dados";
				for ($i = 0; $i < $this->agrupamento; $i++) {
					$comando .= array_key_exists($chaves[$i], $linha) ? "[\"{$linha[$chaves[$i]]}\"]" : "[\"\"]";
				}
				$comando .= "[] = \$linha;";
				eval($comando);
			}
			$this->conteudo .= "<tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">";
			$this->conteudo .= $this->geraListagemAgrupamentoLinhas($dados, $this->agrupamento);
		} else {
			$this->conteudo .= "<tr bgcolor=\"#E8E8E8\"><td class=\"txtAzulEscuro11\" align=\"center\" colspan=\"" . sizeof($this->cabecalho) . "\">Nenhum registro disponível</td></tr>";
		}
		
		$this->conteudo .= "</table>";
		
	}
	
	function geraListagemAgrupamentoLinhas($dados, $nivel) {
		
		$conteudo = "";
		
		if ($nivel > 0) {
			
			$primeira_linha = true;
			$i = 0;
			
			do {
				
				$dado = current($dados);
				
				if ($i != 0) {
					$conteudo .= "<tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\">";
				}
				
				$alinhamento = array_key_exists($this->agrupamento - $nivel, $this->alinhamento) && $this->alinhamento[$this->agrupamento - $nivel] ? $this->alinhamento[$this->agrupamento - $nivel] : "left";
				$cor = $this->buscaCor($dado);
				
				$conteudo .= "<td align=\"{$alinhamento}\" rowspan=\"" . $this->calculaSpan($dado, $nivel) . "\"" . (array_key_exists($this->agrupamento - $nivel, $this->larguras) && $this->larguras[$this->agrupamento - $nivel] ? " width=\"" . $this->larguras[$this->agrupamento - $nivel] . "\"" : "") . ($cor ? " bgcolor=\"$cor\"" : "") . ">" . key($dados) . "</td>";
				$conteudo .= $this->geraListagemAgrupamentoLinhas($dado, $nivel - 1);
				
				$i++;
				
			} while (next($dados));
			
		} else {
			
			$primeira_linha = true;
			$chaves_original = array_keys($this->cabecalho);
			$chaves = array_slice($chaves_original, $this->agrupamento);
			
			do {
				
				$linha = current($dados);
				
				if (! array_key_exists("id", $linha)) $linha["id"] = "";
				if (! array_key_exists("link", $linha)) $linha["link"] = "";
				if (! array_key_exists("colspan", $linha)) $linha["colspan"] = array();
				if (! array_key_exists("cor_linha", $linha)) $linha["cor_linha"] = "";
				
				if (! $primeira_linha) {
					$conteudo .= "<tr bgcolor=\"#E8E8E8\" class=\"txtAzulEscuro11\" id=\"{$linha["id"]}\">";
				} else {
					$primeira_linha = false;
				}
				
				if ($linha["link"]) {
					$link = ! is_int((strpos($linha["link"], "javascript:"))) ? "window.location='{$linha["link"]}'" : $linha["link"];
				}
				
				$id_linha = md5(serialize($linha));
				$quant_campos = sizeof($chaves);
				
				for ($i = 0; $i < $quant_campos; $i++) {
					
					$complemento = array_key_exists("link", $linha) && $linha["link"] ? " id=\"la_{$id_linha}_{$i}\" onmouseover=\"mOverLA('{$id_linha}', '{$quant_campos}', '" . ($linha["cor_linha"] ? $this->clareiaCor($linha["cor_linha"]) : "#FFFFFF") . "')\" onmouseout=\"mOutLA('{$id_linha}', '{$quant_campos}', '" . ($linha["cor_linha"] ? $linha["cor_linha"] : "#E8E8E8") . "')\" onclick=\"if (naoEhTagA(event) && naoEhCheckBox(event)) {$link}\"" : "";
					
					if (array_key_exists($chaves[$i], $linha["colspan"]) && $linha["colspan"][$chaves[$i]] > 1) {
						$complemento .= " colspan=\"{$linha["colspan"][$chaves[$i]]}\"";
						$i += $linha["colspan"][$chaves[$i]] - 1;
					}
					
					if (in_array($chaves[$i], $this->nowrap)) $complemento .= " nowrap";
					
					$alinhamento = array_key_exists($i + $this->agrupamento, $this->alinhamento) && $this->alinhamento[$i + $this->agrupamento] ? $this->alinhamento[$i + $this->agrupamento] : "center";
					
					$conteudo .= "<td{$complemento} align=\"{$alinhamento}\"" . (array_key_exists($i + $this->agrupamento, $this->larguras) && $this->larguras[$i + $this->agrupamento] ? "width=\"" . $this->larguras[$i + $this->agrupamento] . "\"" : "") . ($linha["cor_linha"] ? " bgcolor=\"{$linha["cor_linha"]}\"" : "") . ">";
					$conteudo .= $linha["link"] && ! is_int((strpos($linha["link"], "javascript:"))) ? "<a href=\"{$linha["link"]}\" class=\"linkAzulEscuro11\">" : "";
					$conteudo .= array_key_exists($chaves[$i], $linha) && $linha[$chaves[$i]] ? $linha[$chaves[$i]] : "&nbsp;";
					$conteudo .= $linha["link"] && ! is_int((strpos($linha["link"], "javascript:"))) ? "</a>" : "";
					$conteudo .= "</td>";
				}
				
				$conteudo .= "</tr>";
				
			} while (next($dados));
			
		}
		return $conteudo;
		
	}
	
	function clareiaCor($cor) {
		
		return "#FFFFFF";
		
	}
	
	function buscaCor($dados) {
		
		$cores = array();
		
		if (is_array($dados)) {
			return "";
		} elseif (is_int(key($dados))) {
			foreach ($dados as $linha) {
				$cores[] = $linha["cor_linha"];
			}
		} else {
			return $this->buscaCor(current($dados));
		}
		
		$cores = array_unique($cores);
		
		return count($cores) == 1 ? $cores[0] : "";
		
	}
	
	function calculaSpan($dados, $nivel) {
		
		if ($nivel == 1) {
			return count($dados);
		} else {
			$total = 0;
			foreach ($dados as $sub_nivel) {
				$total += $this->calculaSpan($sub_nivel, $nivel - 1);
			}
			return $total;
		}
		
	}
	
}

?>