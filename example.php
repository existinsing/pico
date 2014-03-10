<?php
require __DIR__."/pico.php";

// this routine gets run for every request that has a handler
middleware(function () {
  // register a shared object/service
  ioc('fruits', function () {
    return array(
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
  }, $shared = true);
});

// create a custom 404 handler
error(404, function () {
  echo json_encode(array("code" => 404, "data" => null));
});

// list all fruits
route('GET', '/fruits', function () {
  $fruits = ioc('fruits');
  echo json_encode(array("code" => 200, "data" => $fruits));
});

// dump fruit info
route('GET', '/fruits/{fruit_id}', function ($fruit_id) {
  $fruits = ioc('fruits');
  if (!isset($fruits[$fruit_id]))
    error(404);
  echo json_encode(array("code" => 200, "data" => $fruits[$fruit_id]));
});

// serve
run();
