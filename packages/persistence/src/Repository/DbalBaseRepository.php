<?php

namespace Primavera\Persistence\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Primavera\Container\Annotation\Injects;
use Primavera\Data\ObjectHydratorInterface;
use Primavera\Metadata\ClassMetadataInterface;
use Primavera\Metadata\Factory\MetadataFactoryInterface;
use Primavera\Persistence\Parser\ParserInterface;

/**
 * @template T
 */
abstract class DbalBaseRepository implements RepositoryInterface
{
    private ClassMetadataInterface $classMetadata;

    protected $interface;

    public function __construct(
        protected Connection $connection,
        protected ObjectHydratorInterface $hydrator,
        protected MetadataFactoryInterface $metadataFactory,
        #[Injects('methodNameToQueryParser')]
        protected ParserInterface $parser,
    ) {
        $this->classMetadata = $metadataFactory->getMetadataForClass($this->interface);
    }

    /**
     * @return \Traversable<int, T>
     */
    public function find(...$criteria): \Traversable
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->getTableName());

        if ($criteria) {
            $qb->where(...$criteria);
        }

        return $this->fetchMany($qb);
    }

    /**
     * @return T
     */
    public function findById($id)
    {
        return $this->fetchOne(
            $this->connection->createQueryBuilder()
                ->select('*')
                ->from($this->getTableName())
                ->where("{$this->getIdColumnName()} = :id")
                ->setParameter('id', $id)
        );
    }

    /**
     * @return T
     */
    public function findOne(...$criteria)
    {
        return $this->fetchOne(
            $this->connection->createQueryBuilder()
                ->select('*')
                ->from($this->getTableName())
                ->where(...$criteria)
        );
    }

    protected function fetchMany(QueryBuilder $qb): \Generator
    {
        $result = $qb->executeQuery();

        while ($row = $result->fetchAssociative()) {
            yield $this->hydrator->hydrate($this->getEntityClassname(), $row);
        }
    }

    protected function fetchOne(QueryBuilder $qb): ?object
    {
        $result = $this->fetchMany($qb);

        return $result->current() ?: null;
    }

    protected function findByMethodExpressions(string $methodName, array $params)
    {
        $mm = $this->classMetadata->getMethodMetadata($methodName);

        $exprs = $this->parser->parse($mm);
        $operation = array_shift($exprs)['operation'];

        return $this->findByExpressions($operation, $exprs, $params);
    }

    /**
     * @param array $expressions
     * @return \Traversable|object
     */
    protected function findByExpressions(string $operation, array $expressions, array $params)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName());

        foreach ($expressions as $expression) {
            [$logical, $expr] = [array_keys($expression)[0], array_values($expression)[0]];

            switch ($logical) {
                case 'And':
                default:
                    $qb->andWhere($expr);
                    break;
                case 'Or':
                    $qb->orWhere($expr);
                    break;
                case 'groupby':
                    $qb->addGroupBy($expr);
                    break;
                case 'orderby':
                    $qb->addOrderBy($expr);
                    break;
                case 'limit':
                    $qb->setMaxResults($expr);
                    break;
            }
        }

        $qb->setParameters($params);

        switch ($operation) {
            case 'find':
                return $this->fetchMany($qb);
            case 'findOne':
                return $this->fetchOne($qb);
            default:
                throw new \InvalidArgumentException("unknown operation {$operation}");
        }
    }


}