<?

	session_start();

	//**************************************************************************

	$arrContextOptions=stream_context_create(array(
		"ssl" => array(
			 "verify_peer" => false,
			 "verify_peer_name" => false,
		)));
	
	$url = "https://oletv.oletelecom.com.br";
	// $url = "http://localhost/intranet/intranet-tv";
	
	$soapClient = new SoapClient(null, array('location' => "{$url}/soap/faturas/faturas2.php", 'uri' => "faturas", "stream_context" => $arrContextOptions));
	$soapClientDesbloqueio = new SoapClient(null, array('location' => "{$url}/soap/faturas/desbloqueio.php", 'uri' => "desbloqueio", "stream_context" => $arrContextOptions));

	//**************************************************************************

	if (isset ( $_REQUEST ["acao"] ))
	{
		$acao 			= $_REQUEST ["acao"];
		$idcliente		= $_REQUEST ['idcliente'];
		$idboleto		= $_REQUEST ['idfatura'];

		if ($acao == "getDesbloquear") {
			$cliente_id = $_REQUEST ['cliente_id'];
			$datanasc = $_REQUEST ['datanasc'];
			$cpf_cnpj = $_REQUEST['idcliente'];

			$faturas = $soapClientDesbloqueio->getDesbloquear($cpf_cnpj, $datanasc, $cliente_id);

			echo(json_encode ($faturas));
		}

		if ($acao == "getFaturas")
		{
			$datanasc		= $_REQUEST ['datanasc'];

			$faturas = $soapClient->getFaturas($idcliente, $datanasc);
			
			for ($j=0; $j<count($faturas["clientes"]); $j++)
			{

				for ($i = 0; $i < count($faturas["clientes"][$j]["faturas"]); $i++)
				{
					if ($faturas["clientes"][$j]["faturas"][$i]['coa_id'] != 0) 
					{
						$hash = md5("OLE Segurança!!" . $faturas["clientes"][$j]["faturas"][$i]['coa_id']);
						$_SESSION['faturas'][$hash] = $faturas["clientes"][$j]["faturas"][$i]['coa_id'];
						$faturas["clientes"][$j]["faturas"][$i]['coa_id'] = $hash;
					}
				}
			}

			echo(json_encode ($faturas));
		}
		else if ($acao == "imprimefatura")
		{
			
			$idboleto			= $_SESSION['faturas'][$idboleto];
			$codigo 			= $soapClient->getCodigoBoleto($idboleto);
			$boleto 			= $soapClient->getBoleto($idboleto);

			$impressao = base64_decode($boleto);
			
			header ( "Content-type: application/pdf;" );
			header ( "Content-disposition: inline; filename=Boleto-" . $codigo . ".pdf;" );
			header ( "Content-length: " . strlen ( $impressao ) );

			echo ($impressao);

		}
		else if ($acao == "downloadfatura")
		{
			$idboleto 			= $_SESSION['faturas'][$idboleto];
			$boleto 			= $soapClient->getBoleto($idboleto);
			$codigo 			= $soapClient->getCodigoBoleto($idboleto);

			$impressao = base64_decode($boleto);
			header ( "Content-type: application/pdf;" );
			header ( "Content-disposition: attachment; filename=Boleto-" . $codigo . ".pdf;" );
			header ( "Content-length: " . strlen ( $impressao ) );

			echo ($impressao);
		}
	}

?>
