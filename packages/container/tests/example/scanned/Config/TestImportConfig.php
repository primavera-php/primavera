<?php

namespace ScannedTest\Config;

use Primavera\Container\Annotation\Value;
use Shared\Stub\TestImportService;
use Primavera\Container\Annotation\Bean;

class TestImportConfig
{
    /**
     * @Value("someValue")
     */
    private $value;

    /**
     * @Bean
     */
    public function importService(): TestImportService {
        return new TestImportService($this->value);
    }
}