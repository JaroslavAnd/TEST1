<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * Пошук та фільтрація по кожному полю таблиці clients
     */
    public function getFilteredQueryBuilder(array $filters = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        if (!empty($filters['id'])) {
            $qb->andWhere('c.id = :id')
               ->setParameter('id', (int)$filters['id']);
        }

        $firstName = $filters['firstName'] ?? $filters['first_name'] ?? null;
        if ($firstName !== null && $firstName !== '') {
            $qb->andWhere('c.firstName LIKE :firstName')
               ->setParameter('firstName', '%' . $firstName . '%');
        }

        $lastName = $filters['lastName'] ?? $filters['last_name'] ?? null;
        if ($lastName !== null && $lastName !== '') {
            $qb->andWhere('c.lastName LIKE :lastName')
               ->setParameter('lastName', '%' . $lastName . '%');
        }

        if (!empty($filters['email'])) {
            $qb->andWhere('c.email LIKE :email')
               ->setParameter('email', '%' . $filters['email'] . '%');
        }

        if (!empty($filters['phone'])) {
            $qb->andWhere('c.phone LIKE :phone')
               ->setParameter('phone', '%' . $filters['phone'] . '%');
        }

        $taxId = $filters['taxId'] ?? $filters['tax_id'] ?? null;
        if ($taxId !== null && $taxId !== '') {
            $qb->andWhere('c.taxId LIKE :taxId')
               ->setParameter('taxId', '%' . $taxId . '%');
        }

        $createdFrom = $filters['createdFrom'] ?? $filters['created_from'] ?? null;
        if ($createdFrom) {
            $qb->andWhere('c.createdAt >= :createdFrom')
               ->setParameter('createdFrom', new \DateTimeImmutable($createdFrom . ' 00:00:00'));
        }

        $createdTo = $filters['createdTo'] ?? $filters['created_to'] ?? null;
        if ($createdTo) {
            $qb->andWhere('c.createdAt <= :createdTo')
               ->setParameter('createdTo', new \DateTimeImmutable($createdTo . ' 23:59:59'));
        }

        $qb->orderBy('c.id', 'DESC');

        return $qb;
    }
}
