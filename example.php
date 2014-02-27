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
error(404, function () {
  header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
  echo json_encode(array("code" => 404, "data" => null));
});

// list all fruits
route('GET', '/fruits', function () use ($DB) {
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
