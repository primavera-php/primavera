<?php


namespace Primavera\Log;


use Monolog\Handler\StreamHandler;
use Monolog\Test\TestCase;

class LoggerTest extends TestCase
{
    function eraseLog() {
        if (file_exists('logging.log')) {
            unlink('logging.log');
        }
    }

    public function setUp(): void
    {
        $this->eraseLog();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }


    public function testGetLogger() {
        Logger::configure([
            'foo' => [
                'handler' => StreamHandler::class,
                'level' => \Monolog\Logger::INFO,
                'stream' => 'logging.log',
            ]
        ]);

        $logger = getLogger('foo');
        $handler = $logger->getHandlers()[0];
        $logger->info('some info');

        $this->assertInstanceOf(StreamHandler::class, $logger->getHandlers()[0]);
        $this->assertTrue($handler->isHandling(['level' => \Monolog\Logger::INFO]));
        $this->assertFalse($handler->isHandling(['level' => \Monolog\Logger::DEBUG]));

        $this->assertFileExists('logging.log');
    }
}