<?php


namespace Primavera\Container\Bean;

use Primavera\Container\Annotation\Value;
use Primavera\Container\Container\Container;
use Psr\Log\LoggerInterface;
use Primavera\Log\Logger;
use Primavera\Metadata\PropertyMetadata;
use Primavera\Container\Annotation\PostBeanProcessor;

#[PostBeanProcessor]
class ValuePostBeanProcessor extends AbstractPropertyPostBeanProcessor
{
    use ValueProcessorTrait;

    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = Logger::getLogger(__CLASS__);
    }

    public function getAnnotationClass(): string {
        return Value::class;
    }
}