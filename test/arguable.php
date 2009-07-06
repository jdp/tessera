<?php
require '../tessera.php';

class ArguableApp extends Tessera {
	
	function foo($bar) {
		echo "Bar is {$bar}, and that's that";
	}
	
}

$basic = new ArguableApp(array(
	'/foo/$bar' => 'foo'
));
