`pico()` provides fast and simple routing for your apps.

`pico()` requires `apc` or `apcu` and PHP 5.4+ to work.

```php
<?php
require __DIR__."/pico.php";

middleware(function () {
  ioc('data', array('id' => 1));
  ioc('singleton', function () {
    static $inst = null;
    if (!$inst)
      $inst = new stdclass;
    return $inst;
  });
  ioc('renewable', function () {
    return new stdclass;
  });
});

bind('name', function ($name) {
  return strtoupper($name);
});

error(404, function () {
  echo '<p>Page not found!</p>';
});

route('GET', '/index', function () {
  echo '<p>Welcome!</p>';
  $mw1 = ioc('mware-1');
  $mw2 = ioc('mware-2');
});

route('GET', '/greet/:name', function ($params) {
  echo "<h1>Hello there, {$params['name']}!</h1>";
});

pico();```

function list:

```php
<?php
// routing
function pico();
function route($methods = null, $pattern = null, $callback = null);
function middleware($callback = null);
function bind($symbol, $callback);
function redirect($location, $code = 302);
function error($code, $callback = null);

// data container
function ioc($name, $value = null);
?>
```

`pico()` uses the `bind()` function created by
(Ross Masters)[http://github.com/rmasters] for dispatch.

`pico()` is licensed under the MIT license - <http://noodlehaus.mit-license.org>
