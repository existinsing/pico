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
