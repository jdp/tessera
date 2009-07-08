<?php
/**
 * Tessera, another minimalist PHP framework
 * @author Justin Poliey <jdp34@njit.edu>
 * @copyright 2009 Justin Poliey <jdp34@njit.edu>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package Tessera
 */

error_reporting(E_ALL);

/**
 * Tessera, a minimalist PHP framework
 */
class Tessera {

	/**
	 * Compiled regular expression routes that match requests to methods
	 * @var array
	 * @access private
	 */
	private $routes;
	
	/**
	 * Local variables set by {@link Tessera::set} accessible to views and layouts
	 * @var array
	 * @access private
	 */
	private $locals = array();
	
	/**
	 * Generate clean or messy URLs
	 * @var boolean
	 * @access private
	 */
	private $clean_urls = false;

	/**
	 * Creates a Tessera application
	 * @param array $routes Array of routes matched to method names
	 * @param array $config Configuration values
	 */
	function __construct($routes, $config = array()) {
		$this->config = $config;
		$this->request_method = $_SERVER['REQUEST_METHOD'];
		/* Snag the query string and use it as the request path */
		if (isset($_SERVER['REDIRECT_QUERY_STRING'])) {
			$_SERVER['QUERY_STRING'] = $_SERVER['REDIRECT_QUERY_STRING'];
		}
		$this->request_path = $_SERVER['QUERY_STRING'];
		/* Set a default request path if necessary */
		if (strlen($this->request_path) == 0) {
			$this->request_path = '/';
		}
		/* Compile all routes, select one, and respond */
		$this->routes = $this->compileRoutes($routes);
		if (!$this->routeRequest($this->request_path, $this->routes)) {
			$this->action = '__error';
			$this->params = array('code' => 404);
		}
		$this->respond($this->action);
	}
	
	/**
	 * Returns a nicely formatted platform-independent path. Takes a variable number of arguments, each being part of a path
	 * @return string
	 */
	private function path_join() {
		return join(func_get_args(), DIRECTORY_SEPARATOR);
	}
	
	/**
	 * Makes a variable available to views and templates
	 * @param string $local The name of the variable
	 * @param mixed $value The value of the variable
	 */
	protected function set($local, $value) {
		if (is_string($local) && preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $local)) {
			$this->locals[$local] = $value;
		}
	}

	/**
	 * Compiles Tessera routes into regular expressions
	 * @param array $routes List of Tessera routes
	 */
	private function compileRoutes($routes) {
		$compiled_routes = array();
		/* Replace each route with a regular expression (if it isn't already) */
		foreach ($routes as $pattern => $action) {
			if (substr($pattern, 0, 1) == '^') {
				$compiled_routes[] = array(
					'pattern' => str_replace('/', '\\/', $pattern),
					'action'  => $action,
				);
				continue;
			}
			$compiled = array(
				'pattern' => '^' . preg_quote($pattern, '/'),
				'action'  => $action
			);
			/* Replace all named params with a regular expression match */
			preg_match_all('/\/\$(\w+)/i', $pattern, $named_params);
			foreach ($named_params[1] as $i => $v) {
				$compiled['pattern'] = str_replace("/\\\${$v}", "/(?P<{$v}>\w+)", $compiled['pattern']);
				$compiled['params'][$i] = $v;
			}
			/* Replace all splat params with a regular expression match */
			$compiled['pattern'] = preg_replace('/\\\\\*\\\\\*/', "(.*)", $compiled['pattern']);
			$compiled['pattern'] = preg_replace('/\\\\\*([^*]?)/', "([^\/]*)$1", $compiled['pattern']);
			/* Add the compiled pattern to the list of routes */
			$compiled_routes[] = $compiled;
		}
		return $compiled_routes;
	}

	/**
	 * Routes a request to a Tessera route, and calls the related action
	 * @param string $request_path The URL being requested
	 * @param array $routes The compiled Tessera routes to be matched against
	 * @return boolean
	 */
	private function routeRequest($request_path, $routes) {
		foreach($routes as $id => $route) {
			$final_pattern = "/{$route['pattern']}(?:\/)?$/i";
			if (preg_match($final_pattern, $request_path, $raw)) {
				$this->params = array();
				$this->splat = array();
				reset($raw); // Reset array iterator
				next($raw); // Skip the whole URL match
				while (list($key, $value) = each($raw)) {
					$this->params[] = $value;
					if (is_string($key)) {
						$this->params[$key] = $value;
						next($raw);
					}
					else {
						$this->splat[] = $value;
					}
				}
				$this->action = $route['action'];
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Responds to a matched Tessera route
	 * @param string $action The name of the action being called
	 */
	private function respond($action) {
		/* Layout defaults to layout.html, and view to <action>.html */
		if (!isset($this->view)) {
			$this->view = $action;
		}
		/* Make sure the action is callable and not a Tessera internal */
		$protected_actions = array('path_join', 'set', 'compileRoutes', 'routeRequest', 'respond', 'render');
		if (!is_callable(array($this, $action)) || in_array($action, $protected_actions)) {
			trigger_error("Unhandled not found request to <strong>{$this->request_path}</strong>", E_USER_ERROR);
		}
		/* Call the action and snag its output */
		$this->locals = array();
		if (is_callable(array($this, '__before'))) {
			call_user_func(array($this, '__before'));
		}
		ob_start();
		/* Send named params as function arguments, for backward compatibility */
		$positionals = array();
		foreach ($this->params as $name => $value) {
			if (is_string($name)) {
				$positionals[] = $value;
			}
		}
		call_user_func_array(array($this, $action), $positionals);
		$this->script_output = ob_get_clean();
		/* Load and execute the view file if it exists. Otherwise its value is the script output */
		$view_html = $this->render($this->view, false);
		$this->view_output = $view_html ? $view_html : $this->script_output;
		/* Load, execute, and display the layout file. If it can't, display the view output */
		if (isset($this->layout)) {
			$this->layout_output = $this->render($this->layout);
		}
		echo isset($this->layout_output) ? $this->layout_output : $this->view_output;
	}
	
	/**
	 * Renders a view and returns its HTML representation
	 * @param string $view The name of the view
	 * @param boolean $force Force the file to exist
	 * @return string
	 */
	protected function render($view, $force = true) {
		$view_file = $this->path_join('views', $view . '.html');
		if (!is_file($view_file)) {
			if ($force) {
				trigger_error("View file <strong>{$view_file}</strong> associated with <strong>{$this->action}</strong> not found", E_USER_ERROR);
			}
			else {
				return null;
			}
		}
		extract($this->locals);
		ob_start();
		include $view_file;
		$html = ob_get_clean();
		return $html;
	}
}
?>
