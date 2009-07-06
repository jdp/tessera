<?php
require '../tessera.php';

class ViewableApp extends Tessera {

	function main() {
		// pass $lyrics to the view
		// the view is in views/main.html
		$this->set('lyrics', "Deep down, deep down, dadi dadu dadu dadi dada");
	}
	
}

$basic = new ViewableApp(array(
	'/' => 'main',
));
