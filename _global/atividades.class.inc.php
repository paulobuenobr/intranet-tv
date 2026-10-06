<?

class atividades {

var $bancodedados;

    function __construct($bancodedados) {
        
        $this->bancodedados = $bancodedados;
        
    }
    
    function registraAtividade($usuario_id, $modulo_id, $acao_nome, $parametro) {
        
        $parametro = preg_replace("/[^0-9]/", "", $parametro);
        
        if ($usuario_id && $modulo_id && $acao_nome && substr($acao_nome, 0, 3) != "ver" && $parametro
			&& $parametro < 16777215) {
            if (($acao_id = $this->buscaIdAcao($modulo_id, $acao_nome)) != false) {
                $this->bancodedados->query("
                    INSERT INTO
                        tab_atividades
                    SET 
                        usu_id = $usuario_id, 
                        aco_id = $acao_id, 
                        ati_datahora = NOW(), 
                        ati_ip = '{$_SERVER["REMOTE_ADDR"]}', 
                        ati_parametro = $parametro");
            }
        }
        
    }
    
    function buscaIdAcao($modulo_id, $acao_nome) {
        
        $query = $this->bancodedados->query("
            SELECT
                aco_id
            FROM
                tab_acoes
            WHERE
                mod_id = $modulo_id AND
                aco_nome = '$acao_nome'");
        
        return $this->bancodedados->resultado($query);
        
    }

}

?>
