<?php
require __DIR__."/pico.php";

// our mock database
$DB = [
  'fruit-1' => [
    'color' => 'green',
    'name' => 'apple'
  ],
  'fruit-2' => [
    'color' => 'yellow',
    'name' => 'banana'
  ],
  'fruit-3' => [
    'color' => 'red',
    'name' => 'strawberry'
  ],
  'fruit-4' => [
    'color' => 'orange',
    'name' => 'orange'
  ]
];

// 404 handler
error(404, function () {
  http_response_code(404);
  echo json_encode(["code" => 404, "data" => null]);
});

// middleware, just say everything's application/json
middleware(function () {
  header('content-type: application/json');
});

// converts fruit id (from {fruit}) to fruit instance
bind('fruit_id', function ($fruit_id) use ($DB) {
  return isset($DB[$fruit_id]) ? $DB[$fruit_id] : null;
});

// list all fruits
route('GET', '/fruits', function () use ($DB) {
  echo json_encode(["code" => 200, "data" => $DB]);
});

// dump fruit info
route('GET', '/fruits/{fruit_id}', function ($params) {
  if ($params['fruit_id'])
    echo json_encode(["code" => 200, "data" => $params['fruit_id']]);
  else
    error(404);
});

// serve
pico();
