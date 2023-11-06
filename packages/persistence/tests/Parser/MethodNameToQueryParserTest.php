<?php

namespace Primavera\PersistenceTests\Parser;

use Primavera\Metadata\Factory\MetadataFactoryFactory;
use Primavera\Persistence\Parser\DbalExpressionFactory;
use Primavera\Persistence\Parser\MethodNameToQueryParser;
use Primavera\Persistence\Parser\SimpleSqlParser;
use Primavera\PersistenceTests\DbTestCase;
use Primavera\Persistence\Annotation\GroupBy;
use Primavera\Persistence\Annotation\OrderBy;
use Primavera\Persistence\Annotation\Limit;

class MethodNameToQueryParserTest extends DbTestCase
{
    private $metadataReader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->metadataReader = (new MetadataFactoryFactory())->createAnnotationMetadataFactory();
    }

    /**
     * @dataProvider provider
     */
    public function testShouldParseMethodNameToDbalExpressions($method, $expectedExpressions)
    {
        $metadata = $this->metadataReader->getMetadataForClass(LexTestInterface::class);

        $methodMetadata = $metadata->getMethodMetadata()[$method];

        $parser = new MethodNameToQueryParser(new DbalExpressionFactory($this->connection, new SimpleSqlParser($this->connection)));

        $expressions = $parser->parse($methodMetadata);

        $this->assertEquals($expectedExpressions, $expressions);
    }

    public function provider()
    {
        return [
            ['findByUserAndEmailOrId', [
                ['operation' => 'find'],
                ['and' => 'User = :user'],
                ['and' => 'Email = :email'],
                ['or' => 'Id = :id'],
            ]],
            ['findByAgeGteAndCustomerTypeIn', [
                ['operation' => 'find'],
                ['and' => 'Age >= :age'],
                ['and' => 'CustomerType IN (:types)'],
                ['orderby' => ['Gender' => 'asc']],
                ['groupby' => ['Gender']],
                ['limit' => ':limit'],
            ]],
        ];
    }
}

interface LexTestInterface
{
    public function findByUserAndEmailOrId($user, $email, $id);

    /**
     * @GroupBy({"Gender"})
     * @OrderBy({"Gender"})
     * @Limit(":limit")
     */
    public function findByAgeGteAndCustomerTypeIn($age, $types, $limit);

}