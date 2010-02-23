<?php
require '../tessera.php';

class LaidOutApp extends Tessera\Base {

	function __before() {
		$this->view->name = 'layout';
	}
	
	function main() {
		$partial = new Tessera\View('main');
		$partial->lyrics = "Deep down, deep down, dadi dadu dadu dadi dada";
		$this->view->content = $partial->render();
	}
	
}

$basic = new LaidOutApp(array(
	'/' => 'main',
));
