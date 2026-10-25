<?php

namespace App\Repository;

use App\Entity\CreditType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CreditType>
 */
class CreditTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreditType::class);
    }

    /**
     * Пошук та фільтрація по кожному полю таблиці credit_types
     */
    public function getFilteredQueryBuilder(array $filters = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ct');

        if (!empty($filters['id'])) {
            $qb->andWhere('ct.id = :id')
               ->setParameter('id', (int)$filters['id']);
        }

        if (!empty($filters['name'])) {
            $qb->andWhere('ct.name LIKE :name')
               ->setParameter('name', '%' . $filters['name'] . '%');
        }

        $interestRate = $filters['interestRate'] ?? $filters['interest_rate'] ?? null;
        if ($interestRate !== null && $interestRate !== '') {
            $qb->andWhere('ct.interestRate = :interestRate')
               ->setParameter('interestRate', (string)$interestRate);
        }

        $minInterestRate = $filters['minInterestRate'] ?? $filters['min_interest_rate'] ?? null;
        if ($minInterestRate !== null && $minInterestRate !== '') {
            $qb->andWhere('ct.interestRate >= :minInterestRate')
               ->setParameter('minInterestRate', (string)$minInterestRate);
        }

        $maxInterestRate = $filters['maxInterestRate'] ?? $filters['max_interest_rate'] ?? null;
        if ($maxInterestRate !== null && $maxInterestRate !== '') {
            $qb->andWhere('ct.interestRate <= :maxInterestRate')
               ->setParameter('maxInterestRate', (string)$maxInterestRate);
        }

        $minAmount = $filters['minAmount'] ?? $filters['min_amount'] ?? null;
        if ($minAmount !== null && $minAmount !== '') {
            $qb->andWhere('ct.minAmount >= :minAmount')
               ->setParameter('minAmount', (string)$minAmount);
        }

        $maxAmount = $filters['maxAmount'] ?? $filters['max_amount'] ?? null;
        if ($maxAmount !== null && $maxAmount !== '') {
            $qb->andWhere('ct.maxAmount <= :maxAmount')
               ->setParameter('maxAmount', (string)$maxAmount);
        }

        $termMonths = $filters['termMonths'] ?? $filters['term_months'] ?? null;
        if ($termMonths !== null && $termMonths !== '') {
            $qb->andWhere('ct.termMonths = :termMonths')
               ->setParameter('termMonths', (int)$termMonths);
        }

        $qb->orderBy('ct.id', 'ASC');

        return $qb;
    }
}
