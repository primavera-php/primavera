<?php

declare(strict_types=1);

namespace Primavera\ExampleApp\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Primavera\Doctrine\Annotation\InjectRepository;
use Primavera\ExampleApp\Entity\Phone;
use Primavera\ExampleApp\Entity\User;

/**
 * @extends EntityRepository<User>
 */
class UserRepository extends EntityRepository
{
    /**
     * @param EntityRepository<Phone> $phoneRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        ClassMetadata $class,
        #[InjectRepository(Phone::class)]
        private EntityRepository $phoneRepository,
    ) {
        parent::__construct($em, $class);
    }

    public function findUserPhone(int $userId): Phone
    {
        return $this->phoneRepository->findOneBy(['user' => $userId]);
    }

    public function save(User $user)
    {
        if ($this->getEntityManager()->getUnitOfWork()->getEntityState($user) === UnitOfWork::STATE_NEW) {
            $this->getEntityManager()->persist($user);
        }

        $this->getEntityManager()->flush();
    }

    public function delete(int $userId)
    {
        $em = $this->getEntityManager();

        $em->remove($em->getReference(User::class, $userId));
        $em->flush();
    }
}
