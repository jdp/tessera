<?php
require '../tessera.php';

class ViewableApp extends Tessera\Base {

	function main() {
		$this->view->set('lyrics', "Deep down, deep down, dadi dadu dadu dadi dada");
	}
	
}

$basic = new ViewableApp(array(
	'/' => 'main',
));
