<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_id',
        'amount',
        'payment_date',
        'payment_method',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    public function credit(): BelongsTo
    {
        return $this->belongsTo(Credit::class);
    }

    /**
     * Фільтрація по кожному полю таблиці payments
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        $creditId = $filters['credit_id'] ?? $filters['creditId'] ?? null;
        if ($creditId) {
            $query->where('credit_id', $creditId);
        }

        if (isset($filters['amount']) && $filters['amount'] !== '') {
            $query->where('amount', $filters['amount']);
        }

        $minAmount = $filters['min_amount'] ?? $filters['minAmount'] ?? null;
        if ($minAmount !== null && $minAmount !== '') {
            $query->where('amount', '>=', $minAmount);
        }

        $maxAmount = $filters['max_amount'] ?? $filters['maxAmount'] ?? null;
        if ($maxAmount !== null && $maxAmount !== '') {
            $query->where('amount', '<=', $maxAmount);
        }

        $method = $filters['payment_method'] ?? $filters['paymentMethod'] ?? null;
        if (!empty($method)) {
            $query->where('payment_method', strtolower($method));
        }

        if (!empty($filters['status'])) {
            $query->where('status', strtolower($filters['status']));
        }

        $dateFrom = $filters['payment_date_from'] ?? $filters['paymentDateFrom'] ?? null;
        if ($dateFrom) {
            $query->where('payment_date', '>=', $dateFrom . ' 00:00:00');
        }

        $dateTo = $filters['payment_date_to'] ?? $filters['paymentDateTo'] ?? null;
        if ($dateTo) {
            $query->where('payment_date', '<=', $dateTo . ' 23:59:59');
        }

        $createdFrom = $filters['created_from'] ?? $filters['createdFrom'] ?? null;
        if ($createdFrom) {
            $query->where('created_at', '>=', $createdFrom . ' 00:00:00');
        }

        $createdTo = $filters['created_to'] ?? $filters['createdTo'] ?? null;
        if ($createdTo) {
            $query->where('created_at', '<=', $createdTo . ' 23:59:59');
        }

        return $query->orderBy('id', 'desc');
    }
}
