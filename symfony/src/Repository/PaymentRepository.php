<?php

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    /**
     * Пошук та фільтрація по кожному полю таблиці payments
     */
    public function getFilteredQueryBuilder(array $filters = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('p')
                   ->leftJoin('p.credit', 'cr')
                   ->addSelect('cr');

        if (!empty($filters['id'])) {
            $qb->andWhere('p.id = :id')
               ->setParameter('id', (int)$filters['id']);
        }

        $creditId = $filters['creditId'] ?? $filters['credit_id'] ?? null;
        if ($creditId) {
            $qb->andWhere('p.credit = :creditId')
               ->setParameter('creditId', (int)$creditId);
        }

        if (isset($filters['amount']) && $filters['amount'] !== '') {
            $qb->andWhere('p.amount = :amount')
               ->setParameter('amount', (string)$filters['amount']);
        }

        $minAmount = $filters['minAmount'] ?? $filters['min_amount'] ?? null;
        if ($minAmount !== null && $minAmount !== '') {
            $qb->andWhere('p.amount >= :minAmount')
               ->setParameter('minAmount', (string)$minAmount);
        }

        $maxAmount = $filters['maxAmount'] ?? $filters['max_amount'] ?? null;
        if ($maxAmount !== null && $maxAmount !== '') {
            $qb->andWhere('p.amount <= :maxAmount')
               ->setParameter('maxAmount', (string)$maxAmount);
        }

        $paymentMethod = $filters['paymentMethod'] ?? $filters['payment_method'] ?? null;
        if (!empty($paymentMethod)) {
            $qb->andWhere('p.paymentMethod = :paymentMethod')
               ->setParameter('paymentMethod', strtolower($paymentMethod));
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('p.status = :status')
               ->setParameter('status', strtolower($filters['status']));
        }

        $paymentDateFrom = $filters['paymentDateFrom'] ?? $filters['payment_date_from'] ?? null;
        if ($paymentDateFrom) {
            $qb->andWhere('p.paymentDate >= :paymentDateFrom')
               ->setParameter('paymentDateFrom', new \DateTime($paymentDateFrom . ' 00:00:00'));
        }

        $paymentDateTo = $filters['paymentDateTo'] ?? $filters['payment_date_to'] ?? null;
        if ($paymentDateTo) {
            $qb->andWhere('p.paymentDate <= :paymentDateTo')
               ->setParameter('paymentDateTo', new \DateTime($paymentDateTo . ' 23:59:59'));
        }

        $createdFrom = $filters['createdFrom'] ?? $filters['created_from'] ?? null;
        if ($createdFrom) {
            $qb->andWhere('p.createdAt >= :createdFrom')
               ->setParameter('createdFrom', new \DateTimeImmutable($createdFrom . ' 00:00:00'));
        }

        $createdTo = $filters['createdTo'] ?? $filters['created_to'] ?? null;
        if ($createdTo) {
            $qb->andWhere('p.createdAt <= :createdTo')
               ->setParameter('createdTo', new \DateTimeImmutable($createdTo . ' 23:59:59'));
        }

        $qb->orderBy('p.id', 'DESC');

        return $qb;
    }
}
