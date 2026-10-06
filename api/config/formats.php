<?php

class formats {
    
    private $feriados_nacionais = array(
        "01-01" => "Confraternização Universal",
        "04-21" => "Tiradentes",
        "05-01" => "Dia do Trabalho",
        "09-07" => "Independência do Brasil",
        "10-12" => "Nossa Senhora Aparecida",
        "11-02" => "Finados",
        "11-15" => "Proclamação da República",
        "12-25" => "Natal"
    );
    
    private $feriados_municipais = array(
        "03-19" => "Aniversário de São José",
        "03-23" => "Aniversário de Florianópolis"
    );
    
    
    public function LimpaCaracter($str) {
        $what = array( 'á','à','ã','â','ä','é','è','ê','ë','í','ì','ï','ó','ò','õ','ô','ö','ú','ù','û','ü','À','À','É','Í','Ó','Ú','ñ','n','ç','Ç',' ','-','(',')',',',';',':','|','!','"','#','$','%','&','/','=','?','~','^','>','<','º','ª' );
        $by   = array( 'a','a','a','a','a','e','e','e','e','i','i','i','o','o','o','o','o','u','u','u','u','A','A','E','I','O','U','n','n','c','C','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_' );
        return str_replace($what, $by, $str);
    }
    
    public function codificacao($string) {
        return mb_detect_encoding($string.'x', 'UTF-8, ISO-8859-1');
    }
    
    public function converteCodificacao($string) {
        if ( formats.codificacao($string) == 'ISO-8859-1' ) {
            return utf8_decode($string);
        }
        else {
            return $string;
        }
    }
    
    
    
    public function isUTF8($string) {
        return (bool) preg_match('%^(?:'.
            '[\x09\x0A\x0D\x20-\x7E]|'.
            '[\xC2-\xDF][\x80-\xBF]|'.
            '\xE0[\xA0-\xBF][\x80-\xBF]|'.
            '[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|'.
            '\xED[\x80-\x9F][\x80-\xBF]|'.
            '\xF0[\x90-\xBF][\x80-\xBF]{2}|'.
            '[\xF1-\xF3][\x80-\xBF]{3}|'.
            '\xF4[\x80-\x8F][\x80-\xBF]{2}'.
            ')*$%xs',
            $string);
    }
    
    
    public function evalmath($equation) {
        $result = 0;
        // sanitize imput
        $equation = preg_replace("/[^a-z0-9+\-.*\/()%]/","",$equation);
        // convert alphabet to $variabel
        $equation = preg_replace("/([a-z])+/i", "\$$0", $equation);
        // convert percentages to decimal
        $equation = preg_replace("/([+-])([0-9]{1})(%)/","*(1\$1.0\$2)",$equation);
        $equation = preg_replace("/([+-])([0-9]+)(%)/","*(1\$1.\$2)",$equation);
        $equation = preg_replace("/([0-9]{1})(%)/",".0\$1",$equation);
        $equation = preg_replace("/([0-9]+)(%)/",".\$1",$equation);
        if ( $equation != "" ){
            $result = @eval("return " . $equation . ";" );
        }
        if ($result == null) {
            throw new Exception("Unable to calculate equation");
        }
        return $result;
    }
    
    
    public function AddZero($num, $i) {
        return str_pad($num, $i, '0', STR_PAD_LEFT);
    }
    
    public function dataExtenso($dataSQL) {
        $Meses = array('','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Junho','Agosto','Setembro','Outubro','Novembro','Dezembro');
        $tmp = explode('-', $dataSQL);
        return $tmp[2].' de '.$Meses[intval($tmp[1])].' de '.$tmp[0];
    }
    
    
    public function numeroExtenso($valor = 0, $bolExibirMoeda = true, $bolPalavraFeminina = false ) {
        $singular = null;
        $plural = null;
        if ( $bolExibirMoeda ) {
            $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
            $plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões","quatrilhões");
        }
        else {
            $singular = array("", "", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
            $plural = array("", "", "mil", "milhões", "bilhões", "trilhões","quatrilhões");
        }
        $c = array("", "cem", "duzentos", "trezentos", "quatrocentos","quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
        $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta","sessenta", "setenta", "oitenta", "noventa");
        $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze","dezesseis", "dezesete", "dezoito", "dezenove");
        $u = array("", "um", "dois", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
        if ( $bolPalavraFeminina ) {
            if ($valor == 1)    $u = array("", "uma", "duas", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
            else                $u = array("", "um", "duas", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
            $c = array("", "cem", "duzentas", "trezentas", "quatrocentas","quinhentas", "seiscentas", "setecentas", "oitocentas", "novecentas");
        }
        $z = 0;
        $valor = number_format( $valor, 2, ".", "." );
        $inteiro = explode( ".", $valor );
        for ( $i = 0; $i < count( $inteiro ); $i++ ) for ( $ii = mb_strlen( $inteiro[$i] ); $ii < 3; $ii++ ) $inteiro[$i] = "0" . $inteiro[$i];
        // $fim identifica onde que deve se dar junção de centenas por "e" ou por "," ;)
        $rt = null;
        $fim = count( $inteiro ) - ($inteiro[count( $inteiro ) - 1] > 0 ? 1 : 2);
        for ( $i = 0; $i < count( $inteiro ); $i++ ) {
            $valor = $inteiro[$i];
            $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
            $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
            $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
            $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
            $t = count( $inteiro ) - 1 - $i;
            $r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
            if ( $valor == "000") $z++;
            elseif ( $z > 0 )     $z--;
            if ( ($t == 1) && ($z > 0) && ($inteiro[0] > 0) ) $r .= ( ($z > 1) ? " de " : "") . $plural[$t];
            if ( $r ) $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
        }
        $rt = mb_substr( $rt, 1 );
        return($rt ? trim( $rt ) : "zero");
    }
    
    public function nuMeses($data1 = 'm/Y', $data2 = 'm/Y') {
        $meses = 0;
        if ($data1 != '' && $data2) {
            $dt1 = explode('/', $data1 );
            $dt2 = explode('/', $data2 );
            
            $datetime1 = date_create($dt1[1].'-'.$dt1[0].'-01');
            $datetime2 = date_create($dt2[1].'-'.$dt2[0].'-01');
            
            $interval = date_diff($datetime1, $datetime2);
            
            $anos = $interval->format('%y');
            $meses = $interval->format('%m')+1;
            $meses += $anos*12;
        }
        return $meses;
    }
    
    public function diffDate($d1, $d2, $h1='00:00:00', $h2='00:00:00', $type='H', $sep='/')
    {
        $d1 = explode($sep, $d1);
        $d2 = explode($sep, $d2);
        
        $h1 = explode(':', $h1);
        $h2 = explode(':', $h2);
        
        switch ($type)
        {
            case 'A': $X = 31536000;   break;
            case 'M': $X = 2592000;    break;
            case 'D': $X = 86400;      break;
            case 'H': $X = 3600;       break;
            case 'MI':$X = 60;         break;
            default:  $X = 1;          break;
        }
        
        return floor( ( ( mktime($h2[0], $h2[1], $h2[2], $d2[1], $d2[2], $d2[0]) - mktime($h1[0], $h1[1], $h1[2], $d1[1], $d1[2], $d1[0] ) ) / $X ) );
        
    }
    
    public function diferencaDias($dt1, $dt2) {
        return (int) ((strtotime($dt2) - strtotime($dt1)) / 60 / 60 / 24);
    }
    
    public function ValidMail($var) {
        if (  preg_match('/^[^@]+@[^@]+\.[a-z]{2,6}$/i', $var))
            return true;
        else
            return false;
    }
    
    public function ValidCEP($var) {
        if (strlen($var) == 9) {
            return preg_match("/^[0-9]{5}\-[0-9]{3}$/", $var);
        }
        else {
            return false;
        }
    }
    
    public function ValidPhoneSemDDD($var) {
        switch (strlen($var)) {
            case 10: 
                return preg_match("/^[0-9]{5}\-[0-9]{4}$/", $var);
                break;
            case 9:
                return preg_match("/^[0-9]{4}\-[0-9]{4}$/", $var);
                break;
            default :
                return false;
                break;
        }
    }
    
    public function ValidPhone($var) {
        $var = $this->LimpaPhone($var);
        if (strlen($var) > 11 && strlen($var < 10)) {
            return false;
        } elseif ($var[2] >= 7) {
            return $this->ValidPhoneCel($var);
        } else {
            return $this->ValidPhoneFix($var);
        }
    }
    
    public function ValidPhoneCel($var) {
        switch (strlen($var)) {
            case 11 :
                return eregi("^([1][1-9])[0-9]{9}$", $var);
                break;
            case 10 :
                return eregi("^([2-9][1-9])([0-9]{8})$", $var);
                break;
            default :
                return false;
                break;
        }
    }
    
    public function ValidPhoneFix($var) {
        if (strlen($var) == 11) {
            return false;
        } else {
            return eregi("^([1-9][1-9])([0-9]{8})$", $var);
        }
    }
    
    public function LimpaPhone($var) {
        $var = preg_replace("/[^0-9]/", "", $var);
        if ($var[0] == "0") {
            $var = substr($var, 1);
        }
        return $var;
    }
    
    public function FormatPhone($var, $nobreak = false) {
        $var = $this->LimpaPhone($var);
        $return = '(' . substr($var, 0, 2) . ') ';
        $var = substr($var, 2);
        $return .= substr($var, 0, - 4) . '-' . substr($var, - 4);
        if ($nobreak) {
            $return = str_replace(' ', '&nbsp;', $return);
        }
        return $return;
    }
    
    public function ValidDtBr($var) {
        if (preg_match('/^([0-2][0-9]|[3][0-1]|[0-9])\/([0][0-9]|[1][0-2]|[0-9])\/[0-9]{4}$/', $var)) {
            return true;
        } else {
            return false;
        }
    }
    public function ValidDtSQL($var) {
        return preg_match('/^([0-9]{2}|[0-9]{4})-([0][0-9]|[1][0-2]|[0-9])-([0-2][0-9]|[3][0-1]|[0-9])$/', $var);
    }
    public function ValidDtUsa($var) {
        if (preg_match('/^([0][0-9]|[1][0-2]|[0-9])\/([0-2][0-9]|[3][0-1]|[0-9])\/([0-9]{2}|[0-9]{4})$/', $var)) {
            return true;
        } elseif (preg_match('/^([0][0-9]|[1][0-2]|[0-9])-([0-2][0-9]|[3][0-1]|[0-9])-([0-9]{2}|[0-9]{4})$/', $var)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function ValidDate($var) {
        if ($this->ValidDtBr($var)) {
            return true;
        } elseif ($this->ValidDtUsa($var)) {
            return true;
        } elseif ($this->ValidDtSQL($var)) {
            return true;
        } else
            return false;
    }
    
    public function FormatDate($var, $sql = false) {
        if ($this->ValidDtBr($var)) {
            $tmp = explode('/', $var);
            if (empty($tmp[1])) {
                $tmp = explode('-', $var);
            }
            if ($tmp[2] > 99) {
                $ano = $this->AddZero($tmp[2], 4);
            } elseif ($tmp[2] > date('y')) {
                $ano = '19' . $this->AddZero($tmp[2], 2);
            } else {
                $ano = '20' . $this->AddZero($tmp[2], 2);
            }
            if ($sql) {
                return $ano . '-' . $this->AddZero($tmp[1], 2) . '-' . $this->AddZero($tmp[0], 2);
            } else {
                return $this->AddZero($tmp[0], 2) . '/' . $this->AddZero($tmp[1], 2) . '/' . $ano;
            }
        } elseif ($this->ValidDtUsa($var)) {
            $tmp = explode('/', $var);
            if (empty($tmp[1])) {
                $tmp = explode('-', $var);
            }
            if ($tmp[2] > 99) {
                $ano = $this->AddZero($tmp[2], 4);
            } elseif ($tmp[2] > date('y')) {
                $ano = '19' . $this->AddZero($tmp[2], 2);
            } else {
                $ano = '20' . $this->AddZero($tmp[2], 2);
            }
            if ($sql) {
                return $ano . '-' . $this->AddZero($tmp[0], 2) . '-' . $this->AddZero($tmp[1], 2);
            } else {
                return $this->AddZero($tmp[1], 2) . '/' . $this->AddZero($tmp[0], 2) . '/' . $ano;
            }
        } elseif ($this->ValidDtSQL($var)) {
            $tmp = explode('-', $var);
            if ($tmp[0] > 99) {
                $ano = $this->AddZero($tmp[0], 4);
            } elseif ($tmp[0] > date('y')) {
                $ano = '19' . $this->AddZero($tmp[0], 2);
            } else {
                $ano = '20' . $this->AddZero($tmp[0], 2);
            }
            if ($sql) {
                return $ano . '-' . $this->AddZero($tmp[1], 2) . '-' . $this->AddZero($tmp[2], 2);
            } else {
                return $this->AddZero($tmp[2], 2) . '/' . $this->AddZero($tmp[1], 2) . '/' . $ano;
            }
        }
    }
    
    public function FormatDtSQL2Br($var) {
        $tmp = explode('-', $var);
        return $tmp[2] . '/' . $tmp[1] . '/' . $tmp[0];
    }
    
    public function FormatDtTimeSQL2Br($var) {
        $tmp = explode(' ', $var);
        $tmp2 = explode('-', $tmp[0]);
        return $tmp2[2] . '/' . $tmp2[1] . '/' . $tmp2[0] . ' ' . $tmp[1];
    }
    
    public function ValidTime($var) {
        if (preg_match('/^([0-9]|[0-1][0-9]|[2][0-3]):([0-5][0-9])$/', $var)) {
            return true;
        } else {
            if (preg_match('/^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$/', $var)) {
                return true;
            } else {
                return false;
            }
        }
    }
    
    public function FormatTime($var) {
        $tmp = explode(':', $var);
        return $this->AddZero($tmp[0], 2) . ':' . $this->AddZero($tmp[1], 2) . ':' . (isset($tmp[2]) ? $this->AddZero($tmp[2], 2) : '00');
    }
    
    public function FormatTimeHM($var) {
        $tmp = explode(':', $var);
        return $this->AddZero($tmp[0], 2) . ':' . $this->AddZero($tmp[1], 2) . 'hs';
    }
    
    public function ValidDateTime($var) {
        $tmp = explode(' ', $var);
        if ($this->ValidDate($tmp[0]) && $this->ValidTime($tmp[1])) {
            return true;
        } else {
            return false;
        }
    }
    
    public function FormatDateTime($var, $sql = false) {
        $tmp = explode(' ', $var);
        return $this->FormatDate($tmp[0], $sql) . ' - ' . $this->FormatTimeHM($this->FormatTime($tmp[1]) );
    }
    
    public function ValidNumber($var) {
        if (preg_match('/^[0-9]+$/', $var)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function Float2Int($var) {
        $cifra = substr($var, 0, 2);
        if ($cifra == 'R$') {
            $var = trim(str_replace($cifra, '', $var));
        } else {
            $cifra = substr($var, 0, 2);
            if ($cifra == 'U$') {
                $var = trim(str_replace($cifra, '', $var));
            } else {
                $cifra = substr($var, 0, 1);
                if ($cifra == '$') {
                    $var = trim(str_replace($cifra, '', $var));
                }
            }
        }
        
        $decimal = substr($var, - 2);
        $var = str_replace(',', '', substr($var, 0, - 3));
        $real = trim(str_replace('.', '', $var));
        
        return $real . $decimal;
    }
    
    public function ValidFloatBr($var) {
        $cifra1 = substr($var, 0, 2); // R$
        $cifra2 = substr($var, 0, 1); // $
        if ($cifra1 == 'R$' || $cifra2 == '$') {
            $var = trim(str_replace($cifra1, '', $var));
            $var = trim(str_replace($cifra2, '', $var));
            return $this->ValidFloat($var);
        } else {
            return false;
        }
    }
    
    public function ValidMoneyBr($var) {
        $tmp = $this->Float2Int($var);
        if ($var == $this->FormatFloatSQL($tmp))    return true;
        else                                        return false;
    }
    
    public function ValidFloatUs($var) {
        $cifra1 = substr($var, 0, 2); // U$
        if ($cifra1 == 'U$') {
            $var = trim(str_replace($cifra1, '', $var));
            return $this->ValidFloat($var);
        } else {
            return false;
        }
    }
    
    public function ValidFloat($var) {
        $sep_decimal = substr($var, - 3, 1);
        $decimal = substr($var, - 2);
        $var = str_replace(',', '', substr($var, 0, - 3));
        $real = trim(str_replace('.', '', $var));
        if ($sep_decimal == ',') {
            $sep_decimal = '.';
        }
        if (! is_numeric($real)) {
            return false;
        }
        if ($sep_decimal != '.') {
            return false;
        }
        if (! is_numeric($decimal)) {
            return false;
        }
        return true;
    }
    
    public function FormatFloat($var) {
        $var = preg_replace("/[^0-9.,\\s]/", "", $var);
        $var = $this->Float2Int($var);
        return substr($var, 0, - 2) . '.' . substr($var, - 2);
    }
    
    public function FormatFloatSQL($var) {
        return number_format(($var / 100), 2, ',', '.');
    }
    
    public function FormatFloatSQLDefault($var) {
        return number_format(($var / 100), 2, '.', '');
    }
    
    public function FormatFloatBR($var) {
        $var = $this->FormatFloat($var);
        return 'R$ ' . number_format($var, 2, ',', '.');
    }
    
    public function FormatFloatSQL2BR($var) {
        return 'R$ ' . number_format(($var / 100), 2, ',', '.');
    }
    
    public function FormatBytes($var) {
        
        $base = log($var) / log(1024);
        $suffix = array("", "KB", "MB", "GB", "TB");
        $f_base = floor($base);
        return round(pow(1024, $base - floor($base)), 1) .' '. $suffix[$f_base];
        
        //return 'R$ ' . number_format(($var / 100), 2, ',', '.');
    }
    
    public function FormatFloatUSA($var) {
        $var = $this->FormatFloat($var);
        return 'U$ ' . number_format($var, 2, '.', ',');
    }
    
    public function FormatFloatSQL2USA($var) {
        return 'U$ ' . number_format(($var / 200), 2, '.', ',');
    }
    
    public function FormatSQL2Vars($var_origem, $type) {
        switch ($type) {
            case 'fone' :
                $var_destino = $this->FormatPhone($var_origem);
                break;
            case 'float_br' :
                $var_destino = $this->FormatFloatSQL2BR($var_origem);
                break;
            case 'float_usa' :
                $var_destino = $this->FormatFloatSQL2USA($var_origem);
                break;
            case 'float' :
                $var_destino = $this->FormatFloatSQL($var_origem);
                break;
            case 'datetime' :
                $var_destino = $this->FormatDtTimeSQL2Br($var_origem);
                break;
            case 'date' :
                $var_destino = $this->FormatDtSQL2Br($var_origem);
                break;
            default :
                $var_destino = htmlentities($var_origem);
                break;
        }
        return $var_destino;
    }
    
    
    public function validaCPF($cpf) {
        
        $cpf = sprintf("%011s", preg_replace("/[^0-9]/", "", $cpf));
        
        if (preg_match("/^([0-9])\1{10}$/", $cpf)) {
            return false;
        }
        
        $dv1 = $dv2 = 0;
        
        for ($i = 0; $i < 9; $i++) {
            $dv1 += $cpf[$i] * (10 - $i);
            $dv2 += $cpf[$i] * (11 - $i);
        }
        
        $dv1 = $dv1 % 11 > 1 ? 11 - ($dv1 % 11) : 0;
        $dv2 = ($dv2 + ($dv1 * 2)) % 11 > 1 ? 11 - (($dv2 + ($dv1 * 2)) % 11) : 0;
        
        return $cpf[9] == $dv1 && $cpf[10] == $dv2;
        
    }
    
    private function limpaCNPJAlfanumerico($cnpj) {

        $cnpj = strtoupper(preg_replace("/[^0-9A-Z]/", "", $cnpj));
        $base = "";
        $dv = "";
        $len = strlen($cnpj);

        for ($i = 0; $i < $len && strlen($base) < 12; $i++) {
            if (preg_match("/^[0-9A-Z]$/", $cnpj[$i])) {
                $base .= $cnpj[$i];
            }
        }

        for (; $i < $len && strlen($dv) < 2; $i++) {
            if (preg_match("/^[0-9]$/", $cnpj[$i])) {
                $dv .= $cnpj[$i];
            }
        }

        return $base . $dv;
    }

    private function valorCNPJMod11($caractere) {
        return ord($caractere) - 48;
    }

    public function formataCNPJ($cnpj) {

        $cnpj = $this->limpaCNPJAlfanumerico($cnpj);

        if ($cnpj == "") {
            return "";
        }

        return substr($cnpj, 0, 2) . "." .
            substr($cnpj, 2, 3) . "." .
            substr($cnpj, 5, 3) . "/" .
            substr($cnpj, 8, 4) . "-" .
            substr($cnpj, 12, 2);
    }

    public function validaCNPJ($cnpj) {

        $cnpj = $this->limpaCNPJAlfanumerico($cnpj);

        if (strlen($cnpj) != 14) {
            return false;
        }

        if (preg_match("/^(.)\1{13}$/", $cnpj)) {
            return false;
        }

        $mult1 = array(5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);
        $mult2 = array(6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);

        $d1 = 0;
        for ($i = 0; $i < 12; $i++) {
            $d1 += $this->valorCNPJMod11($cnpj[$i]) * $mult1[$i];
        }

        $r1 = $d1 % 11;
        $dv1 = $r1 > 1 ? 11 - $r1 : 0;

        if ((int) $cnpj[12] !== $dv1) {
            return false;
        }

        $d2 = 0;
        for ($i = 0; $i < 13; $i++) {
            $d2 += $this->valorCNPJMod11($cnpj[$i]) * $mult2[$i];
        }

        $r2 = $d2 % 11;
        $dv2 = $r2 > 1 ? 11 - $r2 : 0;

        return (int) $cnpj[13] === $dv2;
    }
    
    
    
    /*********************/
    public function ehFeriado($dia, $mes, $ano) {
        
        if ($dia && $mes && $ano) {
            if ($this->ehSextaFeiraSanta($dia, $mes, $ano)) return "Sexta-Feira Santa";
            if ($this->ehCarnaval($dia, $mes, $ano)) return "Carnaval";
            if ($this->ehCorpusChristi($dia, $mes, $ano)) return "Corpus Christi";
            $feriado = $this->buscaFeriadoNacionalFixo($dia, $mes);
            if ($feriado) return $feriado;
            $feriado = $this->buscaFeriadoMunicipalFixo($dia, $mes);
            if ($feriado) return $feriado;
        }
        
        return false;
        
    }
    
    public function ehSextaFeiraSanta($dia, $mes, $ano) {
        
        return date("Y-m-d", strtotime("$ano-$mes-$dia 2 days")) == $this->buscaDataPascoa($ano);
        
    }
    
    public function ehCarnaval($dia, $mes, $ano) {
        
        return date("Y-m-d", strtotime("$ano-$mes-$dia 47 days")) == $this->buscaDataPascoa($ano);
        
    }
    
    public function ehCorpusChristi($dia, $mes, $ano) {
        
        return date("Y-m-d", strtotime("$ano-$mes-$dia 60 days ago")) == $this->buscaDataPascoa($ano);
        
    }
    
    public function buscaDataPascoa($ano) {
        
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
    
    public function buscaFeriadoNacionalFixo($dia, $mes) {
        
        $data = sprintf("%02s-%02s", $mes, $dia);
        
        if (array_key_exists($data, $this->feriados_nacionais)) {
            return $this->feriados_nacionais[$data];
        }
        
    }
    
    public function buscaFeriadoMunicipalFixo($dia, $mes) {
        
        $data = sprintf("%02s-%02s", $mes, $dia);
        
        if (array_key_exists($data, $this->feriados_municipais)) {
            return $this->feriados_municipais[$data];
        }
        
    }
    
    
    
}

?>
