<?

class telnet {

	var $socket = NULL;
	var $host = "";
	var $port = "23";
	var $error = "";
	var $prompt = "\$ ";
	var $buffer = "";
	
	function __construct($host, $port = 23) {
		$this->host = $host;
		$this->port = $port;
	}
	
	function connect($host = null, $port = null) {
		$errstr = "";
		$errno  = 0;
		if ($host) {
			$this->host = $host;
		}
		if ($port) {
			$this->port = $port;
		}
		$this->socket = pfsockopen($this->host, $this->port, $errno, $errstr, 60);
		if (!$this->socket){
			$this->error = "Unable to open a telnet connection: ".socket_strerror($this->socket);
			return false;
		}
		socket_set_timeout($this->socket, 10, 0);
		return true;
	}

	function disconnect() {
		if ($this->socket) {
			if (!fclose($this->socket)) {
				$this->error = "Error while closing telnet socket";
				return false;
			}
			$this->socket = NULL;
		}
		return true;
	}

	function write($buffer) {
		if (!$this->socket){
			$this->error = "Telnet socket is not open";
			return false;
		}
		$buffer = str_replace(chr(255), chr(255).chr(255), $buffer);
		if (fwrite($this->socket, $buffer."\r") < 0){
			$this->error = "Error writing to socket";
			return false;
		}
		$this->buffer = "";
		return true;
	}
	
	function readTo($text) {
		if (!$this->socket){
			$this->error = "Telnet socket is not open";
			return false;
		}
		
		$NULL = chr(0);
		$IAC = chr(255);
		$buf = '';
		
		while (1) {
			$c = $this->getc();
			if ($c === false){
				$this->error = "Couldn't find the requested text: '".$text."'";
				return false;
			}
			if ($c == $NULL || $c == "\021") {
				continue;
			}
			if ($c == $IAC) {
				$c = $this->getc();
				if ($c != $IAC) {
					if (!$this->negotiateTelnetOption($c)){
						return false;
					} else {
						continue;
					}
				}
			}
			$buf .= $c;
			$this->buffer .= $c;
			if (substr($buf, strlen($buf) - strlen($text)) == $text) {
				return true;
			} elseif (substr($buf, strlen($buf) - 12) == "Login failed") {
				return false;
			}
		}
	}

	function getc() {
		$c = fgetc($this->socket);
		return $c;
	}

	function getBuffer() {
		$buf = $this->buffer;
		$buf = explode("\n", $buf);
		unset($buf[count($buf) - 1]);
		$buf = implode("\n", $buf);
		return trim($this->stripColorCodes($buf));
	}
	
	function stripColorCodes($text) {
		return preg_replace('/\e\[[^m]*m/', '', $text);
	}

	function negotiateTelnetOption($command){
		$IAC = chr(255);
		$DONT = chr(254);
		$DO = chr(253);
		$WONT = chr(252);
		$WILL = chr(251);
		if (($command == $DO) || ($command == $DONT)){
			$opt = $this->getc();
			fwrite($this->socket, $IAC.$WONT.$opt);
		} else if (($command == $WILL) || ($command == $WONT)) {
			$opt = fgetc($this->socket);
			fwrite($this->socket, $IAC.$DONT.$opt);
		} else {
			$this->error = "Unknown command ".ord($command);
			return false;
		}
		return true;
	}

	function waitPrompt() {
		return $this->readTo($this->prompt);
	}

	function setPrompt($s) {
		$this->prompt = $s;
	}

	function setHost($s) {
		$this->host = $s;
	}

	function setPort($s) {
		$this->port = $s;
	}

	function getLastError() {
		return $this->error;
	}

}

?>
