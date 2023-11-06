<?php

namespace Primavera\Log;

use Monolog\Handler\FormattableHandlerInterface;

class Logger
{
    private static array $loggers = [];

    public static function getLogger(string $channel): \Monolog\Logger {
        return self::$loggers[$channel] ?? self::$loggers[$channel] = new \Monolog\Logger($channel);
    }

    public static function configure(array $configs) {
        foreach ($configs as $name => $config) {
            $logger = self::getLogger($config['channel'] ?? $name);

            $handlerClass = $config['handler'];
            $processor = $config['processor'] ?? null;
            $formatter = $config['formatter'] ?? null;

            $reflection = new \ReflectionClass($handlerClass);
            $ctorParams = $reflection->getConstructor()->getParameters();

            $ctorArgs = [];

            foreach ($ctorParams as $param) {
                if (isset($config[$param->name])) {
                    $ctorArgs[$param->name] = $config[$param->name];
                }
            }

            $handler = $reflection->newInstanceArgs($ctorArgs);

            if ($formatter && $handler instanceof FormattableHandlerInterface) {
                $handler->setFormatter(new $formatter());
            }

            $logger->pushHandler($handler);

            if ($processor) {
                $logger->pushProcessor($processor);
            }
        }
    }
}

function getLogger(string $chanel) {
    return Logger::getLogger($chanel);
}