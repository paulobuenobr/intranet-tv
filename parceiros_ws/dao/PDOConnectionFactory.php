<?php
	@include("../include/dbconnection.php");

	class PDOConnectionFactory
	{
		public $con			= NULL;
		public $dbType		= "mysql";
		public $host		= DB_SERVER;
		public $user		= DB_USER;
		public $senha		= DB_PASSWORD;
		public $db			= DB_DATABASE;
		public $dbport		= DB_PORT;
		public $persistent	= FALSE;

		public function PDOConnectionFactory($persistent = FALSE)
		{
			if ($persistent != FALSE)
			{
				$this->persistent = TRUE;
			}
		}

		public function getConnection()
		{
			try
			{
				$this->con = new PDO($this->dbType . ":host=" . $this->host . ";dbname=" . $this->db, $this->user, $this->senha, array(PDO::ATTR_PERSISTENT => $this->persistent));
				$this->con -> exec("SET CHARACTER SET utf8");
				return $this->con;
			}
			catch (PDOException $ex)
			{
				echo "ConnectionFactory Error: " . $ex->getMessage() . "<br />";
			}
		}

		public function Close()
		{
			if ($this->con != NULL) $this->con = NULL;
		}
	}

?>
