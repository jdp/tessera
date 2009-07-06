<?php
/**
 * Tessera, another minimalist PHP framework
 * @author Justin Poliey <jdp34@njit.edu>
 * @copyright 2009 Justin Poliey <jdp34@njit.edu>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package Tessera
 */
 
if (!defined('__DIR__')) {
	define('__DIR__', dirname(__FILE__));
}

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
	private function set($local, $value) {
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
		/* Replace each route with a regular expression */
		foreach ($routes as $pattern => $action) {
			preg_match_all('/\/\$(\w+)/i', $pattern, $params);
			$compiled = array(
				'pattern' => preg_quote($pattern, '/'),
				'action'  => $action
			);
			/* Replace all $params with a regular expression match, and save the $param */
			foreach ($params[1] as $i => $v) {
				$compiled['pattern'] = str_replace("/\\\${$v}", "/(\w+)", $compiled['pattern']);
				$compiled['params'][$i] = $v;
			}
			array_push($compiled_routes, $compiled);
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
			if (preg_match("/^{$route['pattern']}$/i", $request_path, $raw)) {
				$this->action = $route['action'];
				$this->params = array();
				for($i = 1; $i < count($raw); $i++) {
					$this->params[$route['params'][$i-1]] = $raw[$i];
				}
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
		$this->view = $action;
		/* Make sure the action is callable and not a Tessera internal */
		$protected_actions = array('path_join', 'set', 'compileRoutes', 'routeRequest', 'respond');
		if (!is_callable(array($this, $action)) || in_array($action, $protected_actions)) {
			trigger_error("Unhandled not found request to <strong>{$this->request_path}</strong>", E_USER_ERROR);
		}
		/* Call the action and snag its output */
		$this->locals = array();
		if (is_callable(array($this, '__before'))) {
			call_user_func(array($this, '__before'));
		}
		ob_start();
		call_user_func_array(array($this, $action), array_values($this->params));
		$this->script_output = ob_get_clean();
		foreach($this->locals as $__local => $__value) {
			${$__local} = $__value;
		}
		/* Load and execute the view file if it exists. Otherwise its value is the script output */
		$view_file = $this->path_join(__DIR__, 'views', $this->view . '.html');
		if (is_file($view_file)) {
			ob_start();
			include $view_file;
			$this->view_output = ob_get_clean();
		}
		else {
			$this->view_output = $this->script_output;
		}
		/* Load, execute, and display the layout file. If it can't, display the view output */
		if (isset($this->layout)) {
			$layout_file = $this->path_join(__DIR__, 'views', $this->layout . '.html');
			if (!is_file($layout_file)) {
				trigger_error("Layout file <strong>{$layout_file}</strong> associated with <strong>{$action}</strong> not found", E_USER_ERROR);
			}
			ob_start();
			include $layout_file;
			$this->layout_output = ob_get_clean();
			echo $this->layout_output;
		}
		else {
			echo $this->view_output;
		}
	}
		
}
?>
