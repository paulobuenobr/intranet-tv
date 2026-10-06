<?

class filtro {

    var $itens;
    var $valores;
    var $url;
    var $colunas;

    function __construct($itens,$valores,$url,$colunas = 3) {
        
        $this->itens = $itens;
        $this->valores = $valores;
        $this->url = $url;
        $this->colunas = $colunas;
        
        if (sizeof($this->itens)>0) {
            $this->formulario = $this->geraFormulario();
            $this->parametros_sql = $this->geraParametrosSQL();
            $this->parametros_url = $this->geraParametrosURL();
        }
        
    }
    
    function ordernaMultiArray($array, $key_sort) {
        
        $key_sorta = explode(",", $key_sort);
        $keys = array_keys($array[0]);
        $nkeys = array();
        $output = array();
        
        for ($m=0; $m < count($key_sorta); $m++) {
            $nkeys[$m] = trim($key_sorta[$m]);
        }
        
        $n += count($key_sorta);
        for ($i=0; $i < count($keys); $i++) {
            if (!in_array($keys[$i], $key_sorta)) {
                $nkeys[$n] = $keys[$i];
                $n += "1";
            }
        }
        
        for ($u=0;$u<count($array); $u++) {
            for ($s=0; $s<count($nkeys); $s++) {
                $k = $nkeys[$s];
                $output[$u][$k] = $array[$u][$k];
            }
        }
        
        sort($output);
        
        return $output;
    
    }
    
    function geraFormulario() {
        
        $formulario = '<form name="filtro" method="GET">';
        $formulario .= '<table border="0" cellspacing="1" cellpadding="2" width="100%" align="center">';
        $formulario .= $this->geraCamposURL($this->url);
        $formulario .= '<tr class="txtAzulEscuro11">';
        
        $colspan_botoes = $this->colunas;
        
        for ($i = 0; $i < sizeof($this->itens); $i++) {
            $colspan_botoes--;
            $formulario .= '<td width="'.intval(100 / $this->colunas).'%"'.($this->itens[$i]["colspan"] ? " colspan=\"{$this->itens[$i]["colspan"]}\"" : "").'><b>'.$this->itens[$i]["descricao"].':</b><br>';
            $formulario .= $this->geraCampoItem($this->itens[$i], $this->valores[$this->itens[$i]["nome"]]);
            $formulario .= '</td>';
            if (($i + 1) % $this->colunas == 0) {
                $formulario .= '</tr><tr class="txtAzulEscuro11">';
                $colspan_botoes = $this->colunas;
            }
        }
        
        $formulario .= '<td width="'.intval(100 * $colspan_botoes / $this->colunas).'%" align="center" colspan="'.$colspan_botoes.'"><input type="submit" class="buttonAzulEscuroBranco11" name="filtrar" value="Filtrar">';
        $formulario .= '&nbsp;';
        $formulario .= '<input type="button" class="buttonAzulEscuroBranco11" name="limpar" value="Limpar" onClick="window.location=\'' . $this->url . '\'">';
        $formulario .= '</td>';
        $formulario .= '</tr>';
        $formulario .= '</table>';
        $formulario .= '</form>';
        
        return $formulario;
    
    }
    
    function geraParametrosSQL() {
        
        $parametros = array();
        
        for ($i = 0; $i < sizeof($this->itens); $i++) {
            $valor = $this->valores[$this->itens[$i]["nome"]];
            if ("{$valor}" !== "") {
                if ($this->itens[$i]["tipo"] == "select") {
                    for ($j = 0; $j < sizeof($this->itens[$i]["valor"]); $j++) {
                        if ($this->itens[$i]["valor"][$j]["id"] == $valor && $this->itens[$i]["valor"][$j]["where"]) {
                            $parametro = str_replace("[valor]", $valor, $this->itens[$i]["valor"][$j]["where"]);
                        }
                    }
                    if (!$parametro) $parametro = str_replace("[valor]", $valor, $this->itens[$i]["where"]);
                } elseif ($this->itens[$i]["tipo"] == "input") {
                    if (!$valor) $valor = "%";
                        $parametro = str_replace("[valor]", $valor, $this->itens[$i]["where"]);
                    }
                $parametros[] = $parametro;
            }
            unset($parametro);
        }
        
        return $parametros ? implode(" AND\n        ", $parametros) : "1";
    
    }
    
    function geraParametrosURL() {
        
        for ($i = 0; $i < sizeof($this->itens); $i++) {
            if ($this->valores[$this->itens[$i]["nome"]]) $parametros .= "&f[" . $this->itens[$i]["nome"] . "]=" . $this->valores[$this->itens[$i]["nome"]];
        }
        
        return $parametros;
        
    }
    
    function geraCampoItem($item, $valor) {
    	
        if ($item["tipo"] == "select") {
            $campo = '<select class="inputBrancoAzulEscuro11" name="f[' . $item["nome"] . ']" style="width: 100%"' . (($item["disabled"])? ' disabled' : '') . '>';
            $campo .= '<option></option>';
            for ($i = 0; $i < sizeof($item["valor"]); $i++) {
                if (strlen($item["valor"][$i]["nome"]) > 40) $item["valor"][$i]["nome"] = substr(trim($item["valor"][$i]["nome"]),0,40) . "...";
                $campo .= '<option value="' . $item["valor"][$i]["id"] . '"' . ("{$item["valor"][$i]["id"]}" === "{$valor}" ? " selected" : "") . '>' . $item["valor"][$i]["nome"] . '</option>';
            }
            $campo .= '</select>';
        } elseif ($item["tipo"] == "input") {
            if (strpos($item["nome"], "data") !== false) {
                $filtro = ' onKeyPress="return formataData(this, event)" onBlur="return formataData(this, event)"';
            } elseif (strpos($item["nome"], "competencia") !== false) {
                $filtro = ' onKeyPress="return formataCompetencia(this, event)" onBlur="return formataCompetencia(this, event)"';
            } elseif ($item["formatacao"]) {
                $filtro = ' onKeyPress="return '.$item["formatacao"].'" onBlur="return '.$item["formatacao"].'"';
            } else {
                $filtro = "";
            }
            $campo = '<input type="text" class="inputBrancoAzulEscuro11" name="f[' . $item["nome"] . ']" value="' . $valor . '" style="width: 100%"'.$filtro.'>';
        }
        
        return $campo;
        
    }
    
    function geraCamposURL($url) {
        
        $inicio = strpos($url,"?");
        $url = substr($url,$inicio+1,strlen($url));
        $url = explode("&", $url);
        
        for ($i = 0; $i < sizeof($url); $i++) {
            $url[$i] = explode("=", $url[$i]);
            $campos .= '<input type="hidden" name="'.$url[$i][0].'" value="'.$url[$i][1].'">';
        }
        
        return $campos;
        
    }

}

?>
