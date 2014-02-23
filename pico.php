<?php
/**
 * pico provides fast and simple routing for your apps.
 *
 * @author Jesus A. Domingo <jesus.domingo@gmail.com>
 * @license MIT <http://noodlehaus.mit-license.org>
 */

namespace noodlehaus\pico;

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
 * Add a middleware routine that gets executed
 * before each request.
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

  // run generic hooks
  foreach ($stack as $cb)
    call_user_func($cb);
}

/**
 * Bind transform callbacks to route symbol values
 */
function bind($symbol, $callback = null) {

  // callback store and symbol cache
  static $bindings = array();
  static $symcache = array();

  // bind callback to symbol
  if (is_callable($callback)) {
    $bindings[$symbol] = $callback;
    return;
  }

  // string symbol, look it up
  if (!is_array($symbol))
    return isset($symcache[$symbol]) ? $symcache[$symbol] : null;

  // called with hash, exec (internal API)
  $values = array();
  foreach ($symbol as $sym => $val) {
    if (isset($bindings[$sym]))
      $symcache[$sym] = $val = call_user_func($bindings[$sym], $val);
    $values[$sym] = $val;
  }

  return $values;
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
function route($methods, $pattern, $callback) {

  static $routes = array();

  // internal API
  if ($methods == null && $pattern == null && $callback == null)
    return $routes;

  $methods = array_map('strtoupper', (array) $methods);
  $pattern = '/'.trim($pattern, '/');
  $regexpr = '@^'.preg_replace('@\{(\w+)\}@', '(?<\1>[^/]+)', $pattern).'$@';

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

  if ($method == 'POST')
    $method = isset($_POST['_method']) ? $_POST['_method'] : $method;

  if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']))
    $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];

  $routes = route(null, null, null);

  if (!isset($routes[$method]))
    error(404);

  $callback = null;
  foreach ($routes[$method] as $regexpr => $handler) {
    if (!preg_match($regexpr, $uri, $params))
      continue;
    $callback = $handler;
    break;
  }

  middleware($uri, $params);

  if ($callback == null)
    error(404);

  $tokens = array_filter(array_keys($params), 'is_string');
  $params = array_map('urldecode', array_intersect_key(
    $params,
    array_flip($tokens)
  ));

  call_user_func($callback, bind($params));
}
