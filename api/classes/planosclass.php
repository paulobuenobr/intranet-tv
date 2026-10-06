<?php

class planosclass extends loadclass {
    
    public $returnClass = array();
    
    function __construct($idParceiro='') {
        
        $this->idParceiro = $idParceiro;
        
        $this->conectaDB();
        
        $query = $this->db->query("SELECT SQL_CALC_FOUND_ROWS
				tab_planos.pla_id AS id,
				tab_planos.pla_nome AS nome,
                tab_planos.pla_parametros AS parametros,
				tab_planos.pla_instalacao AS instalacao,
				tab_planos.pla_mensalidade AS mensalidade,
				tab_planos_servicos.pls_nome AS servico,
				tab_planos_tipos.plt_nome AS tipo,
				tab_planos_status.plu_nome AS status,
				tab_planos_sistemas.pli_id AS sistema_id,
				IF(tab_planos.pli_id IS NOT NULL, tab_planos_sistemas.pli_nome, 'Qualquer um') AS sistema_nome,
				IFNULL((SELECT SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_planos.pla_mensalidade)) FROM tab_descontos_concessoes WHERE tab_descontos_concessoes.pla_id = tab_planos.pla_id AND tab_descontos_concessoes.dcs_id = 5 AND tab_descontos_concessoes.det_id = 4), 0) AS bonificacao,
				IFNULL((SELECT SUM(IF(tab_descontos_concessoes.dec_mensalidade > 1, tab_descontos_concessoes.dec_mensalidade, tab_descontos_concessoes.dec_mensalidade * tab_planos.pla_mensalidade)) FROM tab_descontos_concessoes WHERE tab_descontos_concessoes.pla_id = tab_planos.pla_id AND tab_descontos_concessoes.dcs_id = 5 AND tab_descontos_concessoes.det_id != 4), 0) AS descontos
			FROM
				tab_planos
				INNER JOIN tab_planos_servicos ON (tab_planos.pls_id = tab_planos_servicos.pls_id)
				INNER JOIN tab_planos_tipos ON (tab_planos.plt_id = tab_planos_tipos.plt_id)
				INNER JOIN tab_planos_status ON (tab_planos.plu_id = tab_planos_status.plu_id)
				LEFT OUTER JOIN tab_planos_sistemas ON (tab_planos.pli_id = tab_planos_sistemas.pli_id)
            WHERE 
                tab_planos.plu_id=1
			ORDER BY
				tab_planos_servicos.pls_nome,
				tab_planos.plt_id,
				tab_planos.pla_nome
            ");
        
        $list = array();
        $k = 0;
        
        require_once '_modulos/assinaturas/assinaturas.class.inc.php';
        $intranetAssinaturas = new assinaturas($this->db, null, false);
        
        while (($dados = $this->db->resultadoArray($query)) != false) {
            
            $parametros = unserialize($dados['parametros']);
            
            if ($intranetAssinaturas->possuiLimiteDisponivel($dados["id"], $this->idParceiro) < 1) {
            	continue;
            }
           
            if (  (count($parametros['origens']) == 0 || in_array($this->idParceiro.'', $parametros['origens']) !== false) ) {
            
                $list[$k]['id'] = $dados['id'];
                $list[$k]['tipo'] = $dados['tipo'];
                $list[$k]['nome'] = $this->textCharsetOutput($dados['nome']);
                $list[$k]['tipo_aplicacao'] = (intval($parametros['iptv_stb_quantidade'])>=1?'Box':($dados['tipo']!='Principal'?'':'Mobile'));// $this->textCharsetOutput($dados['nome']);
                $list[$k]['taxa_instalacao'] = number_format( ($dados['instalacao']/100),2);
                $list[$k]['mensalidade']['normal'] = number_format( ($dados['mensalidade']/100),2);
                $list[$k]['mensalidade']['ate_vencimento'] = ($dados['bonificacao']==0?'': number_format( ( ($dados['mensalidade']-$dados['bonificacao'] )/100),2) );
                $list[$k]['mensalidade']['promocao'] = ($dados['bonificacao']==0?'': number_format( ( ($dados['mensalidade']-$dados['bonificacao']-$dados['descontos'] )/100),2) );
                $k++;
            
            }
        }
        
        if ( count($list) > 0 ) {
            $this->returnClass['retorno_status'] = true;
            $this->returnClass['planos'] = $list;
        }
        else {
            $this->returnClass['retorno_status'] = false;
            $this->returnClass['error'] = 'Nenhum plano foi encontrado!';
        }
    }
    
    function __destruct() {
        
    }
    
    
}
