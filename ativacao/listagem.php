<?
$modo = $_GET['modo'];

if ($_GET['inicio']) {
  $inicio = substr($_GET['inicio'],6,4)."-".substr($_GET['inicio'],3,2)."-".substr($_GET['inicio'],0,2);
  $where .= " DATE_FORMAT(ativacao_data,'%Y-%m-%d') >= '$inicio' AND";
}
if ($_GET['termino']) {
  $termino = substr($_GET['termino'],6,4)."-".substr($_GET['termino'],3,2)."-".substr($_GET['termino'],0,2);
  $where .= " DATE_FORMAT(ativacao_data,'%Y-%m-%d') <= '$termino' AND";
}
if ($_GET['edicao']) {
  if ($_GET['edicao'] == 's') $where .= " editado = '1' AND";
  if ($_GET['edicao'] == 'n') $where .= " editado = '0' AND";
}
if ($where) {
  $tamanho = strlen($where);
  $where = "WHERE " . substr($where, 0, $tamanho-4);
}
if ($_GET['tipo'] == 'dia') {
  $_GET['inicio'] = date('d/m/Y');
  $_GET['termino'] = date('d/m/Y');
  $where = "WHERE ativacao_data = CURDATE()";
} if ($_GET['tipo'] == 'semana') {
	$inicio = strtotime("-".(date('d') - date("w"))." days");
	$termino = strtotime("+".(6 - date("w"))." days");
  $_GET['inicio'] = date("d/m/Y", $inicio);
  $_GET['termino'] = date("d/m/Y", $termino);
  $where = "WHERE ativacao_data BETWEEN '".date("Y-m-d", $inicio)."' AND '".date("Y-m-d", $termino)."'";
} if ($_GET['tipo'] == 'mes') {
  $_GET['inicio'] = date('01/m/Y');
  $_GET['termino'] = date('t/m/Y');
  $where = "WHERE ativacao_data BETWEEN '".date("Y-m-01")."' AND '".date("Y-m-t")."'";
}

require_once('conexao.php');
require_once('validacao.inc');


$query = "
    SELECT
      tab_contratos.cor_codigo AS codigo,
      tab_visitas.vis_data AS data
    FROM
      tab_visitas
      INNER JOIN tab_contratos on (tab_visitas.cor_id = tab_contratos.cor_id)
    WHERE
		tab_visitas.vis_data >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
    ORDER BY
      tab_contratos.cor_codigo";
$query = @mysql_query($query, $conexao);

while (($contratos = mysql_fetch_array($query)) != false) {

  $query2 = "
      SELECT
        count(codigo) AS quantidade
      FROM
        ativacao_$modo
      WHERE
        codigo = '$contratos[codigo]'";
  $query2 = @mysql_query($query2, $conexao);
  $quantidade = @mysql_result($query2, 0, 0);

  if ($quantidade == 0) {
    $query3 = "INSERT INTO ativacao_$modo (data_hora, codigo, ativacao_data) VALUES (NOW(), '$contratos[codigo]', '$contratos[data]')";
    $query3 = @mysql_query($query3, $conexao);
  }

}

$query4 = "
    SELECT
      *
    FROM
      ativacao_$modo
    $where
    ORDER BY
      ativacao_data, codigo ASC";
$query4 = @mysql_query($query4, $conexao);

function buscaNome($codigo) {
  global $conexao;
  $cliente_id = intval(substr($codigo, 5, 9), 10);
  $query = "
    SELECT
      IF(LOCATE(' ', cli_nome) > 1, LEFT(cli_nome, LOCATE(' ', cli_nome) -1 ), cli_nome) AS nome
    FROM
      tab_clientes
    WHERE
      cli_id = $cliente_id";
  $query = @mysql_query($query, $conexao);
  return @mysql_result($query, 0, 0);
}

function buscaOrigem($codigo) {
  global $conexao;
  $cliente_id = intval(substr($codigo, 5, 9), 10);
  $query = "
    SELECT
      LEFT(clo_nome, 1) AS origem
    FROM
      tab_clientes
      INNER JOIN tab_clientes_origens ON (tab_clientes.clo_id = tab_clientes_origens.clo_id)
    WHERE
      cli_id = $cliente_id";
  $query = @mysql_query($query, $conexao);
  return @mysql_result($query, 0, 0);
}

function formataDinheiro($valor, $moeda = 'R$') {
  if ($valor !== null) {
    $valor = preg_replace("/[^0-9]/", "", intval($valor));
    $valor = number_format($valor / 100, 2, ",", ".");
    if ($moeda) $valor = $moeda." ".$valor;
    return $valor;
  } else {
    return "&nbsp;";
  }
}

?>
<html>
  <head>
    <title>Wave System Internet - Ativação</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" content="Wed, 26 Feb 1997 08:21:57 GMT">
    <meta http-equiv="Cache-Control" content="no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <link href="wavesystem.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="wavesystem.js"></script>
  </head>

<body alink="White" vlink="White" link="White">
<!-- <center><img src="logo.jpg"></center><br> -->

<?
// MÓDULO ADMINISTRATIVO
if ($modo == 'administrativo') {
  include_once("list_administrativo.php");
}

// MÓDULO FINANCEIRO
if ($modo == 'financeiro') {
  include_once("list_financeiro.php");
}

// MÓDULO ADMINISTRATIVO
if ($modo == 'tecnico') {
  include_once("list_tecnico.php");
}
?>

</body>
</html>
