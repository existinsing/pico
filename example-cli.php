<?php
if (count($argv) < 3)
  die("usage: php example-cli.php <request method> <request uri>\n");

// our fake server vars
$_SERVER = array(
  'SERVER_PROTOCOL' => 'HTTP/1.1',
  'REQUEST_METHOD' => $argv[1],
  'REQUEST_URI' => $argv[2]
);

require __DIR__.'/example.php';
?>
