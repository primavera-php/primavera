<?php

namespace Primavera\Persistence\Database;

interface TableInterface
{
    public function getTableName(): string;

    public function getIdColumnName(): string;

    public function getEntityClassname(): string;
}