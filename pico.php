<?php
/**
 * pico provides fast and simple routing for your apps.
 *
 * @author Jesus A. Domingo <jesus.domingo@gmail.com>
 * @license MIT <http://noodlehaus.mit-license.org>
 */

/**
 * Simplistic value/DI container
 */
function context($name, $value = null) {

  static $cache = array();

  if ($value == null) {

    if (!isset($cache[$name]))
      return null;

    if (is_callable($cache[$name]))
      return call_user_func($cache[$name]);

    return $cache[$name];
  }

  $cache[$name] = $value;
}

/**
 * Set an http error handler, or trigger one
 */
function error($code, $callback = null) {

  static $handlers = array();

  $code = intval($code);

  if ($callback == null) {
    http_response_code($code);
    if (isset($handlers[$code]))
      call_user_func($handlers[$code]);
    else
      echo "{$code} - Application Error";
    exit;
  }

  $handlers[$code] = $callback;
}

/**
 * URL redirect
 */
function redirect($location, $code = 302) {
  header("Location: {$location}", true, intval($code));
  exit;
}

/**
 * Route symbol, regex, or generic filters
 */
function middleware($uri, $cb_or_tokens = null) {

  static $regex_cb = array('.*' => array()),
         $param_cb = array();

  // a catch-all mapping call
  if (is_callable($uri)) {
    $regex_cb['.*'][] = $uri;
    return;
  }

  // this is a mapping call
  if (is_callable($cb_or_tokens)) {
    if ($uri[0] == ':')
      $param_cb[substr($uri, 1)][] = $cb_or_tokens;
    else
      $regex_cb[$uri][] = $cb_or_tokens;
    return;
  }

  // get symbols that have hooks
  $matches = array_intersect(
    array_keys($param_cb),
    array_keys($cb_or_tokens)
  );

  // run generic hooks
  foreach ($regex_cb['.*'] as $cb)
    call_user_func($cb, $uri);
  unset($regex_cb['.*']);

  // run regex hooks
  foreach ($regex_cb as $regex => $cb_list) {
    if (preg_match("@{$regex}@", $uri)) {
      foreach ($cb_list as $cb)
        call_user_func($cb, $uri);
    }
  }

  // run hooks for matching symbols
  if ($matches) {
    foreach ($matches as $match) {
      foreach ($param_cb[$match] as $func) {
        call_user_func(
          $func,
          $cb_or_tokens[$match]
        );
      }
    }
  }

}

/**
 * Route mapping function
 */
function route($methods, $pattern, $callback) {

  static $routes = array();

  // internal API
  if ($methods == null && $pattern == null && $callback == null)
    return $routes;

  $methods = array_map('strtoupper', (array) $methods);
  $pattern = '/'.trim($pattern, '/');
  $regexpr = apc_fetch("regexpr:{$pattern}");

  if (!$regexpr) {
    $regexpr = '@^'.preg_replace('@:(\w+)@', '(?<\1>[^/]+)', $pattern).'$@';
    apc_store("regexpr:{$pattern}", $regexpr);
  }

  foreach ($methods as $method)
    $routes[$method][$regexpr] = $callback;
}

/**
 * Request dispatcher
 */
function pico() {

  $uri = '/'.trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

  $method = strtoupper($_SERVER['REQUEST_METHOD']);
  if ($method == 'POST') {
    if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']))
      $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
    else
      $method = isset($_POST['_method']) ? $_POST['_method'] : $method;
  }

  $routes = route(null, null, null);
  $callback = null;

  $regexpr = apc_fetch("invoke:{$method}:{$uri}");
  if ($regexpr) {
    $callback = $routes[$method][$regexpr];
    preg_match($regexpr, $uri, $params);
  } else {
    foreach ($routes[$method] as $regexpr => $handler) {
      if (preg_match($regexpr, $uri, $params)) {
        apc_store("invoke:{$method}:{$uri}", $regexpr);
        $callback = $handler;
        break;
      }
    }
  }

  if ($callback == null)
    error(404);

  $tokens = array_filter(array_keys($params), 'is_string');
  $params = array_map('urldecode', array_intersect_key(
    $params,
    array_flip($tokens)
  ));

  middleware($uri, $params);
  call_user_func($callback, $params);
}
?>
