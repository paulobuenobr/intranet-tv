<?

$modo = $_GET['modo'];
$id = $_GET['id'];

require_once('conexao.php');
require_once('validacao.inc');

switch($modo) {
  case ('tecnico'):
  tecnico($id);
  break;
  default:
  retornar($modo);
  break;
}

############################
##### FUNÇÕES GERAIS #######
############################

function carregaArquivo($arquivo) {
  $fp = fopen($arquivo, "r");
  $retorno = fread($fp, filesize($arquivo));
  fclose($fp);
  return $retorno;
}

############################
####### BUSCA DADOS ########
############################

function buscaQuantidadeCodigo ($codigo) {
  GLOBAL $conexao;
  $query = "
    SELECT count(id)
    FROM
      ativacao_tecnico
    WHERE
      codigo = '$codigo'";

  $query = @mysql_query($query, $conexao);
  $quantidade = @mysql_result($query,0,0);

  return $quantidade;
}

function buscaIdPorCodigo ($codigo) {
  GLOBAL $conexao;
  $query = "
    SELECT id
    FROM
      ativacao_tecnico
    WHERE
      codigo = '$codigo'";

  $query = @mysql_query($query, $conexao);
  $id = @mysql_result($query,0,0);

  return $id;
}

function existeCodigo ($codigo) {
  GLOBAL $conexao;
  $query = "
    SELECT cor_id
    FROM
      tab_contratos
    WHERE
      tab_contratos.cor_codigo = '$codigo'";

  $query = @mysql_query($query, $conexao);
  $existe = @mysql_numrows($query);

  return $existe;
}

############################
###### ADICIONA DADOS ######
############################

function tecnico ($id) {
  if (empty($_POST["confirmar"])) {
    $conteudo = carregaArquivo("add_tecnico.php");
    print "$conteudo";
  } else {
    GLOBAL $conexao;
    $quantidade = buscaQuantidadeCodigo($_POST["codigo"]);

    if ($quantidade == 0) {
      if (!existeCodigo($_POST["codigo"])) {
        header("Location: editar.php?modo=tecnico&id=$id&existe=1");
        exit;
      }

      $_POST["ativacao_data"] = substr($_POST["ativacao_data"],6,4).substr($_POST["ativacao_data"],3,2).substr($_POST["ativacao_data"],0,2);

      $_POST["vo_instalacao"] = preg_replace("/[^0-9]/","",$_POST["vo_instalacao"]);
      $_POST["vo_rede"] = preg_replace("/[^0-9]/","",$_POST["vo_rede"]);
      $_POST["vo_hub"] = preg_replace("/[^0-9]/","",$_POST["vo_hub"]);
      $_POST["vo_total"] = $_POST["vo_instalacao"]+$_POST["vo_rede"]+$_POST["vo_hub"];

      $query = "INSERT INTO ativacao_administrativo (data_hora, codigo, ativacao_data) VALUES (NOW(), '$_POST[codigo]', '$_POST[ativacao_data]')";
      $query = @mysql_query($query, $conexao);

      $query = "INSERT INTO ativacao_financeiro (data_hora, codigo, ativacao_data) VALUES (NOW(), '$_POST[codigo]', '$_POST[ativacao_data]')";
      $query = @mysql_query($query, $conexao);

      $query = "INSERT INTO ativacao_tecnico (data_hora, codigo, ativacao_data, ativacao_cabo, ativacao_conector, ativacao_rede, ativacao_hub, vo_instalacao, vo_rede, vo_hub, vo_total, editado) VALUES (NOW(), '$_POST[codigo]', '$_POST[ativacao_data]', '$_POST[ativacao_cabo]', '$_POST[ativacao_conector]', '$_POST[ativacao_rede]', '$_POST[ativacao_hub]', '$_POST[vo_instalacao]', '$_POST[vo_rede]', '$_POST[vo_hub]', '$_POST[vo_total]', '1')";
      $query = @mysql_query($query, $conexao);
    } else {
      $id = buscaIdPorCodigo($_POST["codigo"]);
      header("Location: editar.php?modo=tecnico&id=$id&existe=2");
      exit;
    }

    header("Location: listagem.php?modo=tecnico");
  }
}
?>
