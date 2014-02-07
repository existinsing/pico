`pico` provides fast and simple routing for your apps.

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

// 404 handler
pico_error(404, function () {
  http_response_code(404);
  echo json_encode(array("code" => 404, "data" => null));
});

// middleware, just say everything's application/json
pico_middleware(function () {
  header('content-type: application/json');
});

// converts fruit id (from {fruit}) to fruit instance
pico_bind('fruit_id', function ($fruit_id) use ($DB) {
  return isset($DB[$fruit_id]) ? $DB[$fruit_id] : null;
});

// list all fruits
pico_route('GET', '/fruits', function () use ($DB) {
  echo json_encode(array("code" => 200, "data" => $DB));
});

// dump fruit info
pico_route('GET', '/fruits/{fruit_id}', function ($params) {
  if ($params['fruit_id'])
    echo json_encode(array("code" => 200, "data" => $params['fruit_id']));
  else
    pico_error(404);
});

// serve
pico_run();
```

function list:

```php
<?php
function pico_run();
function pico_route($methods = null, $pattern = null, $callback = null);
function pico_middleware($callback = null);
function pico_bind($symbol, $callback);
function pico_redirect($location, $code = 302);
function pico_error($code, $callback = null);
?>
```

`pico` uses the `pico_bind()` function which is based on code written by
[Ross Masters](http://github.com/rmasters) for
[dispatch](http://github.com/noodlehaus/dispatch).

`pico` is licensed under the MIT license - <http://noodlehaus.mit-license.org>
