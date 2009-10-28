<?php
require '../tessera.php';

class ErrorHandlingApp extends Tessera {

	function index() {
		echo "Deep down, deep down, dadi dadu dadu dadi dada";
	}
	
}

$basic = new ErrorHandlingApp(array(
	'/' => 'index',
));
