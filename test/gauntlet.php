<?php
require '../tessera.php';

class Gauntlet extends Tessera {
	
	function __before() {
		?>
		<ul>
			<li><a href="gauntlet.php?/first">/first</a></li>
			<li><a href="gauntlet.php?/second/foo">/second/$foo</a></li>
			<li><a href="gauntlet.php?/third/foo">/third/*</a></li>
			<li><a href="gauntlet.php?/fourth/foo/bar">/fourth/$foo/*</a></li>
			<li><a href="gauntlet.php?/fifth/foo">^/fifth/(\w+)</a></li>
			<li><a href="gauntlet.php?/sixth/foo/bar">/sixth/**</a></li>
			<li><a href="gauntlet.php?/seventh/foo/baz/bar/quux/zwei">/seventh/$foo/*/$bar/**</a></li>
		</ul>
		<p>The current request is: <strong><?php echo $this->request_path; ?></strong></p>
		<?php
	}

	function index() {
		echo "<p>The stress test!</p>";
	}
	
	function basic() {
		echo "First: PASS";
	}
	
	function named() {
		echo "<p>Testing named params</p>";
		echo "<pre>";
		print_r($this->params);
		echo "</pre>";
	}
	
	function splat() {
		echo "<p>Testing splat parameters</p>";
		echo "<pre>";
		print_r($this->splat);
		echo "</pre>";
	}
	
	function regex() {
		echo "<p>Testing regular expression matches</p>";
		echo "<pre>";
		print_r($this->params);
		echo "</pre>";
	}
	
	function mixed() {
		echo "<p>Testing named params</p>";
		echo "<pre>";
		print_r($this->params);
		echo "</pre>";
		echo "<p>Testing splat parameters</p>";
		echo "<pre>";
		print_r($this->splat);
		echo "</pre>";
	}
	
}

$gauntlet = new Gauntlet(array(
	'/' => 'index',
	'/first' => 'basic',
	'/second/$foo' => 'named',
	'/third/*' => 'splat',
	'/fourth/$foo/*' => 'mixed',
	'^/fifth/(\w+)' => 'regex',
	'/sixth/**' => 'splat',
	'/seventh/$foo/*/$bar/**' => 'mixed'
));
