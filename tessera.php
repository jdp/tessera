<?php
/**
 * Tessera, another minimalist PHP framework
 * @author Justin Poliey <jdp34@njit.edu>
 * @copyright 2009-2010 Justin Poliey <jdp34@njit.edu>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package Tessera
 */

/**
 * Tessera, a minimalist PHP framework
 */
namespace Tessera;

error_reporting(E_ALL);

function path_join() {
	return join(func_get_args(), DIRECTORY_SEPARATOR);
}

/*
 * A simple wrapping for a View. A view is the visual representation of the
 * code being run in the action matched to the route.
 */
class View {

	/*
	 * The name of the view without any file extension.
	 * @access public
	 */
	public $name;

	/*
	 * The local variables available only to that view
	 * @access protected
	 */
	protected $locals = array();

	/*
	 * Creates a new view and assigns it a name
	 * @param string $name The name of the view
	 */
	public function __construct($name){
		$this->name = $name;
	}

	public function __set($name, $value) {
		$this->locals[$name] = $value;
	}

	public function & __get($name) {
		return $this->locals[$name];
	}

	/*
	 * Sets a variable local to the view. If passed an array, it will treat
	 * the keys as the variable names and the values as the variable values.
	 * @param string|array $name_or_array Either the name of the variable, or an array of name => variable mappings
	 * @param any $value The value of the variable, if a name was provided
	 */
	public function set($name_or_array, $value = null) {
		if (is_array($name_or_array)) {
			foreach ($name_or_array as $name => $value) {
				$this->locals[$name] = $value;
			}
		}
		else if (is_string($name_or_array)) {
			$this->locals[$name_or_array] = $value;
		}
	}

	/*
	 * Returns the filename of the view
	 * @return string
	 */
	public function getFilename() {
		return path_join('views', $this->name.'.php');
	}

	/*
	 * Either returns the contents of a view's file, or outputs the contents
	 * of the view's file directly, based on the value of the $echo parameter.
	 * @param boolean $echo Whether or not to output the view contents directly
	 * @return string
	 */
	public function render($echo = FALSE) {
		if (file_exists($this->getFilename())) {
			if (!$echo) {
				ob_start();
			}
			extract($this->locals);
			include $this->getFilename();
			if (!$echo) {
				return ob_get_clean();
			}
		}
		else {
			return null;
		}
	}

}

/*
 * The Tessera base class is what responds to the requests. It looks for the
 * first matching route and calls the matching action.
 */
class Base {

	protected $layout = null;

	/**
	 * Compiled regular expression routes that match requests to methods
	 * @var array
	 * @access protected
	 */
	protected $routes;

	/**
	 * Creates a Tessera application
	 * @param array $routes Array of routes matched to method names
	 * @param array $config Configuration values
	 */
	function __construct($routes, $config = array()) {
		$this->config = $config;
		$this->errors = array();
		$this->request_method = $_SERVER['REQUEST_METHOD'];
		/* Snag the query string and use it as the request path */
		if (isset($_SERVER['REDIRECT_QUERY_STRING'])) {
			$_SERVER['QUERY_STRING'] = $_SERVER['REDIRECT_QUERY_STRING'];
		}
		$this->request_path = empty($_SERVER['QUERY_STRING']) ? '/' : $_SERVER['QUERY_STRING'];
		/* Compile all routes, select one, and respond */
		$this->routes = $this->compileRoutes($routes);
		if (!$this->routeRequest($this->request_path, $this->routes)) {
			$this->action = '__error';
			$this->params = array('code' => 404);
		}
		$this->respond($this->action);
	}

	/**
	 * Compiles Tessera routes into regular expressions
	 * @param array $routes List of Tessera routes
	 */
	private function compileRoutes($routes) {
		$compiled_routes = array();
		/* Replace each route with a regexp (if it isn't a regexp already) */
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
				$compiled['pattern'] = str_replace("/\\\${$v}", "/(?P<{$v}>[\w\-]+)", $compiled['pattern']);
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
		/* Make sure the action is callable and not a Tessera internal */
		$protected_actions = array('compileRoutes', 'routeRequest', 'respond', 'render');
		if (!is_callable(array($this, $action)) || in_array($action, $protected_actions)) {
			trigger_error("Unhandled not found request to <strong>{$this->request_path}</strong>", E_USER_ERROR);
		}
		$this->view = new View($action);
		/* Call the action and snag its output */
		$this->locals = array();
		ob_start();
		if (is_callable(array($this, '__before'))) {
			call_user_func(array($this, '__before'));
		}
		/* Send named params as function arguments, for backward compatibility */
		$positionals = array();
		foreach ($this->params as $name => $value) {
			if (is_string($name)) {
				$positionals[] = $value;
			}
		}
		if (!isset($this->use_view)) {
			$this->use_view = TRUE;
		}
		call_user_func_array(array($this, $action), $positionals);
		$this->action_output = ob_get_clean();
		if (is_callable(array($this, '__after'))) {
			call_user_func(array($this, '__after'));
		}
		/* Load and execute the layout if it is loaded. Otherwise use action output */
		if (file_exists($this->view->getFilename()) AND $this->use_view) {
			$this->view->render(TRUE);
		}
		else {
			echo $this->action_output;
		}
	}
	
}
?>
