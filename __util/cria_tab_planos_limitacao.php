<?php

// Exemplo:
// http://localhost/intranet/intranet-tv/__util/cria_tab_planos_limitacao.php

// https://oletv.oletelecom.com.br/__util/cria_tab_planos_limitacao.php

require_once("../global.inc.php");

global $bancodedados;

$util = new util($bancodedados);
$util->executar();

class util {

    /**
     * @var bancodedados
     */
    private $bancodedados;

    function __construct($bancodedados) {
        $this->bancodedados = $bancodedados;
    }

    function executar() {
        header("Content-Type: application/json; charset=utf-8");

        $this->criarTabelaPlanoLimitacao();
        $this->alterarTabelaPlanosSeNecessario();

        echo json_encode(array(
            "sucesso" => true,
            "mensagem" => "Estrutura de tab_planos_limitacao/tab_planos validada com sucesso."
        ));
    }

    function criarTabelaPlanoLimitacao() {
        $this->bancodedados->query("
            CREATE TABLE IF NOT EXISTS tab_planos_limitacao (
              id INT NOT NULL AUTO_INCREMENT,
              pla_id SMALLINT(5) UNSIGNED NULL,
              valor INT(10) NULL DEFAULT 0,
              PRIMARY KEY (id),
              KEY tab_planos_limitacao_pla_id_idx (pla_id),
              CONSTRAINT tab_planos_limitacao_pla_id
                FOREIGN KEY (pla_id)
                REFERENCES tab_planos (pla_id)
                ON DELETE NO ACTION
                ON UPDATE NO ACTION
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1");

        $this->bancodedados->query("ALTER TABLE tab_planos_limitacao ADD COLUMN plano SMALLINT(5) NULL AFTER valor");
            
    }

    function alterarTabelaPlanosSeNecessario() {
        $alteracoes = array();

        if (!$this->colunaExiste("tab_planos", "pla_tipo_limitacao")) {
            $alteracoes[] = "ADD COLUMN pla_tipo_limitacao INT(1) NULL DEFAULT 1 AFTER issRetido";
        }

        if (!$this->colunaExiste("tab_planos", "pla_valor_max")) {
            $alteracoes[] = "ADD COLUMN pla_valor_max INT(8) NULL DEFAULT 0 AFTER pla_tipo_limitacao";
        }

        if (!$this->plaIdEstaNoFormatoEsperado()) {
            $alteracoes[] = "CHANGE COLUMN pla_id pla_id SMALLINT(5) UNSIGNED NOT NULL";
        }

        if ($alteracoes) {
            $this->bancodedados->query("
                ALTER TABLE tab_planos
                " . implode(",\n", $alteracoes));
        }
    }

    function colunaExiste($tabela, $coluna) {
        $tabela = addslashes($tabela);
        $coluna = addslashes($coluna);

        $query = $this->bancodedados->query("
            SELECT
                COUNT(*)
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = DATABASE() AND
                TABLE_NAME = '{$tabela}' AND
                COLUMN_NAME = '{$coluna}'");

        return intval($this->bancodedados->resultado($query)) > 0;
    }

    function plaIdEstaNoFormatoEsperado() {
        $query = $this->bancodedados->query("
            SELECT
                COLUMN_TYPE AS column_type,
                IS_NULLABLE AS is_nullable
            FROM
                INFORMATION_SCHEMA.COLUMNS
            WHERE
                TABLE_SCHEMA = DATABASE() AND
                TABLE_NAME = 'tab_planos' AND
                COLUMN_NAME = 'pla_id'
            LIMIT 1");
        $dados = $this->bancodedados->resultadoArray($query);

        if (!$dados) {
            return false;
        }

        return strtolower($dados["column_type"]) == "smallint(5) unsigned" && strtoupper($dados["is_nullable"]) == "NO";
    }
}

?>
