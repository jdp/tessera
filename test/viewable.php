<?php
require '../tessera.php';

class ViewableApp extends Tessera {

	function index() {
		// pass $lyrics to the view
		// the view is in views/index.html
		$this->set('lyrics', "Deep down, deep down, dadi dadu dadu dadi dada");
	}
	
}

$basic = new ViewableApp(array(
	'/' => 'index',
));
