<?php
require '../tessera.php';

class BrokenApp extends Tessera {

	/* If any HTTP error occurs, it gets routed here */
	function __error($code) {
		echo "Error code {$code} for <strong>{$this->request_path}</strong>";
	}
	
	function index() {
		echo "Deep down, deep down, dadi dadu dadu dadi dada";
	}
	
}

$basic = new BrokenApp(array(
	'/' => 'index',
));
