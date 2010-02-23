<?php
require '../tessera.php';

class ArguableApp extends Tessera\Base {

	function index() {
		echo 'Try clicking <a href="arguable.php?/foo/bar">here</a>.';
	}
	
	function foo($bar) {
		echo "Bar is {$bar}, and that's that";
	}
	
}

$basic = new ArguableApp(array(
	'/' => 'index',
	'/foo/$bar' => 'foo'
));
