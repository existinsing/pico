`pico` is a bare bones router for your php app. written primarily
form personal use.

```php
<?php
function run();
function route($methods, $pattern, $callback);
function redirect($location, $code = 302);
function error($code, $callback = null);
function middleware($callback);
function ioc($name, $loader = null, $shared = false);
?>
```

how to use it:

```php
<?php
require __DIR__."/pico.php";

// our mock database
$DB = array(
  'fruit-1' => array(
    'color' => 'green',
    'name' => 'apple'
  ),
  'fruit-2' => array(
    'color' => 'yellow',
    'name' => 'banana'
  ),
  'fruit-3' => array(
    'color' => 'red',
    'name' => 'strawberry'
  ),
  'fruit-4' => array(
    'color' => 'orange',
    'name' => 'orange'
  )
);

// middleware, routine that runs for every request
middleware(function () {
  ioc('db', function () {
    // put a db loader into our ioc, for lazy loading
    return new mongoclient('mongodb://localhost');
  }, $shared = true);
});

// 404 handler
error(404, function () {
  header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
  echo json_encode(array("code" => 404, "data" => null));
});

// list all fruits
route('GET', '/fruits', function () use ($DB) {
  // how you would get something from the ioc()
  $db = ioc('db');
  echo json_encode(array("code" => 200, "data" => $DB));
});

// dump fruit info
route('GET', '/fruits/{fruit_id}', function ($fruit_id) use ($DB) {
  if (!isset($DB[$fruit_id]))
    error(404);
  echo json_encode(array("code" => 200, "data" => $DB[$fruit_id]));
});

// serve
run();
```

Released under the MIT license - <http://noodlehaus.mit-license.org>
