`pico()` provides fast and simple routing for your apps.

`pico()` requires `apc` or `apcu` and PHP 5.4+ to work.

```php
<?php
require __DIR__."/pico.php";

// on 404, call function
error(404, function () {
  echo "Page not found!";
});

// middleware for all requests
middleware(function ($uri) {
  $db = connect_to_db();
  // store something into context()
  context('db', $db);
});

// this gets run on /admin/blah URIs
middleware('^\/admin\/', function ($uri) {
  // ...
});

// this gets run for routes that have :name in it
middleware(':name', function ($name) {
  context('name', strtoupper($name));
});

// plain route
route('GET', '/index', function () {
  // pull db from context()
  $db = context('db');
  echo "hello, there!";
});

// route with symbol (which has a middleware tied to it)
route('GET', '/greet/:name', function ($name) {
  // get value stored by middleware
  $name = context('name');
  echo "hello, {$name}!";
});

// serve the routes
pico();
?>
```

function list:

```php
<?php
// routing
function pico();
function route($methods = null, $pattern = null, $callback = null);
function middleware($uri, $cb_or_tokens = null);
function redirect($location, $code = 302);
function error($code, $callback = null);

// data container
function context($name, $value = null);
?>
```

`pico()` is licensed under the MIT license - <http://noodlehaus.mit-license.org>
