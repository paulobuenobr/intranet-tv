<?php

header('Content-Type: text/html; charset=UTF-8');
require_once("_global/modulo.class.inc.php");

class ibge extends modulo {

    var $bancodedados;

    function __construct($bancodedados) {
        $this->bancodedados = $bancodedados;

        if ($_GET["a"] == "atualizarEstados") {
            $this->atualizarEstados();
        } else if ($_GET["a"] == "atualizarCidades") {
            $this->atualizarCidades();
        } else if ($_GET["a"] == "atualizarEstadosECidades") {
            $this->atualizarEstados();
            $this->atualizarCidades();
        }
    }

    function atualizarEstados() {

        $file = __DIR__ . "/ibge/estados.json";
        $json = file_get_contents($file);
        $estados = json_decode($json, true);

        $resultadoQuery = $this->bancodedados->conexao->query("SELECT COUNT(est_ibge) FROM tab_estados");
        if ($resultadoQuery == false) {
            $resultadoQuery = $this->bancodedados->conexao->query("ALTER TABLE tab_estados ADD COLUMN est_ibge INT(10) NULL DEFAULT 0 AFTER est_nome;");
        }

        foreach ($estados as $uf) {
            $this->bancodedados->query("UPDATE tab_estados SET est_ibge = {$uf['id']} WHERE est_sigla = '{$uf['sigla']}'");
        }
    }

    function atualizarCidades() {

        $estados = $this->buscaEstados();
        $resultadoQuery = $this->bancodedados->conexao->query("SELECT COUNT(est_ibge) FROM tab_cidades");
        if ($resultadoQuery == false) {
            $resultadoQuery = $this->bancodedados->conexao->query("ALTER TABLE tab_cidades ADD COLUMN cid_ibge INT(10) NULL DEFAULT 0 AFTER cid_nome;");
        }

        foreach ($estados as $uf) {

            $file = __DIR__ . "/ibge/" . $uf['sigla'] . ".json";
            $json = file_get_contents($file);
            $cidades = json_decode($json, true);
            $this->inserindoCodigoIBGE($uf['id'], $cidades);
        }
    }

    function inserindoCodigoIBGE($uf_id, $cidades) {

        foreach ($cidades as $cidade) {

            $nomeCidade = str_replace("'", "\'", $cidade['nome']);
            $query = "UPDATE tab_cidades SET cid_ibge = {$cidade['id']} WHERE est_id = {$uf_id} AND (
                    cid_nome LIKE '" . str_replace("-", " ", $nomeCidade) . "' OR cid_nome LIKE '" . str_replace("\'", "`", $nomeCidade) . "' OR
                    cid_nome LIKE '{$nomeCidade}' OR cid_nome LIKE '" . utf8_decode(str_replace("\'", "`", $nomeCidade)) . "' OR
                    cid_nome LIKE '" . utf8_decode(str_replace("-", " ", $nomeCidade)) . "' OR cid_nome LIKE '" . utf8_decode($nomeCidade) . "')";
            $this->bancodedados->conexao->query($query);
        }
    }

    function buscaEstados() {

        $query = $this->bancodedados->query("SELECT est_id AS id, est_sigla AS sigla, est_nome AS nome, est_ibge AS ibge FROM tab_estados");

        return $this->bancodedados->resultadoArrayTotal($query);
    }
}
