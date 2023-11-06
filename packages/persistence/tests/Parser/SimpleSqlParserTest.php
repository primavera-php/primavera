<?php

namespace Primavera\PersistenceTests\Parser;

use Primavera\Persistence\Parser\SimpleSqlParser;
use Primavera\PersistenceTests\DbTestCase;

class SimpleSqlParserTest extends DbTestCase
{
    public function testShouldParseSelectSql()
    {
        $parser = new SimpleSqlParser($this->connection);

        $exprs = $parser->parse(
            'SELECT * FROM users WHERE name = :name AND email is not :email AND id is :id OR name LIKE :alias GROUP BY email ORDER BY name DESC, email LIMIT 10'
        );

        $this->assertEquals([
            ['select' => ['*']],
            ['from' => 'users'],
            ['and' => 'name = :name'],
            ['and' => 'email <> :email'],
            ['and' => 'id = :id'],
            ['or' => 'name LIKE :alias'],
            ['groupby' => ['email']],
            ['orderby' => ['name' => 'desc', 'email' => 'asc']],
            ['limit' => '10'],
        ], $exprs);
    }

    public function testShouldParsePersistSql()
    {
        $parser = new SimpleSqlParser($this->connection);

        $exprs = $parser->parse('INSERT users SET name = :name, email = :email');

        $this->assertEquals([
            ['insert' => 'users'],
            ['set' => ['name = :name', 'email = :email']],
        ], $exprs);
    }
}