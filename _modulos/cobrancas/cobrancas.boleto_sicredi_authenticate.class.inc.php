<?php

require_once __DIR__ . '/../vendor/autoload.php';

class cobrancas_boleto_sicredi_authenticate {

    var $bancodedados;
    var $homologacao;
    var $dotenv;

    /**
     * @param type $bd
     * @param type $config
     * @param type $homologacao
     */
    function __construct($bd, $config, $homologacao = false) {
        $this->bancodedados = $bd;
        $this->homologacao = $homologacao;

        $this->dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $this->dotenv->load();

        $client = new \SicrediAPI\Client($config->SICREDI_API_KEY, new \GuzzleHttp\Client(), true);
        if ($client->authenticate($config->SICREDI_USERNAME, $config->SICREDI_PASSWORD)) {
            echo "Authenticated successfully\n";
        } else {
            echo "Authentication failed\n";
        };
    }
}
