<?php
/**
 * pico provides fast and simple routing for your apps.
 *
 * @author Jesus A. Domingo <jesus.domingo@gmail.com>
 * @license MIT <http://noodlehaus.mit-license.org>
 */

/**
 * Set an http error handler, or trigger one
 *
 * @param int $code error code to map to or to trigger
 * @param callable $callback error handler for the code
 *
 * @return void
 */
function error($code, $callback = null) {

  static $handlers = array();

  $code = intval($code);

  if ($callback !== null)
    return ($handlers[$code] = $callback);

  header("{$_SERVER['SERVER_PROTOCOL']} {$code} Error");

  if (isset($handlers[$code]))
    exit(call_user_func($handlers[$code]));

  exit("{$code} - Error");
}

/**
 * Perform URL redirect
 *
 * @param string $location url to redirect to
 * @param int $code http status code (defaults to 302)
 *
 * @return void
 */
function redirect($location, $code = 302) {
  header("Location: {$location}", true, intval($code));
  exit;
}

/**
 * Add a middleware routine that gets executed before each request.
 * This is more for encapsulating logic that would otherwise just be
 * floating around in the global scope.
 *
 * @param callable $callback routine to execute
 *
 * @return void
 */
function middleware($callback = null) {

  static $stack = array();

  // mapping call
  if (is_callable($callback)) {
    $stack[] = $callback;
    return;
  }

  // internal api for running all middleware
  foreach ($stack as $cb)
    call_user_func($cb);
}

/**
 * Map a callback against a method-route pair.
 *
 * @param string|array $methods http methods to map to
 * @param string $pattern route pattern to map to
 * @param callable $callback route handler
 *
 * @return void
 */
function route($methods = null, $pattern = null, $callback = null) {

  static $routes = array();

  // internal api, for getting all defined routes
  if (func_num_args() == 0)
    return $routes;

  // create route regexp
  $methods = array_map('strtoupper', (array) $methods);
  $pattern = '/'.trim($pattern, '/');
  $regexpr = '@^'.preg_replace('@\{(\w+)\}@', '(?<\1>[^/]+)', $pattern).'$@';

  // map it for every method supported
  foreach ($methods as $method)
    $routes[$method][$regexpr] = $callback;
}

/**
 * Request dispatcher
 *
 * @return void
 */
function run() {

  $uri = '/'.trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

  $method = strtoupper($_SERVER['REQUEST_METHOD']);

  // only check for overrides when method is POST
  if ($method == 'POST') {
    $method = isset($_POST['_method']) ? $_POST['_method'] : $method;
    if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']))
      $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
  }

  $routes = route();

  // we give a bad request error for unsupported method
  if (!isset($routes[$method]))
    error(400);

  $callback = null;
  foreach ($routes[$method] as $regexpr => $handler) {
    if (!preg_match($regexpr, $uri, $params))
      continue;
    $callback = $handler;
    break;
  }

  if ($callback == null)
    error(404);

  // we only run middleware if we have a match found
  middleware();

  // create argument hash for handler
  $tokens = array_filter(array_keys($params), 'is_string');
  $params = array_map('urldecode', array_intersect_key(
    $params,
    array_flip($tokens)
  ));

  call_user_func_array($callback, array_values($params));
}

/**
 * Simplistic ioc container. If called with just the service name,
 * the instance (if available or can be loaded) is returned. Passing
 * a loader routine registers that routine to the service.
 *
 * @param string $name name for the service to register
 * @param callable $loader optional, lazy loader for the service
 * @param boolean $shared defaults to false, if instance is shared
 *
 * @return mixed whatever the loader generates
 */
function ioc($name, $loader = null, $shared = false) {

  static $loaders = array();
  static $objects = array();

  // fetch logic
  if (func_num_args() == 1) {

    // locate the loader
    list($loader, $shared) = (
      isset($loaders[$name]) ?
      $loaders[$name] :
      array(null, null)
    );

    // if no loader, then give back null but issue a warning
    if (!$loader) {
      trigger_error(
        "ioc() service [{$name}] is not registered",
        E_USER_WARNING
      );
      return null;
    }

    // not a shared instance
    if (!$shared)
      return $loader();

    // shared instance, check and return, or create and return
    if (!isset($objects[$name]))
      $objects[$name] = $loader();

    return $objects[$name];
  }

  // set logic
  $loaders[$name] = array($loader, $shared);
}
