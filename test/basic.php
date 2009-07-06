<?php
require '../tessera.php';

class BasicApp extends Tessera {

	function index() {
		echo "Deep down, deep down, dadi dadu dadu dadi dada";
	}
	
}

$basic = new BasicApp(array(
	'/' => 'index',
));
