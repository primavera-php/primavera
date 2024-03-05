<?php

namespace Shared\Stub;

use ScannedTest\Interfaces\InterfaceForBuilding;

class BuildingComponent implements InterfaceForBuilding
{
    public function __construct(
        public BarComponent $barComponent,
    ) {}

    public function isBuilt(): bool
    {
        return true;
    }
}
