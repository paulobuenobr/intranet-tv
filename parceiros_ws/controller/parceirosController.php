<?php

	@require("../dao/PDOConnectionFactory.php");
	@require("../dao/ParceirosDAO.php");
	$action = $_REQUEST ["action"];

	if ($action == 'getpartners') {
		getPartners();
	}
	else if ($action == 'getstatepartners') {
		getPartnersByState();
	}
	else if ($action == 'getstates') {
		getStates();
	}
	else if ($action == 'getcities') {
		getCities();
	}

	/***********************************************************************/

	function getPartners()
	{
		$state	= stripslashes($_REQUEST ["state"]);
		$city	= stripslashes($_REQUEST ["city"]);

		$response	= array();
		$partners	= array();
		$DAO		= new ParceirosDAO();

		$result = $DAO->ListPartners($state, $city);

		if ($result->rowCount() > 0)
		{
			$response["success"] = 1;
			$response["message"] = "success";
			
			foreach ($result as $row)
			{
				$partners[] = array(
					"id" => $row['clo_id'],
					"nome" => $row['clo_nome']
				);
			}

			$response["parceiros"] = $partners;
		}
		else
		{
			$response["success"] = 0;
			$response["message"] = "error";
		}

		returnData($response);
	}

	/***********************************************************************/

	function getPartnersByState()
	{
		$state	= stripslashes($_REQUEST ["state"]);

		$response	= array();
		$partners	= array();
		$DAO		= new ParceirosDAO();

		$result = $DAO->ListPartnersByState($state);

		if ($result->rowCount() > 0)
		{
			$response["success"] = 1;
			$response["message"] = "success";
			
			foreach ($result as $row)
			{
				$partners[] = array(
					"id" => $row['clo_id'],
					"nome" => $row['clo_nome']
				);
			}

			$response["parceiros"] = $partners;
		}
		else
		{
			$response["success"] = 0;
			$response["message"] = "error";
		}

		returnData($response);
	}

	/***********************************************************************/
	
	function getStates()
	{
		$response	= array();
		$estados	= array();
		$DAO		= new ParceirosDAO();
		
		$result = $DAO->ListStates();

		if ($result->rowCount() > 0)
		{
			$response["success"] = 1;
			$response["message"] = "success";
			
			foreach ($result as $row)
			{
				$estados[] = array(
					"id" => $row['est_id'],
					"sigla" => $row['est_sigla'], 
					"nome" => $row['est_nome']
				);
			}

			$response["estados"] = $estados;
		}
		else
		{
			$response["success"] = 0;
			$response["message"] = "error";
		}				

		returnData($response);
	}

	/***********************************************************************/
	
	function getCities()
	{
		$state	= stripslashes($_REQUEST ["state"]);

		$response	= array();
		$cities		= array();
		$DAO		= new ParceirosDAO();

		$result = $DAO->ListStateCities($state);

		if ($result->rowCount() > 0)
		{
			$response["success"] = 1;
			$response["message"] = "success";
			
			foreach ($result as $row)
			{
				$cities[] = array(
					"id" => $row['cid_id'],
					"nome" => $row['cid_nome']
				);
			}

			$response["cidades"] = $cities;
		}
		else
		{
			$response["success"] = 0;
			$response["message"] = "error";
		}

		returnData($response);
	}

	/***********************************************************************/

	function returnData($data)
	{
		header('content-type: application/json; charset=utf-8');

		$jsonRes = json_encode($data);
		echo($jsonRes);
	}
	
	/***********************************************************************/

?>