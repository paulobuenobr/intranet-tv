<?php


class equipamentosclass extends loadclass {
    
    public $returnClass = array();
    
    function __construct() {
        
        $this->conectaDB();
        
        
        $modelos = array();
        $queryModelos = $this->db->query("SELECT * FROM tab_equipamentos_modelos WHERE equ_id=".DECODER_PADRAO." and eqm_disponivel='1'");
        $kM = 0;
        while (($dadosModelo = $this->db->resultadoArray($queryModelos)) != false) {
            $modelos[$kM]['id'] = $dadosModelo['eqm_id'];
            $modelos[$kM]['nome'] = $this->textCharsetOutput( $dadosModelo['eqm_nome'] );
            $kM++;
        }
        $list = $modelos;
        /*
        
        $query = $this->db->query("SELECT * FROM tab_equipamentos");
        
        $list = array();
        $k = 0;
        while (($dados = $this->db->resultadoArray($query)) != false) {
            $list[$k]['id'] = $dados['equ_id'];
            $list[$k]['nome'] = $this->textCharsetOutput( $dados['equ_nome'] );
            
            $modelos = array();
            $queryModelos = $this->db->query("SELECT * FROM tab_equipamentos_modelos WHERE equ_id='".$dados['equ_id']."' and eqm_disponivel='1'");
            $kM = 0;
            while (($dadosModelo = $this->db->resultadoArray($queryModelos)) != false) {
                $modelos[$kM]['id'] = $dadosModelo['eqm_id'];
                $modelos[$kM]['nome'] = $this->textCharsetOutput( $dadosModelo['eqm_nome'] );
                $kM++;
            }
            $list[$k]['modelos'] = $modelos;
            unset($modelos);
            $k++;
        }
        */
        if ( count($list) > 0 ) {
            $this->returnClass['retorno_status'] = true;
            $this->returnClass['modelos'] = $list;
        }
        else {
            $this->returnClass['retorno_status'] = false;
            $this->returnClass['error'] = 'Nenhum equipamento foi encontrado!';
        }
        
        return $this->returnClass;
    }
    
    function __destruct() {
        
    }
    
    
}
