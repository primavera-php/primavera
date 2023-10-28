<?php

namespace Vox\PersistenceTests\Parser;

use Vox\Metadata\Factory\MetadataFactoryFactory;
use Vox\Persistence\Parser\DbalExpressionFactory;
use Vox\Persistence\Parser\MethodNameToQueryParser;
use Vox\Persistence\Parser\SimpleSqlParser;
use Vox\PersistenceTests\DbTestCase;
use Vox\Persistence\Annotation\GroupBy;
use Vox\Persistence\Annotation\OrderBy;
use Vox\Persistence\Annotation\Limit;

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

        $methodMetadata = $metadata->methodMetadata[$method];

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