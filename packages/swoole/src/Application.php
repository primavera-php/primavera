<?php

namespace Primavera\Swoole;

use Primavera\Framework\Application as BaseApplication;
use Slim\App;
use OpenSwoole\Http\Server;

class Application extends BaseApplication
{
    public function run()
    {
        $container = $this->getContainer();

        $server = new Server($container->get('swoole.server.host'), $container->get('swoole.server.port'));

        $server->on('start', fn($s) => printf("Swoole server running"));

        $server->setHandler($container->get(App::class));

        $server->start();
    }
}