<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'account_number',
        'balance',
        'currency',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function credits(): HasMany
    {
        return $this->hasMany(Credit::class);
    }

    /**
     * Фільтрація по кожному полю таблиці bank_accounts
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

        $accountNumber = $filters['account_number'] ?? $filters['accountNumber'] ?? null;
        if ($accountNumber !== null && $accountNumber !== '') {
            $query->where('account_number', 'like', '%' . $accountNumber . '%');
        }

        if (isset($filters['balance']) && $filters['balance'] !== '') {
            $query->where('balance', $filters['balance']);
        }

        $minBalance = $filters['min_balance'] ?? $filters['minBalance'] ?? null;
        if ($minBalance !== null && $minBalance !== '') {
            $query->where('balance', '>=', $minBalance);
        }

        $maxBalance = $filters['max_balance'] ?? $filters['maxBalance'] ?? null;
        if ($maxBalance !== null && $maxBalance !== '') {
            $query->where('balance', '<=', $maxBalance);
        }

        if (!empty($filters['currency'])) {
            $query->where('currency', strtoupper($filters['currency']));
        }

        if (!empty($filters['status'])) {
            $query->where('status', strtolower($filters['status']));
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
