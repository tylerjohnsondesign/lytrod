<?php


class AltoRouter
{

    /**
     * @var array Array of all routes (incl. named routes).
     */
    protected $routes = [];

    /**
     * @var array Array of all named routes.
     */
    protected $namedRoutes = [];

    /**
     * @var string Can be used to ignore leading part of the Request URL (if main file lives in subdirectory of host)
     */
    protected $basePath = '';

    /**
     * @var array Array of default match types (regex helpers)
     */
    protected $matchTypes = [
        'i'  => '[0-9]++',
        'a'  => '[0-9A-Za-z]++',
        'h'  => '[0-9A-Fa-f]++',
        '*'  => '.+?',
        '**' => '.++',
        ''   => '[^/\.]++'
    ];

    /**
     * Create router in one call from config.
     *
     * @param array $routes
     * @param string $basePath
     * @param array $matchTypes
     * @throws Exception
     */
    public function __construct(array $routes = [], $basePath = '', array $matchTypes = [])
    {
        $this->addRoutes($routes);
        $this->setBasePath($basePath);
        $this->addMatchTypes($matchTypes);
    }

    /**
     * Retrieves all routes.
     * Useful if you want to process or display routes.
     * @return array All routes.
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Add multiple routes at once from array in the following format:
     *
     *   $routes = [
     *      [$method, $route, $target, $name]
     *   ];
     *
     * @param array $routes
     * @return void
     * @author Koen Punt
     * @throws Exception
     */
    public function addRoutes($routes)
    {
        if (!is_array($routes) && !$routes instanceof Traversable) {
            throw new RuntimeException('Routes should be an array or an instance of Traversable');
        }
        foreach ($routes as $route) {
            call_user_func_array([$this, 'map'], $route);
        }
    }

    /**
     * Set the base path.
     * Useful if you are running your application from a subdirectory.
     * @param string $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Add named match types. It uses array_merge so keys can be overwritten.
     *
     * @param array $matchTypes The key is the name and the value is the regex.
     */
    public function addMatchTypes(array $matchTypes)
    {
        $this->matchTypes = array_merge($this->matchTypes, $matchTypes);
    }

    /**
     * Map a route to a target
     *
     * @param string $method One of 5 HTTP Methods, or a pipe-separated list of multiple HTTP Methods (GET|POST|PATCH|PUT|DELETE)
     * @param string $route The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
     * @param mixed $target The target where this route should point to. Can be anything.
     * @param string $name Optional name of this route. Supply if you want to reverse route this url in your application.
     * @throws Exception
     */
    public function map($method, $route, $target, $name = null)
    {

        $this->routes[] = [$method, $route, $target, $name];

        if ($name) {
            if (isset($this->namedRoutes[$name])) {
                throw new RuntimeException("Can not redeclare route '{$name}'");
            }
            $this->namedRoutes[$name] = $route;
        }

        return;
    }

    /**
     * Reversed routing
     *
     * Generate the URL for a named route. Replace regexes with supplied parameters
     *
     * @param string $routeName The name of the route.
     * @param array @params Associative array of parameters to replace placeholders with.
     * @return string The URL of the route with named parameters in place.
     * @throws Exception
     */
    public function generate($routeName, array $params = [])
    {

        // Check if named route exists
        if (!isset($this->namedRoutes[$routeName])) {
            throw new RuntimeException("Route '{$routeName}' does not exist.");
        }

        // Replace named parameters
        $route = $this->namedRoutes[$routeName];

        // prepend base path to route url again
        $url = $this->basePath . $route;

        if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $index => $match) {
                list($block, $pre, $type, $param, $optional) = $match;

                if ($pre) {
                    $block = substr($block, 1);
                }

                if (isset($params[$param])) {
                    // Part is found, replace for param value
                    $url = str_replace($block, $params[$param], $url);
                } elseif ($optional && $index !== 0) {
                    // Only strip preceding slash if it's not at the base
                    $url = str_replace($pre . $block, '', $url);
                } else {
                    // Strip match block
                    $url = str_replace($block, '', $url);
                }
            }
        }

        return $url;
    }

    /**
     * Match a given Request Url against stored routes
     * @param string $requestUrl
     * @param string $requestMethod
     * @return array|boolean Array with route information on success, false on failure (no match).
     */
    public function match($requestUrl = null, $requestMethod = null)
    {

        $params = [];

        // set Request Url if it isn't passed as parameter
        if ($requestUrl === null) {
            $requestUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        }

        // strip base path from request url
        $requestUrl = substr($requestUrl, strlen($this->basePath));

        // Strip query string (?a=b) from Request Url
        if (($strpos = strpos($requestUrl, '?')) !== false) {
            $requestUrl = substr($requestUrl, 0, $strpos);
        }

        $lastRequestUrlChar = $requestUrl ? $requestUrl[strlen($requestUrl)-1] : '';

        // set Request Method if it isn't passed as a parameter
        if ($requestMethod === null) {
            $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        }

        foreach ($this->routes as $handler) {
            list($methods, $route, $target, $name) = $handler;

            $method_match = (stripos($methods, $requestMethod) !== false);

            // Method did not match, continue to next route.
            if (!$method_match) {
                continue;
            }

            if ($route === '*') {
                // * wildcard (matches all)
                $match = true;
            } elseif (isset($route[0]) && $route[0] === '@') {
                // @ regex delimiter
                $pattern = '`' . substr($route, 1) . '`u';
                $match = preg_match($pattern, $requestUrl, $params) === 1;
            } elseif (($position = strpos($route, '[')) === false) {
                // No params in url, do string comparison
                $match = strcmp($requestUrl, $route) === 0;
            } else {
                // Compare longest non-param string with url before moving on to regex
				// Check if last character before param is a slash, because it could be optional if param is optional too (see https://github.com/dannyvankooten/AltoRouter/issues/241)
                if (strncmp($requestUrl, $route, $position) !== 0 && ($lastRequestUrlChar === '/' || $route[$position-1] !== '/')) {
                    continue;
                }

                $regex = $this->compileRoute($route);
                $match = preg_match($regex, $requestUrl, $params) === 1;
            }

            if ($match) {
                if ($params) {
                    foreach ($params as $key => $value) {
                        if (is_numeric($key)) {
                            unset($params[$key]);
                        }
                    }
                }

                return [
                    'target' => $target,
                    'params' => $params,
                    'name' => $name
                ];
            }
        }

        return false;
    }

    /**
     * Compile the regex for a given route (EXPENSIVE)
     * @param $route
     * @return string
     */
    protected function compileRoute($route)
    {
        if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {
            $matchTypes = $this->matchTypes;
            foreach ($matches as $match) {
                list($block, $pre, $type, $param, $optional) = $match;

                if (isset($matchTypes[$type])) {
                    $type = $matchTypes[$type];
                }
                if ($pre === '.') {
                    $pre = '\.';
                }

                $optional = $optional !== '' ? '?' : null;

                //Older versions of PCRE require the 'P' in (?P<named>)
                $pattern = '(?:'
                        . ($pre !== '' ? $pre : null)
                        . '('
                        . ($param !== '' ? "?P<$param>" : null)
                        . $type
                        . ')'
                        . $optional
                        . ')'
                        . $optional;

                $route = str_replace($block, $pattern, $route);
            }
        }
        return "`^$route$`u";
    }
}



class Routes {

	protected $router;

	function __construct(){
		add_action('init', array($this, 'match_current_request') );
		add_action('wp_loaded', array($this, 'match_current_request') );
	}

	static function match_current_request() {
		global $upstatement_routes;
		if (isset($upstatement_routes->router)) {
			$route = $upstatement_routes->router->match();
			
			unset($upstatement_routes->router);
			
			if ($route && isset($route['target'])) {
				if ( isset($route['params']) ) {
					call_user_func($route['target'], $route['params']);
				} else {
					call_user_func($route['target']);
				}
			}
		}
	}

	/**
	 * @param string $route         A string to match (ex: 'myfoo')
	 * @param callable $callback    A function to run, examples:
	 *                              Routes::map('myfoo', 'my_callback_function');
	 *                              Routes::map('mybaq', array($my_class, 'method'));
	 *                              Routes::map('myqux', function() {
	 *                                  //stuff goes here
	 *                              });
	 */
	public static function map($route, $callback, $args = array()) {
		global $upstatement_routes;
		if (!isset($upstatement_routes->router)) {
			$upstatement_routes->router = new AltoRouter();
			$site_url = get_bloginfo('url');
			$site_url_parts = explode('/', $site_url);
			$site_url_parts = array_slice($site_url_parts, 3);
			$base_path = implode('/', $site_url_parts);
			if (!$base_path || strpos($route, $base_path) === 0) {
				$base_path = '/';
			} else {
				$base_path = '/' . $base_path . '/';
			}
			// Clean any double slashes that have resulted
			$base_path = str_replace( "//", "/", $base_path );
			$upstatement_routes->router->setBasePath($base_path);
		}
		$route = self::convert_route($route);
		$upstatement_routes->router->map('GET|POST|PUT|DELETE', trailingslashit($route), $callback, $args);
		$upstatement_routes->router->map('GET|POST|PUT|DELETE', untrailingslashit($route), $callback, $args);
	}

	/**
	 * @return string 					A string in a format for AltoRouter
	 *                       			ex: [:my_param]
	 */
	public static function convert_route($route_string) {
		if (strpos($route_string, '[') > -1) {
			return $route_string;
		}
		$route_string = preg_replace('/(:)\w+/', '/[$0]', $route_string);
		$route_string = str_replace('[[', '[', $route_string);
		$route_string = str_replace(']]', ']', $route_string);
		$route_string = str_replace('[/:', '[:', $route_string);
		$route_string = str_replace('//[', '/[', $route_string);
		if ( strpos($route_string, '/') === 0 ) {
			$route_string = substr($route_string, 1);
		}
		return $route_string;
	}

	/**
	 * @param string $template           A php file to load (ex: 'single.php')
	 * @param array|bool $tparams       An array of data to send to the php file. Inside the php file
	 *                                  this data can be accessed via:
	 *                                  global $params;
	 * @param int $status_code          A code for the status (ex: 200)
	 * @param WP_Query $query           Use a WP_Query object in the template file instead of
	 *                                  the default query
	 * @param int $priority		    The priority used by the "template_include" filter
	 * @return bool
	 */
	public static function load($template, $tparams = false, $query = false, $status_code = 200, $priority = 10) {
		$fullPath = is_readable($template);
		if (!$fullPath) {
			$template = locate_template($template);
		}
		if ($tparams){
			global $params;
			$params = $tparams;
		}
		if ($status_code) {
			add_filter('status_header', function($status_header, $header, $text, $protocol) use ($status_code) {
				$text = get_status_header_desc($status_code);
				$header_string = "$protocol $status_code $text";
				return $header_string;
			}, 10, 4 );
			if (404 != $status_code) {
				add_action('parse_query', function($query) {
					if ($query->is_main_query()){
						$query->is_404 = false;
					}
				},1);
				add_action('template_redirect', function(){
					global $wp_query;
					$wp_query->is_404 = false;
				},1);
			}
		}

		if ($query) {
			add_action('do_parse_request', function() use ($query) {
				global $wp;
				if ( is_callable($query) )
					$query = call_user_func($query);

				if ( is_array($query) )
					$wp->query_vars = $query;
				elseif ( !empty($query) )
					parse_str($query, $wp->query_vars);
				else
					return true; // Could not interpret query. Let WP try.

				return false;
			});
		}
		if ($template) {
			add_filter('template_include', function($t) use ($template) {
				return $template;
			}, $priority);
			return true;
		}
		return false;
	}
}

global $upstatement_routes;
$upstatement_routes = new Routes();

if (    file_exists($composer_autoload = __DIR__ . '/vendor/autoload.php')
		|| file_exists($composer_autoload = WP_CONTENT_DIR.'/vendor/autoload.php')){
  require_once($composer_autoload);
}