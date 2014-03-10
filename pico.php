<?php
/**
 * pico is a toolkit for quickly prototyping small and simple php apps.
 *
 * @author Jesus A. Domingo <jesus.domingo@gmail.com>
 * @license MIT <http://noodlehaus.mit-license.org>
 */

/**
 * Set an http error handler, or trigger one
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
 * Perform a URL redirect
 */
function redirect($location, $code = 302) {
  header("Location: {$location}", true, intval($code));
  exit;
}

/**
 * Add a middleware routine that gets executed before each request.
 * This is more for encapsulating logic that would otherwise just be
 * floating around in the global scope.
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

  // no defined handlers for method
  if (!isset($routes[$method]))
    error(404);

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
