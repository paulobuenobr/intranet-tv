<?php

class cron_nfse {

    var $bancodedados;
    var $nfse;
    var $local;

    function __construct($bancodedados) {

        $this->bancodedados = $bancodedados;
        $this->nfse = controle::instanciaObjeto("nfse", $bancodedados, null, false);

        $cobrancas = controle::instanciaObjeto("cobrancas", $this->bancodedados, null, false);
        $local = $cobrancas->buscaLocalDaNota();

        $this->local = $local['sigla'];
    }

    function consultarLoteRpsCtb() {

        if ($this->local == "ctb") {
            $this->nfse->consultarLoteRpsCtb();
        }
    }

    function recepcionarLoteRpsNaoGeradasCtb() {

        if ($this->local == "ctb") {
            $this->nfse->recepcionarLoteRpsNaoGeradasCtb();
        }
    }

    function recepcionarLoteRpsCtb() {

        if ($this->local == "ctb") {
            $this->nfse->recepcionarLoteRpsCtb();
        }
    }

    function emiteNotaSJSC() {

        if ($this->local == "sjsc") {
            $this->nfse->emiteNotaSJSC();
        }
    }

    function emiteNotasNaoGeradasSJSC() {

        if ($this->local == "sjsc") {
            $this->nfse->emiteNotasNaoGeradasSJSC();
        }
    }

    function gerarNaoCanceladosSJSC() {

        if ($this->local == "sjsc") {
            $this->nfse->gerarNaoCanceladosSJSC();
        }
    }
}
