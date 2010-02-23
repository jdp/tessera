<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html>
	<head>
		<title>Tessera Laid Out</title>
		<style type="text/css">
			body { font-family: Helvetica, "Helvetica Neue", Arial, sans-serif; }
			#wrapper { width: 760px; margin: auto; }
			span.filename { font-family: monospace; }
		</style>
	</head>
	<body>
		<div id="wrapper">
			<h1>Tessera: Laid Out</h1>
			<p>In Tessera, layouts get no special treatment and there is no special way to work with them. They are regular views.</p>
			<p>The special variable, <code>$this->view</code> can be manipulated to act like a layout. In the <code>YourApp::__before()</code> function, just change <code>$this->view->name</code> to any view name and it will use that as a layout. Then just set a variable inside of the view to the results of a call to <code>render()</code> on another view.</p>
			<div style="background-color: #eeeeee; padding: 1em;">
				<?php echo $content; ?>
			</div>
		</div>
	</body>
</html>
		
