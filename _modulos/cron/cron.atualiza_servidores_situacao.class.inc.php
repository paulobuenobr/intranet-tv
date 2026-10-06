<?

class cron_atualiza_servidores_situacao {

  var $bancodedados;

  function __construct($bancodedados) {
    $this->bancodedados = $bancodedados;
  }

  function atualizaServidoresSituacao() {
    $dados_nagios = $this->buscaDadosNagios();
    foreach ($dados_nagios as $servidor => $servicos) {
      if ($servicos["PING"]["status"] == "CRITICAL") {
        $dados["PING"][] = "'$servidor'";
      } elseif ($servicos["SSH"]["status"] == "CRITICAL") {
        $dados["SSH"][] = "'$servidor'";
      }
    }
    if ($dados["PING"]) {
      $dados["PING"] = @implode(", ", $dados["PING"]);
      $query = "UPDATE tab_servidores SET ser_data_hora_situacao = IF(seu_id IN (2, 3), ser_data_hora_situacao, NOW()), seu_id = 3 WHERE ser_nome IN ({$dados["PING"]})";
      $this->bancodedados->query($query);
    }
    if ($dados["SSH"]) {
      $dados["SSH"] = @implode(", ", $dados["SSH"]);
      $query = "UPDATE tab_servidores SET ser_data_hora_situacao = IF(seu_id IN (2, 3), ser_data_hora_situacao, NOW()), seu_id = 2 WHERE ser_nome IN ({$dados["SSH"]})";
      $this->bancodedados->query($query);
    }
    $dados = ($dados) ? @implode(", ", $dados) : "''";
    $query = "UPDATE tab_servidores SET ser_data_hora_situacao = IF(seu_id = 1, ser_data_hora_situacao, NOW()), seu_id = 1 WHERE ser_nome NOT IN ($dados)";
    $this->bancodedados->query($query);
  }

  function buscaDadosNagios() {
    $url = "http://nagios:tiririca@nagios2.wavesystem.com.br/nagios/cgi-bin/status.cgi?host=all&servicestatustypes=28";
    // Inicializa Sessão CURL
    $sessao = curl_init();
    curl_setopt($sessao, CURLOPT_TIMEOUT, 5);
    curl_setopt($sessao, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($sessao, CURLOPT_URL, $url);
    // Executa Curl
    $conteudo = curl_exec($sessao);
    // Finaliza Sessão CURL
    curl_close($sessao);
    return $this->trataDados($conteudo);
  }

  function trataDados($dados) {
    $hosts = $this->buscaHosts($dados);
    foreach ($hosts as $host) {
      $services = $this->buscaServices($dados, $host);
      foreach ($services as $service) {
        $dados_tratados[$host][$service] = $this->buscaDetalhes($dados, $host, $service);
      }
    }
    return $dados_tratados;
  }

  function buscaHosts($dados) {
    preg_match_all('/\<A HREF\=\'extinfo.cgi\?type\=1\&host\=(.*)\'\>/U', $dados, $hosts);
    $hosts = $hosts[1];
    for ($i = 0; $i < count($hosts); $i++) {
      $hosts[$i] = urldecode(str_replace("&nbsp;", "", (strip_tags($hosts[$i]))));
    }
    return $hosts;
  }

  function buscaServices($dados, $host) {
    preg_match_all("/\<A HREF\=\'extinfo.cgi\?type\=2\&host\=$host\&service\=(.*)\'\>/U", $dados, $services);
    $services = $services[1];
    for ($i = 0; $i < count($services); $i++) {
      $services[$i] = urldecode(str_replace("&nbsp;", "", (strip_tags($services[$i]))));
    }
    return $services;
  }

  function buscaDetalhes($dados, $host, $service) {
    preg_match('/<A HREF=\'extinfo.cgi\?type=2&host='.urlencode($host)."&service=".urlencode($service)."'>$service</A>(.*)</TD>@</TR>@</U", str_replace("\n", "@", $dados), $retorno);
    $retorno = explode("@", preg_replace("/@+/", "@", strip_tags($retorno[1])));
    $detalhes["status"] = trim($retorno[1]);
    $detalhes["ultima_verificacao"] = trim($retorno[2]);
    $detalhes["duracao"] = trim($retorno[3]);
    $detalhes["tentativas"] = trim($retorno[4]);
    $detalhes["informacao"] = trim($retorno[5]);
    return $detalhes;
  }

}

?>
