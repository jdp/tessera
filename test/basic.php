<?php
require '../tessera.php';

echo "<pre>";
print_r($_SERVER);
echo "</pre>";

class BasicApp extends Tessera {

	function index() {
		echo "Deep down, deep down, dadi dadu dadu dadi dada";
	}
	
}

$basic = new BasicApp(array(
	'/' => 'index',
));
