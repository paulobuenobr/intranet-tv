<?

// Limite máximo de execução 900s = 15min
set_time_limit(900);

class cron_atualiza_servidores {

  var $bancodedados;
  var $servidores;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
    $this->servidores = controle::instanciaObjeto("servidores", $this->bancodedados, null, false);
  }
  
  function publicaTodos() {
    $query = "
      UPDATE
        tab_clientes_pontos
      SET
        clp_publicado = 0
      WHERE
        cps_id = 1";
    $this->bancodedados->query($query);
  }
  
  ##########################
  ### Ajusta Velocidades ###
  ##########################
  
  function ajustaVelocidades() {
    // Define Ajuste
    $ajuste[150] = intval(150 * 1.10);
    $ajuste[200] = intval(200 * 1.10);
    $ajuste[300] = intval(300 * 1.10);
    $ajuste[400] = intval(400 * 1.10);
    $ajuste[600] = intval(600 * 1.10);
    $ajuste[800] = intval(800 * 1.10);    
    $ajuste[1024] = intval(1024 * 1.10);
    $ajuste[1536] = intval(1536 * 1.10);
    $ajuste[2048] = intval(2048 * 1.10);
    $ajuste[2560] = intval(2560 * 1.10);
    $ajuste[3072] = intval(3072 * 1.10);
    $ajuste[4096] = intval(4096 * 1.10);
    $ajuste[5070] = intval(5070 * 1.10);
    $ajuste[6144] = intval(6144 * 1.10);
    $ajuste[8192] = intval(8192 * 1.10);
    $ajuste[10240] = intval(10240 * 1.10);
    $ajuste[20480] = intval(20480 * 1.10);
    // Aplica Ajuste
    $servidores = $this->buscaServidoresAjustaveis(0);
    if ($servidores) {
      foreach ($servidores as $servidor_id) {
        $this->servidores->ajustaVelocidade($servidor_id, $ajuste);
      }
    }
  }
  
  ############################
  ### Restaura Velocidades ###
  ############################
  
  function restauraVelocidades() {
  	$servidores = $this->buscaServidoresAjustaveis(1);
    if ($servidores) {
      foreach ($servidores as $servidor_id) {
        $this->servidores->ajustaVelocidade($servidor_id);
      }
    }
  }
  
  ###########################
  ### Atualiza Servidores ###
  ###########################

  function atualizaServidores() {
    $query = "
      UPDATE
        tab_servidores
      SET
        ser_publicando = 0
      WHERE
        ser_publicando = 1 AND
        DATE_ADD(ser_data_hora_publicacao, INTERVAL 60 MINUTE) <= NOW()";
    $this->bancodedados->query($query);
    $servidores = $this->buscaServidores();
    if ($servidores) {
      foreach ($servidores as $servidor_id) {
        $this->servidores->publicarServicos($servidor_id);
      }
    }
    //if (intval(date("Hi")) >= 100 && intval(date("Hi")) <= 1200) {
    //  $this->restauraVelocidades();
    //}
  }

  ########################
  ### Funções de Busca ###
  ########################
  
  function buscaServidoresAjustaveis($status) {
    $query = "
      SELECT DISTINCT
        tab_servidores.ser_id AS servidor_id
      FROM
        tab_servidores
        INNER JOIN tab_servidores_interfaces ON (tab_servidores.ser_id = tab_servidores_interfaces.ser_id)
        INNER JOIN tab_servidores_servicos ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
      WHERE
        tab_servidores_servicos.sst_id IN (9, 10, 12) AND
        tab_servidores_interfaces.sia_id = 1 AND
        -- tab_servidores.ser_ajustado = $status AND
        tab_servidores.set_id = 1";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $servidores[] = $dados["servidor_id"];
    }
    return $servidores;
  }

  function buscaServidores() {
    $query = "
      SELECT DISTINCT
        tab_servidores.ser_id AS servidor_id,
        tab_servidores.ser_nome AS servidor_nome,
        tab_servidores.ser_data_hora_publicacao_inicio AS publicacao_inicio,
        tab_servidores.ser_data_hora_publicacao_termino AS publicacao_termino
      FROM
        tab_servidores
        INNER JOIN tab_servidores_interfaces ON (tab_servidores.ser_id = tab_servidores_interfaces.ser_id)
        INNER JOIN tab_servidores_servicos ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
        INNER JOIN tab_servidores_servicos_tipos ON (tab_servidores_servicos.sst_id = tab_servidores_servicos_tipos.sst_id)
        INNER JOIN tab_clientes_pontos ON (tab_servidores_interfaces.sei_id = tab_clientes_pontos.sei_id)
      WHERE
		tab_servidores.ser_id NOT IN (610) AND
        tab_servidores_interfaces.sia_id = 1 AND
        tab_servidores.ser_publicando = 0 AND
        tab_servidores.set_id = 1 AND
        tab_servidores_servicos.sss_id = 1 AND
        tab_servidores_servicos_tipos.sst_servico IS NOT NULL
      GROUP BY
        tab_servidores.ser_id
      HAVING
        SUM(IF(tab_clientes_pontos.clp_publicado = 1, 0, 1)) > 0
        
      UNION
      
      SELECT DISTINCT
        tab_servidores.ser_id AS servidor_id,
        tab_servidores.ser_nome AS servidor_nome,
        tab_servidores.ser_data_hora_publicacao_inicio AS publicacao_inicio,
        tab_servidores.ser_data_hora_publicacao_termino AS publicacao_termino
      FROM
        tab_servidores
        INNER JOIN tab_servidores_interfaces ON (tab_servidores.ser_id = tab_servidores_interfaces.ser_id)
        INNER JOIN tab_servidores_servicos ON (tab_servidores_interfaces.sei_id = tab_servidores_servicos.sei_id)
        INNER JOIN tab_servidores_servicos_tipos ON (tab_servidores_servicos.sst_id = tab_servidores_servicos_tipos.sst_id)
      WHERE
 		tab_servidores.ser_id NOT IN (610) AND
        tab_servidores.ser_publicado = 0 AND
        tab_servidores.set_id = 1 AND
        tab_servidores_servicos.sss_id = 1 AND
        tab_servidores_servicos_tipos.sst_servico IS NOT NULL
        
      ORDER BY
        RAND()
      LIMIT
        0, 5";
    $query = $this->bancodedados->query($query);
    while (($dados = $this->bancodedados->resultadoArray($query)) != false) {
      $servidores[] = $dados["servidor_id"];
    }
    return $servidores;
  }

}

?>
