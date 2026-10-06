<?php

require_once("_global/modulo.class.inc.php");

class sicredi extends modulo {

    var $bancodedados;

    function __construct($bancodedados) {

        $this->bancodedados = $bancodedados;
    }

    function sicredi($dados, $teste = false) {

        $boleto = true;
        //$boleto = $this->buscaDadosBoleto($dados);
        if ($boleto) {
            $newDados = $this->remontarDados($dados);

            try {
                $retorno = $this->darBaixaCobranca($newDados);

                return $retorno;
            } catch (Exception $ex) {
                return false;
            }
        } else {
            return false;
        }
    }

    function buscaDadosCobranca($dados) {

        $nossoNumero = substr($dados['nossoNumero'], 0, 8);
        $q = "SELECT * FROM tab_cobrancas WHERE coa_nosso_numero= '{$nossoNumero}' AND cof_id IN (SELECT cof_id FROM tab_cobrancas_formatos WHERE cof_cedente = '{$dados['beneficiario']}')";
        $cobranca = $this->bancodedados->resultadoArray($this->bancodedados->query($q));

        return $cobranca['coa_id'];
    }

    /**
     * 
     * @param type $dados
     * @return type
     */
    function buscaDadosBoleto($dados) {

        $boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);

        return $boletoSicredi->buscaBoletoPeloNossoNumero($dados['nossoNumero']);
    }

    /**
     * 
     * @param type $dados
     * @return type
     */
    function darBaixaCobranca($dados) {

        $boletoSicredi = controle::instanciaObjeto("cobrancas.boleto_sicredi", $this->bancodedados);

        return $boletoSicredi->liquidarPorMovimentacao($dados);
    }

    function remontarDados($dados, $boleto = null) {

        $boleto['dataEvento'] = date('Y-m-d');
        $seuNumero = $this->buscaDadosCobranca($dados);
         
        $boletoDataEvento = $boleto['dataEvento'];
        $registro["data_processamento"] = $boletoDataEvento;
        $registro["data_credito"] = $boletoDataEvento;
        $registro["valor_juro"] = $dados['valorJuros'] * 100;
        $registro["valor_desconto"] = $dados['valorDesconto'] * 100;
        $registro["valor_acrescimo"] = $dados['valorMulta'] * 100;
        $registro["valor_decrescimo"] = $dados['valorAbatimento'] * 100;
        $registro["valor_total"] = $dados['valorLiquidacao'] * 100;
        $registro["valor_iof"] = 0;
        $registro["id"] = (int) $seuNumero;
        $registro["seuNumero"] = (int) $seuNumero;
        $registro["nossoNumero"] = $dados['nossoNumero'];
        $registro["tipoLiquidacao"] = $dados['movimento'];
        $registro["valor_original"] = $this->calculaValorOriginal($dados) * 100;

        return $registro;
    }

    function calculaValorOriginal($dados) {

        $valor = $dados['valorLiquidacao'] + ($dados['valorDesconto'] + $dados['valorAbatimento']) - ($dados['valorJuros'] + $dados['valorMulta']);

        return $valor;
    }
}
