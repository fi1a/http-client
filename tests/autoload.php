<?php

declare(strict_types=1);

use Fi1a\Unit\HttpClient\Fixtures\Server\Server;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/define.php';

$server = new Server();
$server->start((int) WEB_SERVER_PORT);
register_shutdown_function(static function () use ($server) {
    $server->stop();
});
