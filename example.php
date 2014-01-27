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

pico();
