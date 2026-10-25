<?php

namespace App\Repository;

use App\Entity\BankAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BankAccount>
 */
class BankAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankAccount::class);
    }

    /**
     * Пошук та фільтрація по кожному полю таблиці bank_accounts
     */
    public function getFilteredQueryBuilder(array $filters = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b')
                   ->leftJoin('b.client', 'c')
                   ->addSelect('c');

        if (!empty($filters['id'])) {
            $qb->andWhere('b.id = :id')
               ->setParameter('id', (int)$filters['id']);
        }

        $clientId = $filters['clientId'] ?? $filters['client_id'] ?? null;
        if ($clientId) {
            $qb->andWhere('b.client = :clientId')
               ->setParameter('clientId', (int)$clientId);
        }

        $accountNumber = $filters['accountNumber'] ?? $filters['account_number'] ?? null;
        if ($accountNumber !== null && $accountNumber !== '') {
            $qb->andWhere('b.accountNumber LIKE :accountNumber')
               ->setParameter('accountNumber', '%' . $accountNumber . '%');
        }

        if (isset($filters['balance']) && $filters['balance'] !== '') {
            $qb->andWhere('b.balance = :balance')
               ->setParameter('balance', (string)$filters['balance']);
        }

        $minBalance = $filters['minBalance'] ?? $filters['min_balance'] ?? null;
        if ($minBalance !== null && $minBalance !== '') {
            $qb->andWhere('b.balance >= :minBalance')
               ->setParameter('minBalance', (string)$minBalance);
        }

        $maxBalance = $filters['maxBalance'] ?? $filters['max_balance'] ?? null;
        if ($maxBalance !== null && $maxBalance !== '') {
            $qb->andWhere('b.balance <= :maxBalance')
               ->setParameter('maxBalance', (string)$maxBalance);
        }

        if (!empty($filters['currency'])) {
            $qb->andWhere('b.currency = :currency')
               ->setParameter('currency', strtoupper($filters['currency']));
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('b.status = :status')
               ->setParameter('status', strtolower($filters['status']));
        }

        $createdFrom = $filters['createdFrom'] ?? $filters['created_from'] ?? null;
        if ($createdFrom) {
            $qb->andWhere('b.createdAt >= :createdFrom')
               ->setParameter('createdFrom', new \DateTimeImmutable($createdFrom . ' 00:00:00'));
        }

        $createdTo = $filters['createdTo'] ?? $filters['created_to'] ?? null;
        if ($createdTo) {
            $qb->andWhere('b.createdAt <= :createdTo')
               ->setParameter('createdTo', new \DateTimeImmutable($createdTo . ' 23:59:59'));
        }

        $qb->orderBy('b.id', 'DESC');

        return $qb;
    }
}
