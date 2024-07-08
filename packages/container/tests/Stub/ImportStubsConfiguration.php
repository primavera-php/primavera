<?php

namespace Primavera\Container\Test\Stub;

use Primavera\Container\Annotation\Factory;

class ImportStubsConfiguration
{
    #[Factory]
    public function importedStub(): ImportedStub
    {
        return new ImportedStub;
    }
}
