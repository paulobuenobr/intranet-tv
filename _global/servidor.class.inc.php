<?php

class servidor {
	
	static function getGet($campo = null) {
		
		if (!$campo) {
			return $_GET;
		} elseif (array_key_exists($campo, $_GET)) {
			return $_GET[$campo];
		} else {
			return null;
		}
		
	}
	
	static function getPost($campo = null) {
		
		if (!$campo) {
			return $_POST;
		} elseif (array_key_exists($campo, $_POST)) {
			return $_POST[$campo];
		} else {
			return null;
		}
		
	}
	
}

?>