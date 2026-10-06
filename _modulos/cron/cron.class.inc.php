<?

class cron {

  var $bancodedados;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
  }

  function executa($forcar_crons = "") {
    // Limpa Crons
    $this->limpaCrons();
    // Busca Crons
    $crons = ($forcar_crons) ? explode (",", $forcar_crons) : $this->buscaCrons();
    for ($i = 0; $i < sizeof($crons); $i++) {
      $dados_cron = $this->buscaDadosCron($crons[$i]);
      if ($dados_cron["executavel"] || $forcar_crons) {
        $dados_cron["log"] = $this->inicializaExecucao($dados_cron);
        $cron_instancia = controle::instanciaObjeto("cron.{$dados_cron["classe"]}", $this->bancodedados);
        if (method_exists($cron_instancia, $dados_cron["acao"])) {
          $cron_instancia->{$dados_cron["acao"]}();
        }
        $this->finalizaExecucao($dados_cron);
      }
    }
  }

  function limpaCrons() {
    $query = "
    	UPDATE
    		tab_cron
    	SET
    		cro_em_execucao = 0
    	WHERE
    		cro_em_execucao = 1 AND
    		(cro_ultima_execucao_real < DATE_SUB(NOW(), INTERVAL 4 HOUR) OR cro_ultima_execucao_real IS NULL)";
    $this->bancodedados->query($query);
    $query = "
    	UPDATE
    		tab_cron
    	SET
    		cro_em_execucao = 0 
    	WHERE
    		cro_id = 3 AND
    		cro_em_execucao = 1 AND
    		(cro_ultima_execucao_real < DATE_SUB(NOW(), INTERVAL 10 MINUTE) OR cro_ultima_execucao_real IS NULL)";
    $this->bancodedados->query($query);
  }

  function buscaCrons() {
    $query = "
      SELECT
        cro_id AS id
      FROM
        tab_cron
      ORDER BY
        cro_ultima_execucao_real DESC";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $crons[] = $dados["id"];
    }
    return $crons;
  }

  function buscaDadosCron($id) {
    $query = "
      SELECT
        cro_id AS id,
        cro_classe AS classe,
        cro_acao AS acao,
        cro_intervalo_minutos AS intervalo_minutos,
        cro_tempo_real AS tempo_real,
        IF((cro_ativo = 1) AND
           (cro_em_execucao != 1) AND
           (cro_dias_semana IS NULL OR LOCATE(DATE_FORMAT(NOW(), '%w'), cro_dias_semana) > 0) AND
           (cro_dias_mes IS NULL OR LOCATE(DATE_FORMAT(NOW(), '%d'), cro_dias_mes) > 0) AND
           (cro_intervalo_minutos IS NULL OR cro_ultima_execucao_inicio IS NULL OR DATE_SUB(NOW(), INTERVAL cro_intervalo_minutos MINUTE) > cro_ultima_execucao_inicio), 1, 0) AS executavel
      FROM
        tab_cron
      WHERE
        cro_id = $id";
    $query = $this->bancodedados->query($query);
    return $this->bancodedados->resultadoArray($query);
  }

  function inicializaExecucao($dados_cron) {
  	// Cron
    $query = "UPDATE tab_cron SET cro_em_execucao = 1, cro_ultima_execucao_real = NOW(), cro_ultima_execucao_inicio = IF(cro_tempo_real = 's', DATE_ADD(cro_ultima_execucao_inicio, INTERVAL cro_intervalo_minutos MINUTE), NOW()) WHERE cro_id = {$dados_cron["id"]}";
    $this->bancodedados->query($query);
    // Log
    $query = "INSERT INTO tab_cron_log (crl_classe, crl_acao, crl_inicio) VALUES ('{$dados_cron["classe"]}', '{$dados_cron["acao"]}', NOW())";
    $this->bancodedados->query($query);
    return $this->bancodedados->ultimoIdInserido();
  }

  function finalizaExecucao($dados_cron) {
    // Cron
    $query = "UPDATE tab_cron SET cro_em_execucao = 0, cro_ultima_execucao_termino = NOW() WHERE cro_id = {$dados_cron["id"]}";
    $this->bancodedados->query($query);
  	// Log
    $query = "UPDATE tab_cron_log SET crl_termino = NOW() WHERE crl_id = {$dados_cron["log"]}";
    $this->bancodedados->query($query);
  }

}

?>
