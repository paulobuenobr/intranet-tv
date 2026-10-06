<?php

class cron_limpa_historicoapi {

    var $bancodedados;

    function __construct($bancodedados) {
        $this->bancodedados = $bancodedados;
    }

    function limpaHistoricoAPI() {

        $query = "DELETE FROM tab_clientes_origens_historicoapi WHERE requestDate <= DATE_SUB(NOW(), INTERVAL 15 DAY)";
        $this->bancodedados->query($query);
    }

}