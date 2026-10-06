<?

class modulo {
	
	/**
	 * Conexão com Banco de Dados
	 * @var bancodedados
	 */
	var $bancodedados;
	
	var $permissoes;
	
	var $acao;
	
	var $conteudo;
	
	var $dados_item;
	
	var $dados_modulo;
	
	var $dados_acao;
	
	function geraTitulo() {
		
		$titulo = '<table border="0" cellspacing="1" cellpadding="3" width="100%">';
		$titulo .= '<tr align="center" class="txtBranco11">';
		$titulo .= '<td bgcolor="#505050"><b>';
		$titulo .= $this->dados_modulo["descricao"];
		$titulo .= '</b></td></tr></table>';
		
		return $titulo;
		
	}
	
	function temPermissao($acao, $modulo = null, $usuario_id = null) {
		
		if (! $usuario_id) {
			
			if (! $modulo) {
				$modulo = $this->dados_modulo["nome"];
			}
			
			return is_array($this->permissoes) && is_string($acao) && array_key_exists($modulo, $this->permissoes) && array_key_exists($acao, $this->permissoes[$modulo]);
		
		} elseif ($acao && $modulo && $usuario_id) {
			
			$query = $this->bancodedados->query("
				SELECT
					COUNT(rel_gru_aco_tempermissao.aco_id)
				FROM
					tab_usuarios
					INNER JOIN rel_usu_gru_fazparte ON (tab_usuarios.usu_id = rel_usu_gru_fazparte.usu_id)
					INNER JOIN rel_gru_aco_tempermissao ON (rel_usu_gru_fazparte.gru_id = rel_gru_aco_tempermissao.gru_id)
					INNER JOIN tab_acoes ON (rel_gru_aco_tempermissao.aco_id = tab_acoes.aco_id)
					INNER JOIN tab_modulos ON (tab_acoes.mod_id = tab_modulos.mod_id)
				WHERE
					tab_modulos.mod_nome = '$modulo' AND
					tab_acoes.aco_nome = '$acao' AND
					tab_usuarios.usu_id = $usuario_id");
			
			return $this->bancodedados->resultado($query) > 0;
			
		}
		
	}
	
	function geraAcoes($acoes) {
		
		$conteudo = array ();
		
		if ($acoes) {
			
			$conteudo[] = "<table border=\"0\" cellspacing=\"1\" cellpadding=\"3\" width=\"100%\"><tr align=\"center\" class=\"txtBranco11\">";
			
			$colunas = 0;
			
			foreach ($acoes as $acao => $nome) {
				if ($this->temPermissao($acao)) {
					$colunas++;
				}
			}
			
			$width = intval(100 / $colunas, 10);
			
			foreach ($acoes as $acao => $nome) {
				if ($this->temPermissao($acao)) {
					if ($acao == $this->dados_acao["nome"] || $acao == preg_replace("/^editar/", "ver", $this->dados_acao["nome"])) {
						$conteudo[] = "<td bgcolor=\"#505050\" width=\"{$width}%\"><strong>&nbsp;{$nome}&nbsp;</strong></td>";
					} else {
						$conteudo[] = "<td bgcolor=\"#828282\" width=\"{$width}%\"><strong>&nbsp;<a href=\"?m={$this->dados_modulo["nome"]}&a={$acao}&i={$this->dados_item["id"]}\" class=\"txtBranco11\">{$nome}</a>&nbsp;</strong></td>";
					}
				}
			}
			
			$conteudo[] = "</tr></table>";
			
		}
		
		return implode($conteudo);
		
	}
	
	function carregaArquivo($arquivo) {
		
		if (! file_exists($arquivo)) {
			if (file_exists(DIR_RAIZ . $arquivo)) {
				$arquivo = DIR_RAIZ . $arquivo;
			} elseif (file_exists("_modulos/{$this->dados_modulo["nome"]}/$arquivo")) {
				$arquivo = "_modulos/{$this->dados_modulo["nome"]}/$arquivo";
			} else {
				return null;
			}
		}
		
		$fp = fopen($arquivo, "r");
		$retorno = fread($fp, filesize($arquivo));
		
		fclose($fp);
		
		return $retorno;
		
	}
	
	function buscaDadosModulo($nome) {
		
		$query = $this->bancodedados->query("
			SELECT 
				mod_id AS id, 
				mod_nome AS nome, 
				mod_descricao AS descricao 
			FROM 
				tab_modulos 
			WHERE 
				mod_nome = '$nome'");
		
		$this->dados_modulo = $this->bancodedados->resultadoArray($query);
		
	}
	
	function buscaDadosAcao($nome) {
		
		$query = $this->bancodedados->query("
			SELECT 
				aco_id AS id, 
				aco_nome AS nome, 
				aco_descricao AS descricao, 
				act_id AS tipo 
			FROM 
				tab_acoes 
			WHERE 
				aco_nome = '$nome' AND
				mod_id = '{$this->dados_modulo["id"]}'");
		
		$this->dados_acao = $this->bancodedados->resultadoArray($query);
		$this->acao = $this->dados_acao["nome"];
		
	}
	
	function geraInput($tipo, $nome, $tamanho = "100%", $valor = null, $enabled = true, $size = null, $formatador = null, $onclick = null, $onchange = null, $checked = false, $class = "inputBrancoAzulEscuro11") {
		
		$style = array ();
		$input = array ();
		
		if ($onclick && $enabled) $style[] = "cursor:pointer";
		if ($tamanho) $style[] = "width:$tamanho";
		$input[] = "type=\"$tipo\"";
		if (is_array($nome)) {
			$input[] = "name=\"{$nome["name"]}\"";
			$input[] = "id=\"{$nome["id"]}\"";
		} else {
			$input[] = "name=\"$nome\"";
			$input[] = "id=\"$nome\"";
		}
		if ($style) $input[] = "style=\"" . implode(";", $style) . "\"";
		if ($valor !== "") $input[] = "value=\"$valor\"";
		if ($size) $input[] = "size=\"$size\"";
		if ($size) $input[] = "maxlength=\"$size\"";
		if ($formatador) $input[] = "onkeypress=\"$formatador\"";
		if ($formatador) $input[] = "onblur=\"$formatador\"";
		if ($onclick) $input[] = "onclick=\"$onclick\"";
		if ($onchange) $input[] = "onchange=\"$onclick\"";
		if ($class) $input[] = "class=\"$class\"";
		if ($checked) $input[] = " checked";
		if (!$enabled) $input[] = " disabled";
		
		return "<input " . implode(" ", $input) . ">";
		
	}
	
	function geraSelect($itens, $selecionado = null, $item_nulo = false, $nome = null, $tamanho = null, $onchange = null, $enabled = true, $class = "inputBrancoAzulEscuro11") {
		
		$id = preg_replace("/\\[(.*)\\]/i", "_$1", $nome);
		
		if ($item_nulo) {
			$select = "<option></option>";
		} else {
			$select = "";
		}
		
		if ($itens) {
			foreach ($itens as $valor => $item) {
				if (is_array($item)) {
					$select .= "<optgroup label=\"$valor\">";
					foreach ($item as $valor_item => $item_item) {
						$select .= "<option value=\"$valor_item\"" . ((is_array($selecionado) ? in_array($valor_item, $selecionado) : "{$valor_item}" === "{$selecionado}") ? " selected" : "") . ">$item_item</option>";
					}
					$select .= "</optgroup>";
				} else {
					$select .= "<option value=\"$valor\"" . ((is_array($selecionado) ? in_array($valor, $selecionado) : "{$valor}" === "{$selecionado}") ? " selected" : "") . ">$item</option>";
				}
			}
		}
		
		
		return "<select name=\"$nome\" id=\"$id\"" . ($tamanho ? " style=\"width:$tamanho\"" : "") . ($onchange ? " onchange=\"$onchange\"" : "") . ($enabled ? "" : " disabled") . " class=\"$class\">" . $select . "</select>";
		
	}
	
	function geraCheckBoxes($nome, $itens, $selecionados = null, $desativados = null, $todos_desativados = false) {
		
		if (! $itens) return "Nenhum registro disponível";
		
		$i = 0;
		$check_boxes = array ();
		
		foreach ($itens as $id => $descricao) {
			$check_boxes[] = "<input type=\"checkbox\" name=\"{$nome}[]\" value=\"$id\" id=\"{$nome}_{$i}\"" . ($selecionados && in_array($id, $selecionados) ? " checked" : "") . ($todos_desativados || ($desativados && in_array($id, $desativados)) ? " disabled" : "") . "><label for=\"{$nome}_{$i}\"> $descricao</label><br>";
			if ($desativados && in_array($id, $desativados)) {
				$check_boxes[] = "<input type=\"hidden\" name=\"{$nome}[]\" value=\"$id\">";
			}
			$i++;
		}
		
		return implode("", $check_boxes);
		
	}
	
	function geraRadioButtons($nome, $itens, $selecionado = null, $desativados = null, $enabled = true, $colunas = 1, $onchange = null) {
		
		$radio_buttons = array ();
		$ids = array_keys($itens);
		
		$radio_buttons[] = "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
		
		for ($i = 0; $i < count($itens) / $colunas; $i++) {
			$radio_buttons[] = "<tr class=\"txtAzulEscuro11\">";
			for ($coluna = 0; $coluna < $colunas; $coluna++) {
				$id_index = (int) (ceil(count($itens) / $colunas) * $coluna) + $i;
				if (array_key_exists($id_index, $ids)) {
					$id = $ids[$id_index];
					$radio_buttons[] = "<td><input type=\"radio\" name=\"{$nome}\" value=\"{$id}\" id=\"{$nome}_{$id}\"" . ($selecionado && $id == $selecionado ? " checked" : "") . (! $enabled || ($desativados && in_array($id, $desativados)) ? " disabled" : "") . ($onchange ? " onchange=\"$onchange\"" : "") . " style=\"margin:2px\"></td><td style=\"padding-right: 5px\"><label for=\"{$nome}_{$id}\">{$itens[$id]}</label></td>";
				}
			}
			$radio_buttons[] = "</tr>";
		}
		
		$radio_buttons[] = "</table>";
		
		return implode("", $radio_buttons);
		
	}
	
	function formataDinheiro($valor, $moeda = 'R$', $exibir_nulo = false) {
		
		$valor_formatado = preg_replace("/[^0-9]/", "", intval($valor, 10));
		if (! $valor_formatado && ! $exibir_nulo) return false;
		$valor_formatado = number_format($valor_formatado / 100, 2, ",", ".");
		if ($moeda) $valor_formatado = "$moeda $valor_formatado";
		if ($valor < 0) $valor_formatado = "<font color=\"red\">- $valor_formatado</font>";
		
		return $valor_formatado;
		
	}
	
}

?>
