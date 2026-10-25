<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Credit extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'bank_account_id',
        'credit_type_id',
        'amount',
        'remaining_amount',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function creditType(): BelongsTo
    {
        return $this->belongsTo(CreditType::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Фільтрація по кожному полю таблиці credits
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        $clientId = $filters['client_id'] ?? $filters['clientId'] ?? null;
        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        $bankAccountId = $filters['bank_account_id'] ?? $filters['bankAccountId'] ?? null;
        if ($bankAccountId) {
            $query->where('bank_account_id', $bankAccountId);
        }

        $creditTypeId = $filters['credit_type_id'] ?? $filters['creditTypeId'] ?? null;
        if ($creditTypeId) {
            $query->where('credit_type_id', $creditTypeId);
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

        $minRemaining = $filters['min_remaining_amount'] ?? $filters['minRemainingAmount'] ?? null;
        if ($minRemaining !== null && $minRemaining !== '') {
            $query->where('remaining_amount', '>=', $minRemaining);
        }

        $maxRemaining = $filters['max_remaining_amount'] ?? $filters['maxRemainingAmount'] ?? null;
        if ($maxRemaining !== null && $maxRemaining !== '') {
            $query->where('remaining_amount', '<=', $maxRemaining);
        }

        if (!empty($filters['status'])) {
            $query->where('status', strtolower($filters['status']));
        }

        $startDate = $filters['start_date'] ?? $filters['startDate'] ?? null;
        if ($startDate) {
            $query->where('start_date', '>=', $startDate);
        }

        $endDate = $filters['end_date'] ?? $filters['endDate'] ?? null;
        if ($endDate) {
            $query->where('end_date', '<=', $endDate);
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
