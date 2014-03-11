# pico

`pico` is a toolkit for creating small php apps.

## function list

```php
<?php
function run();
function route($methods, $pattern, $callback);
function redirect($location, $code = 302);
function error($code, $handler = null);
function middleware($callback);
function ioc($name, $loader = null, $shared = false);
?>
```

## api documentation

### run()

`run()`

Dispatches the current HTTP request and matches it against the our routes.
If no route handler exists for the requested URI, a `404` is emitted.

### route()

`route($methods, $pattern, $callback)`

Maps a handler against the method(s) and route pattern pair. `$methods` can
be an array of HTTP methods that you want the route to respond to.

Route patterns can contain symbols of the form `:symbol`. Values matched by
these symbols are then passed as arguments to your route handler.

Your route handler has to be a `callable`.

```php
<?php
// simple route
route('GET', '/index', 'index_function');

// route with symbols
route('GET', '/users/:username', function ($username) {
  // $username is taken from :username
});

// create route defaults
route('GET', '/items(/:page)?', function ($page = 1) {});
```

### redirect()

`redirect($location, $code = 302)`

Flushes out an HTTP redirect header using the value from `$location` and the
optional `$code`. The default status code for this is `302`.

```php
<?php
// redirect with default code
redirect('/index');

// override the default code
redirect('/index', 301);
```

### error()

`error($code, $handler = null)`

If called with a callable value for `$handler`, this function maps that
callable against error code `$code`. If that HTTP error code is raised,
then that handler will be called. Only one handler can be mapped against
a `$code`.

If called with just the `$code` argument, this raises the HTTP error and
invokes the handler for it.

```php
<?php
// create a 404 handler
error(404, function () {
  die("Sorry, we can't find your page.");
});

// trigger a 404, which will call our handler
error(404);
```

### middleware()

`middleware($callback)`

Sets up a routine that gets run everytime a request has a matching handler.
This exists mainly to provide a contained scope for doing start up tasks.

```php
<?php
// this gets executed if there's a matching handler for the request
middleware(function () {
  ioc('db', function () {
    return new mongoclient('mongodb://localhost');
  }, true);
});
```

### ioc()

`ioc($name, $loader = null, $shared = false)`

This is a simplistic object container, factory, or whatever this should be
called.

`$name` is the name of the object instance.

`$loader` is the routine that generates the instance.

`$shared` tells whether `$loader` is invoked everytime `$name` is requested
from the container, or only invoked once and the result is cached.
The default value for this is `false`.

Calling this with just the `$name` argument performs a fetch of the instance.

```php
<?php
// shared
ioc('always_one', function () {
  static $one = 1;
  return $one++;
}, true);

// not shared
ioc('next', function () {
  static $id = 1;
  return $id++;
});

assert(ioc('always_one') == ioc('always_one'));
assert(ioc('next') == ioc('next') + 1);
```

## license

Released under the MIT license - <http://noodlehaus.mit-license.org>
