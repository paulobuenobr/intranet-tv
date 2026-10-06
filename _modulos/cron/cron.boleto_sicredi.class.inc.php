<?

class cron_boleto_sicredi {

    var $bancodedados;

    function cron_boleto_sicredi($bancodedados) {

        $this->bancodedados = $bancodedados;
    }

    function gerarBoletosSicredi() {

        $boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);
                
        $q = "
            SELECT 
                c.cof_id, c.coa_id , c.cot_id, c.coa_aguardando_registro 
            FROM 
                tab_cobrancas c
            LEFT JOIN tab_cobrancas_lotes l ON l.col_id = c.col_id 
            WHERE 
                c.cof_id IN (SELECT cof_id FROM tab_cobrancas_formatos WHERE cof_parametros LIKE '%sicredi%') AND 
                (l.cla_id = 4 OR c.col_id IS NULL) AND c.coa_aguardando_registro = 1 AND c.cot_id = 1";
        $query = $this->bancodedados->query($q);

        while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            if ($dados['coa_aguardando_registro']) {
                $atualzia = $boletoSicredi->criarBoleto($dados['cof_id'], $dados['coa_id']);
            }
        }
    }

    function cancelarBoletos() {
        $boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);

        $sql = "
            SELECT 
                cof_id, coa_id , cot_id, coa_aguardando_registro 
            FROM 
                tab_cobrancas 
            WHERE 
                cof_id IN (SELECT cof_id FROM tab_cobrancas_formatos WHERE cof_parametros LIKE '%sicredi%') AND
                coa_aguardando_registro = 1 AND cot_id NOT IN (1 , 2 , 3)";
        $query = $this->bancodedados->query($sql);
        
         while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
            $cancelar = $boletoSicredi->cancelarBoleto($dados['coa_id']);
        }
    }

    function pesquisaNaoLiquidados() {

        $boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);
        $boletoSicredi->pesquisaNaoLiquidados();
    }

    function liquidadosPorDia() {

        $boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);
        $boletoSicredi->liquidadosPorDia();
    }

    function liquidadosHoje() {

        $boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);
        $boletoSicredi->liquidadosHoje();
    }
}

?>