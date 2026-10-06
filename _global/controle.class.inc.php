<?

require_once "servidor.class.inc.php";

class controle {
    
    static function instanciaObjeto() {
        
        $argumentos = func_get_args();
        
        if (sizeof($argumentos) > 0) {
            $classe = $argumentos[0];
            if (!class_exists($classe)) {
                $dados = controle::buscaDadosClasse($argumentos[0]);
                include_once($dados["arquivo"]);
                $classe = $dados["classe"];
            }
            if (class_exists($classe)) {
                $eval_argumentos = array();
                for ($i = 1; $i < sizeof($argumentos); $i++) {
                    $eval_argumentos[] .= "\$argumentos[$i]";
                }
                $instancia = eval("return new {$classe}(".implode(", ", $eval_argumentos).");");
                return $instancia;
            } else {
                if (!strpos(__FILE__, "rds.js.php")) {
                    print "<p><b>Erro no carregamento da classe \"{$argumentos[0]}\" - {$dados["arquivo"]}!</b></p>";
                } else {
                    print "alert('Erro no carregamento da classe \"{$argumentos[0]}\"!');";
                }
                return false;
            }
        } else {
            return false;
        }
    
    }
    
    static function buscaDadosClasse($classe) {
        
        $posicao_ponto = strpos($classe, ".");
        
        if ($posicao_ponto) {
            $modulo = substr($classe, 0, $posicao_ponto);
            $nome = $classe;
            $classe = str_replace(".", "_", $classe);
        } else {
            $modulo = $nome = $classe;
        }
        
        if (file_exists(dirname(__FILE__)."/$nome.class.inc.php")) {
            $arquivo = dirname(__FILE__)."/$nome.class.inc.php";
        } elseif (file_exists(DIR_GLOBAL."$nome.class.inc.php")) {
            $arquivo = DIR_GLOBAL."$nome.class.inc.php";
        } elseif (file_exists(DIR_MODULOS."$modulo/$nome.class.inc.php")) {
            $arquivo = DIR_MODULOS."$modulo/$nome.class.inc.php";
        }
        
        return $arquivo ? array("classe" => $classe, "arquivo" => $arquivo) : false;
    
    }

}

?>
