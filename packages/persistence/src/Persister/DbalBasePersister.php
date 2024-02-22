<?php

namespace Primavera\Persistence\Persister;

use Doctrine\DBAL\Connection;
use Primavera\Data\ObjectExtractorInterface;
use Primavera\Data\ObjectHydratorInterface;

abstract class DbalBasePersister implements PersisterInterface
{
    public function __construct(
        private Connection $connection,
        private ObjectExtractorInterface $extractor,
        private ObjectHydratorInterface $hydrator,
    ) {}

    public function save($data)
    {
        if (is_object($data)) {
            $data = $this->extractor->extract($data);
        }

        if ($this->isAutoIncrementId()) {
            if (!$data[$this->getIdColumnName()]) {
                return $this->insert($data);
            }

            return $this->update($data);
        }

        $exists = (bool) $this->connection->createQueryBuilder()
            ->select('count(1)')
            ->from($this->getTableName())
            ->where("{$this->getIdColumnName()} = :id")
            ->setParameter('id', $data[$this->getIdColumnName()])
            ->fetchOne();

        if ($exists) {
            return $this->update($data);
        }

        return $this->insert($data);
    }

    public function insert(array $data)
    {
        $this->connection->insert($this->getTableName(), $data);

        return $this->hydrate($this->fetch($this->connection->lastInsertId()));
    }

    public function update(array $data)
    {
        $this->connection->update(
            $this->getTableName(),
            $data,
            [$this->getIdColumnName() => $data[$this->getIdColumnName()]]
        );

        return $this->hydrate($this->fetch($data[$this->getIdColumnName()]));
    }

    public function delete($id)
    {
        $this->connection->delete($this->getTableName(), [$this->getIdColumnName() => $id]);
    }

    private function hydrate($data)
    {
        return $this->hydrator->hydrate($this->getEntityClassname(), $data);
    }

    private function fetch($id)
    {
        return $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->getTableName())
            ->where("{$this->getIdColumnName()} = :id")
            ->setParameter($this->getIdColumnName(), $id)
            ->fetchAssociative();
    }
}