`pico()` provides fast and simple routing for your apps.

```php
<?php
require __DIR__."/pico.php";

// gets run for every request
middleware(function () {
  some_startup_stuff();
});

// gets run on routes with {name} symbol
bind('name', function ($name) {
  return strtoupper($name);
});

// our 404 callback routine
error(404, function () {
  echo '<p>Page not found!</p>';
});

// plain route
route('GET', '/index', function () {
  echo '<p>Welcome!</p>';
});

// route with symbol
route('GET', '/greet/{name}', function ($params) {
  // $params contains all symbol values
  echo "<h1>Hello there, {$params['name']}!</h1>";
});

pico();
```

function list:

```php
<?php
function pico();
function route($methods = null, $pattern = null, $callback = null);
function middleware($callback = null);
function bind($symbol, $callback);
function redirect($location, $code = 302);
function error($code, $callback = null);
?>
```

`pico()` uses the `bind()` function created by
[Ross Masters](http://github.com/rmasters) for dispatch.

`pico()` is authored by [Jesus A. Domingo](http://github.com/noodlehaus).

`pico()` is licensed under the MIT license - <http://noodlehaus.mit-license.org>
