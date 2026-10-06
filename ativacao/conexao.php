<?

  $hostname_conexao = "10.0.100.250";
	$database_conexao = "intranet";
	$username_conexao = "intranet";
	$password_conexao = "WSinTr@net";

	$conexao = (@mysql_Connect($hostname_conexao, $username_conexao, $password_conexao));
  @mysql_select_db($database_conexao, $conexao);

?>
