<?php

	class ParceirosDAO extends PDOConnectionFactory
	{
		// irá receber uma conexão
		public $conex = NULL;

		/**************************************************************************/

		public function ParceirosDAO()
		{
			//echo("ParceirosDAO<br />");

			$this->conex = PDOConnectionFactory::getConnection();
		}

		/**************************************************************************/

		public function ListPartners($state, $city)
		{
			try
			{
				$stmt = $this->conex->QUERY("SELECT DISTINCT tab_clientes_origens.clo_id, tab_clientes_origens.clo_nome FROM tab_estados INNER JOIN tab_cidades ON (tab_estados.est_id = tab_cidades.est_id) INNER JOIN rel_clo_cid_contem ON (rel_clo_cid_contem.cid_id = tab_cidades.cid_id) INNER JOIN tab_clientes_origens ON (rel_clo_cid_contem.clo_id = tab_clientes_origens.clo_id) WHERE tab_estados.est_nome = '" . $state . "' AND tab_cidades.cid_nome = '" . $city . "' ORDER BY tab_clientes_origens.clo_nome");

				$this->conex = NULL;
				return $stmt;
			}
			catch (PDOException $ex)
			{
				echo "Error: " . $ex->getMessage();
			}

		}
		
		/**************************************************************************/

		public function ListPartnersByState($state)
		{
			try
			{
				// This query is wrong... Waiting Murilo
				$stmt = $this->conex->QUERY("SELECT DISTINCT tab_clientes_origens.clo_id, tab_clientes_origens.clo_nome FROM tab_estados INNER JOIN tab_cidades ON (tab_estados.est_id = tab_cidades.est_id) INNER JOIN rel_clo_cid_contem ON (rel_clo_cid_contem.cid_id = tab_cidades.cid_id) INNER JOIN tab_clientes_origens ON (rel_clo_cid_contem.clo_id = tab_clientes_origens.clo_id) WHERE tab_estados.est_nome = '" . $state . "' ORDER BY tab_clientes_origens.clo_nome");


				$this->conex = NULL;
				return $stmt;
			}
			catch (PDOException $ex)
			{
				echo "Error: " . $ex->getMessage();
			}

		}
		
		/**************************************************************************/

		public function ListStates()
		{
			try
			{
				//$stmt = $this->conex->query("SELECT DISTINCT tab_estados.est_nome FROM tab_estados INNER JOIN tab_cidades ON (tab_estados.est_id = tab_cidades.est_id) INNER JOIN rel_clo_cid_contem ON (rel_clo_cid_contem.cid_id = tab_cidades.cid_id) ORDER BY tab_estados.est_nome");

				$stmt = $this->conex->query("SELECT DISTINCT tab_estados.est_id, tab_estados.est_sigla, tab_estados.est_nome FROM tab_estados INNER JOIN tab_cidades ON (tab_estados.est_id = tab_cidades.est_id) INNER JOIN rel_clo_cid_contem ON (rel_clo_cid_contem.cid_id = tab_cidades.cid_id) ORDER BY tab_estados.est_nome");

				$this->conex = NULL;
				return $stmt;
			}
			catch (PDOException $ex)
			{
				echo "Error: " . $ex->getMessage();
			}
		}

		/**************************************************************************/

		public function ListStateCities($state)
		{
			try
			{
				if ($query == NULL)
				{
					$stmt = $this->conex->query("SELECT DISTINCT tab_cidades.cid_id, tab_cidades.cid_nome FROM tab_estados INNER JOIN tab_cidades ON (tab_estados.est_id = tab_cidades.est_id) INNER JOIN rel_clo_cid_contem ON (rel_clo_cid_contem.cid_id = tab_cidades.cid_id) WHERE tab_estados.est_nome = '" . $state . "' ORDER BY tab_cidades.cid_nome");
				}
				else
				{
					$stmt = $this->conex->QUERY($query);
				}
				
				$this->conex = NULL;
				return $stmt;
			}
			catch (PDOException $ex)
			{
				echo "Error: " . $ex->getMessage();
			}

		}

		/**************************************************************************/
	}
?>