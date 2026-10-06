<?

class funcoes {

    static $feriados_nacionais = array(
        "01-01" => "Confraternização Universal",
        "04-21" => "Tiradentes",
        "05-01" => "Dia do Trabalho",
        "09-07" => "Independência do Brasil",
        "10-12" => "Nossa Senhora Aparecida",
        "11-02" => "Finados",
        "11-15" => "Proclamação da República",
        "12-25" => "Natal"
    );
    
    static $feriados_municipais = array(
        "03-19" => "Aniversário de São José",
        "03-23" => "Aniversário de Florianópolis"
    );
    
    static function mescla_arrays() {
        
        $retorno = array();
        $argumentos = func_get_args();
        
        foreach ($argumentos as $argumento) {
            if ($argumento) {
                $retorno = array_merge($retorno, $argumento);
            }
        }
        
        return $retorno;
        
    }
    
    static function mescla_arrays_recursivo() {
        
        $retorno = array();
        $argumentos = func_get_args();
        
        foreach ($argumentos as $argumento) {
            if ($argumento) {
                $retorno = array_merge_recursive($retorno, $argumento);
            }
        }
        
        return $retorno;
        
    }
    
    static function ehFeriado($dia, $mes, $ano) {
        
        if ($dia && $mes && $ano) {
            if (funcoes::ehSextaFeiraSanta($dia, $mes, $ano)) return "Sexta-Feira Santa";
            if (funcoes::ehCarnaval($dia, $mes, $ano)) return "Carnaval";
            if (funcoes::ehCorpusChristi($dia, $mes, $ano)) return "Corpus Christi";
            $feriado = funcoes::buscaFeriadoNacionalFixo($dia, $mes);
            if ($feriado) return $feriado;
            $feriado = funcoes::buscaFeriadoMunicipalFixo($dia, $mes);
            if ($feriado) return $feriado;
        }
        
        return false;
    
    }
    
    static function ehSextaFeiraSanta($dia, $mes, $ano) {

        return date("Y-m-d", strtotime("$ano-$mes-$dia 2 days")) == funcoes::buscaDataPascoa($ano);

    }
    
    static function ehCarnaval($dia, $mes, $ano) {

        return date("Y-m-d", strtotime("$ano-$mes-$dia 47 days")) == funcoes::buscaDataPascoa($ano);

    }
    
    static function ehCorpusChristi($dia, $mes, $ano) {

        return date("Y-m-d", strtotime("$ano-$mes-$dia 60 days ago")) == funcoes::buscaDataPascoa($ano);

    }

    static function buscaDataPascoa($ano) {
        
        $ano = $ano ? $ano : date("Y");
        
        if ($ano < 1583) {
            $A = ($ano % 4);
            $B = ($ano % 7);
            $C = ($ano % 19);
            $D = ((19 * $C + 15) % 30);
            $E = ((2 * $A + 4 * $B - $D + 34) % 7);
            $mes = (int)(($D + $E + 114) / 31);
            $dia = (($D + $E + 114) % 31) + 1;
        } else {
            $A = ($ano % 19);
            $B = (int)($ano / 100);
            $C = ($ano % 100);
            $D = (int)($B / 4);
            $E = ($B % 4);
            $F = (int)(($B + 8) / 25);
            $G = (int)(($B - $F + 1) / 3);
            $H = ((19 * $A + $B - $D - $G + 15) % 30);
            $I = (int)($C / 4);
            $K = ($C % 4);
            $L = ((32 + 2 * $E + 2 * $I - $H - $K) % 7);
            $M = (int)(($A + 11 * $H + 22 * $L) / 451);
            $mes = (int)(($H + $L - 7 * $M + 114) / 31);
            $dia = (($H + $L - 7 * $M + 114) % 31) + 1;
        }
        
        return "{$ano}-{$mes}-{$dia}";
        
    }
    
    static function buscaFeriadoNacionalFixo($dia, $mes) {
      
        $data = sprintf("%02s-%02s", $mes, $dia);
        
        if (array_key_exists($data, funcoes::$feriados_nacionais)) {
            return funcoes::$feriados_nacionais[$data];
        }
        
    }

    static function buscaFeriadoMunicipalFixo($dia, $mes) {
      
        $data = sprintf("%02s-%02s", $mes, $dia);
        
        if (array_key_exists($data, funcoes::$feriados_municipais)) {
            return funcoes::$feriados_municipais[$data];
        }
        
    }

}

?>
