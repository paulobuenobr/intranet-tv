<?php
// 	session_start();
//     $id        = $_SESSION["id"];
// 	$superuser = $_SESSION['superuser'];
// 	$enabled   = $_SESSION['enabled'];

	define ("TEXTERRORNOERROR", "Operação efetuada com sucesso!");
	define ("TEXTINSERTSUCCESS", "Inserção dos dados feita com sucesso.");
	define ("TEXTERRORNORECORDS", "Nenhum registro encontrado!");
	define ("TEXTERRORDBQUERY", "Erro na consulta ao BD!");
	define ("TEXTERRORDBACCESS", "Erro na consulta ao BD! Erro de acesso!");
	define ("TEXTERRORQUERYARGS", "Argumentos inválidos!");
	define ("TEXTERRORNOSMSFILE", "Atenção - Você não inseriu um arquivo válido para processar.");
	define ("TEXTERRORNOMESSAGE", "Atenção - Você não inseriu uma mensagem.");
	define ("TEXTERRORNOPHONENUMBERS", "Atenção - Você não inseriu os números de telefones corretamente.");
	define ("TEXTERRORPHONENUMBERS", "Atenção - Erro no formato do(s) número(s).");



	// Connection details
	$manager_host = "127.0.0.1";
	$manager_user = "admin";
	$manager_pass = "scanner2013";
	
	// Default Port
	$manager_port = "5038";
	
	// Connection timeout - 30s
	$manager_connection_timeout = 30;
	
	// The type of peer (i.e. iax2 or sip)
	$peer_type = "sip";
	
?>
