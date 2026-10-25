<?php

namespace App\Repository;

use App\Entity\Credit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Credit>
 */
class CreditRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Credit::class);
    }

    /**
     * Пошук та фільтрація по кожному полю таблиці credits
     */
    public function getFilteredQueryBuilder(array $filters = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('cr')
                   ->leftJoin('cr.client', 'c')
                   ->leftJoin('cr.bankAccount', 'b')
                   ->leftJoin('cr.creditType', 'ct')
                   ->addSelect('c', 'b', 'ct');

        if (!empty($filters['id'])) {
            $qb->andWhere('cr.id = :id')
               ->setParameter('id', (int)$filters['id']);
        }

        $clientId = $filters['clientId'] ?? $filters['client_id'] ?? null;
        if ($clientId) {
            $qb->andWhere('cr.client = :clientId')
               ->setParameter('clientId', (int)$clientId);
        }

        $bankAccountId = $filters['bankAccountId'] ?? $filters['bank_account_id'] ?? null;
        if ($bankAccountId) {
            $qb->andWhere('cr.bankAccount = :bankAccountId')
               ->setParameter('bankAccountId', (int)$bankAccountId);
        }

        $creditTypeId = $filters['creditTypeId'] ?? $filters['credit_type_id'] ?? null;
        if ($creditTypeId) {
            $qb->andWhere('cr.creditType = :creditTypeId')
               ->setParameter('creditTypeId', (int)$creditTypeId);
        }

        if (isset($filters['amount']) && $filters['amount'] !== '') {
            $qb->andWhere('cr.amount = :amount')
               ->setParameter('amount', (string)$filters['amount']);
        }

        $minAmount = $filters['minAmount'] ?? $filters['min_amount'] ?? null;
        if ($minAmount !== null && $minAmount !== '') {
            $qb->andWhere('cr.amount >= :minAmount')
               ->setParameter('minAmount', (string)$minAmount);
        }

        $maxAmount = $filters['maxAmount'] ?? $filters['max_amount'] ?? null;
        if ($maxAmount !== null && $maxAmount !== '') {
            $qb->andWhere('cr.amount <= :maxAmount')
               ->setParameter('maxAmount', (string)$maxAmount);
        }

        $minRemaining = $filters['minRemainingAmount'] ?? $filters['min_remaining_amount'] ?? null;
        if ($minRemaining !== null && $minRemaining !== '') {
            $qb->andWhere('cr.remainingAmount >= :minRemaining')
               ->setParameter('minRemaining', (string)$minRemaining);
        }

        $maxRemaining = $filters['maxRemainingAmount'] ?? $filters['max_remaining_amount'] ?? null;
        if ($maxRemaining !== null && $maxRemaining !== '') {
            $qb->andWhere('cr.remainingAmount <= :maxRemaining')
               ->setParameter('maxRemaining', (string)$maxRemaining);
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('cr.status = :status')
               ->setParameter('status', strtolower($filters['status']));
        }

        $startDate = $filters['startDate'] ?? $filters['start_date'] ?? null;
        if ($startDate) {
            $qb->andWhere('cr.startDate >= :startDate')
               ->setParameter('startDate', new \DateTime($startDate));
        }

        $endDate = $filters['endDate'] ?? $filters['end_date'] ?? null;
        if ($endDate) {
            $qb->andWhere('cr.endDate <= :endDate')
               ->setParameter('endDate', new \DateTime($endDate));
        }

        $createdFrom = $filters['createdFrom'] ?? $filters['created_from'] ?? null;
        if ($createdFrom) {
            $qb->andWhere('cr.createdAt >= :createdFrom')
               ->setParameter('createdFrom', new \DateTimeImmutable($createdFrom . ' 00:00:00'));
        }

        $createdTo = $filters['createdTo'] ?? $filters['created_to'] ?? null;
        if ($createdTo) {
            $qb->andWhere('cr.createdAt <= :createdTo')
               ->setParameter('createdTo', new \DateTimeImmutable($createdTo . ' 23:59:59'));
        }

        $qb->orderBy('cr.id', 'DESC');

        return $qb;
    }
}
