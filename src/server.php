<?php
/**
* Sample http server for event loop test.
* @author Nam Nguyen <namnvhue@gmail.com>
*/

use React\EventLoop\Factory;
use React\Socket\Server;
use React\Socket\ConnectionInterface;
require __DIR__ . '/../vendor/autoload.php';
$loop = Factory::create();
$server = new Server(isset($argv[1]) ? $argv[1] : 0, $loop, array(
    'tls' => array(
        'local_cert' => isset($argv[2]) ? $argv[2] : (__DIR__ . '/localhost.pem')
    )
));
$server->on('connection', function (ConnectionInterface $connection) {
    $connection->once('data', function () use ($connection) {        
        $body = "<html><h1>Everyday is a beautiful day!</h1></html>\r\n";
        $connection->end("HTTP/1.1 200 OK\r\nContent-Length: " . strlen($body) . "\r\nConnection: close\r\n\r\n" . $body);
    });
});
$server->on('error', 'printf');
echo 'Listening on ' . strtr($server->getAddress(), array('tcp:' => 'http:', 'tls:' => 'https:')) . PHP_EOL;
$loop->run();
