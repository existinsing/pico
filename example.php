<?php
require __DIR__."/pico.php";

middleware(function ($uri) {
  // store a value in context()
  context('mware-1', array('id' => 1));
});

middleware(function ($uri) {
  context('mware-2', function () {
    // only gets run on access
    return array('id' => 2);
  });
});

middleware('^\/greet\/', function () {
  // gets run when uri starts with /greet/
  echo '<p>Getting ready to greet!</p>';
});

middleware(':name', function ($name) {
  // gets run when route contains :name
  echo "<p>Greeting {$name}!</p>";
});

error(404, function () {
  echo '<p>Page not found!</p>';
});

route('GET', '/index', function () {
  echo '<p>Welcome!</p>';
  $mw1 = context('mware-1');
  $mw2 = context('mware-2');
});

route('GET', '/greet/:name', function ($params) {
  echo "<h1>Hello there, {$params['name']}!</h1>";
});

pico();
