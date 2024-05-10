<?php
echo "ok";
require dirname(__DIR__).'/vendor/autoload.php';
require __DIR__.'/src/Chat.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use zhabbler_chat\Chat;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8000
);

$server->run();