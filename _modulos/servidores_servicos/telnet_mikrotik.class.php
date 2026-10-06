<?

require_once("telnet.class.php");

/*
$mikrotik = new telnet_mikrotik("200.194.239.230");
if ($mikrotik->conectar()) {
	if ($mikrotik->autenticar("admin", "elr3OII,")) {
		print $mikrotik->executar("/ip dhcp-server lease print detail without-paging");
	}
	$mikrotik->desconectar();
}
*/

class telnet_mikrotik {
	
	var $telnet;
	var $debug;
	
	public function __construct($ip, $port = 23, $debug = false) {
		$this->telnet = new telnet($ip, $port);
		$this->telnet->setPrompt("] > ");
		$this->debug = $debug;
	}
	
	public function conectar() {
		if ($this->telnet->connect()) {
			return true;
		} else {
			return $this->telnet->getLastError();
		}
	}
	
	public function desconectar() {
		$this->executar("/quit");
		$this->telnet->disconnect();
	}
	
	public function autenticar($usuario, $senha) {
		if ($this->telnet->readTo("Login: ")) {
			$this->telnet->write($usuario);
			if ($this->telnet->readTo("Password: ")) {
				$this->telnet->write($senha);
				if ($this->telnet->waitPrompt()) {
					return true;
				}
			}
		}
		return false;
	}
	
	public function executar($comando) {
		if ($this->debug) {
			print $comando."\n";
			return;
		}
		$this->telnet->write($comando);
		$this->telnet->waitPrompt();
		$this->telnet->waitPrompt();
		$this->telnet->waitPrompt();
		return $this->telnet->getBuffer();
	}
	
}

?>
