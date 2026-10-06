<?php

require_once("_global/modulo.class.inc.php");

/**
 * Exportacao Cidade DataPrev: campos separados por " | " (pipe com espacos), conforme manual / modelo CSV.
 * Permissoes: rel_gru_aco_tempermissao (cadastro em tab_modulos / tab_acoes).
 */
class cidade_dataprev extends modulo {

    var $ultimo_status = null;

    /** Ordem das colunas (cabecalho = aliases da consulta). */
    var $colunas_exportacao = array(
        "Data_atualizacao", "doc_numero", "end_logradouro", "end_numero", "end_complemento", "end_bairro", "end_municipio", "end_munibge", "end_uf", "end_cep", "geo_latitude", "geo_longitude", "cnpj"
    );

    function __construct($bancodedados, $permissoes, $executar = true) {

        $this->bancodedados = $bancodedados;
        $this->permissoes = $permissoes;

        if ($executar) {
            $this->buscaDadosModulo($_GET["m"]);
            $this->buscaDadosAcao($_GET["a"] ? $_GET["a"] : "gerar");
            $this->conteudo = $this->geraTitulo();
            $this->{$this->acao}();
        }
    }

    function listar() {

        $this->conteudo .= $this->carregaArquivo("cidade_dataprev.listar.inc.php");
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=listar&c=gerar", $this->conteudo);
        if ($_GET["c"] == "gerar") {
            if (!$this->exportar()) {
                $info = "Nao ha registros para exportar.";

                print "<script type=\"text/javascript\">alert('$info'); window.location = './?m={$this->dados_modulo["nome"]}&a=gerar';</script>";
            }
        }
    }

    function gerar() {
       
        $this->conteudo .= $this->carregaArquivo("cidade_dataprev.listar.inc.php");
        $this->conteudo = str_replace("[action]", "?m={$this->dados_modulo["nome"]}&a=gerar&c=gerar", $this->conteudo);
        if ($_GET["c"] == "gerar") {
            if (!$this->exportar()) {
                $info = "Nao ha registros para exportar.";

                print "<script type=\"text/javascript\">alert('$info'); window.location = './?m={$this->dados_modulo["nome"]}&a=gerar';</script>";
            }
        }
    }

    function exportar() {

        $linhas = $this->buscaDadosExportacao();
        if (!$linhas) {
            return false;
        }

        $nomeArquivo = "cidade_dataprev_" . date("Ymd_His") . ".csv";
        $conteudo = $this->montarArquivoPipe($linhas);
        header("Content-Type: text/csv; charset=ISO-8859-1");
        header("Content-Disposition: attachment; filename=" . $nomeArquivo);
        header("Expires: 0");
        header("Cache-Control: must-revalidate");
        header("Pragma: public");
        header("Content-Length: " . strlen($conteudo));
        echo $conteudo;

        exit;
    }

    /**
     * @return array|false lista de linhas (array associativo por coluna) ou false se vazio
     */
    function buscaDadosExportacao() {

        $sql = "
            SELECT 
                    DATE_FORMAT(CURDATE(), '%d/%m/%Y') AS Data_atualizacao,
                    CONCAT('***',SUBSTRING(REPLACE(REPLACE(tab_clientes.cli_cpf_cnpj, '.', ''), '-', ''),4,5),'***') AS doc_numero,
                    IF(tab_logradouros.log_nome, tab_logradouros.log_nome, 'Rua Principal') AS end_logradouro,
                    IF(tab_clientes.cli_numero, tab_clientes.cli_numero, 'S/N') AS end_numero,
                    null AS end_complemento,
                    IF(tab_bairros.bai_nome,tab_bairros.bai_nome, 'Centro') AS end_bairro,
                    tab_cidades.cid_nome AS end_municipio,
                    tab_cidades.cid_ibge AS end_munibge,
                    tab_estados.est_sigla AS end_uf,
                    LPAD(IF(tab_cidades.cid_cep_geral,tab_cidades.cid_cep_geral , tab_clientes.cep_id ), 8, '0') AS end_cep,
                    null AS geo_latitude,
                    null AS geo_longitude,
                    '23911953000107' AS cnpj
                FROM 
                    tab_clientes
                    INNER JOIN tab_contratos ON tab_clientes.cli_id = tab_contratos.cli_id
                    INNER JOIN tab_ceps ON tab_ceps.cep_id = tab_clientes.cep_id
                    INNER JOIN tab_logradouros ON tab_logradouros.log_id = tab_ceps.log_id 
                    INNER JOIN tab_bairros ON tab_bairros.bai_id = tab_logradouros.bai_id
                    INNER JOIN tab_cidades ON tab_cidades.cid_id = tab_bairros.cid_id
                    INNER JOIN tab_estados ON tab_estados.est_id = tab_cidades.est_id

                WHERE 
                    tab_contratos.crs_id IN(3,4,19,20) AND 
                    tab_clientes.clt_id = 1 AND
                    TRIM(tab_clientes.cli_cpf_cnpj) <> '' AND
                    tab_contratos.pls_id IN (1,3)";

        $query = $this->bancodedados->query($sql);

        if (!$this->bancodedados->linhas($query)) {

            return false;
        }

        $out = array();
        while (($row = $this->bancodedados->resultadoArray($query)) != false) {
            $out[] = $row;
        }
        
        return $out;
    }

    /**
     * @param array $linhas
     * @return string conteudo ISO-8859-1 com CRLF
     */
    function montarArquivoPipe($linhas) {
        /* Manual: separador pipe com espacos */
        $sep = "|";
        $buf = array();
        $cab = array();

        foreach ($this->colunas_exportacao as $col) {
            $cab[] = $this->formatarCampoPipe($col);
        }

        $buf[] = implode($sep, $cab);

        foreach ($linhas as $row) {
            $campos = array();
            foreach ($this->colunas_exportacao as $col) {
                $v = isset($row[$col]) ? $row[$col] : "";
                $campos[] = $this->formatarCampoPipe($v);
            }

            $buf[] = implode($sep, $campos);
        }

        $texto = implode("\r\n", $buf) . "\r\n";
        if (function_exists("mb_convert_encoding") && mb_detect_encoding($texto, "UTF-8", true)) {
            $texto = mb_convert_encoding($texto, "ISO-8859-1", "UTF-8");
        }

        return $texto;
    }

    /**
     * Evita quebra de layout: remove pipe e quebras de linha dos valores.
     */
    function formatarCampoPipe($valor) {

        $s = (string) $valor;
        $s = str_replace(array("\r", "\n", "|"), " ", $s);

        return trim($s);
    }
}
?>