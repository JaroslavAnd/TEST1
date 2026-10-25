<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'interest_rate',
        'min_amount',
        'max_amount',
        'term_months',
    ];

    protected $casts = [
        'interest_rate' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'term_months' => 'integer',
    ];

    public function credits(): HasMany
    {
        return $this->hasMany(Credit::class);
    }

    /**
     * Фільтрація по кожному полю таблиці credit_types
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        $interestRate = $filters['interest_rate'] ?? $filters['interestRate'] ?? null;
        if ($interestRate !== null && $interestRate !== '') {
            $query->where('interest_rate', $interestRate);
        }

        $minRate = $filters['min_interest_rate'] ?? $filters['minInterestRate'] ?? null;
        if ($minRate !== null && $minRate !== '') {
            $query->where('interest_rate', '>=', $minRate);
        }

        $maxRate = $filters['max_interest_rate'] ?? $filters['maxInterestRate'] ?? null;
        if ($maxRate !== null && $maxRate !== '') {
            $query->where('interest_rate', '<=', $maxRate);
        }

        $minAmount = $filters['min_amount'] ?? $filters['minAmount'] ?? null;
        if ($minAmount !== null && $minAmount !== '') {
            $query->where('min_amount', '>=', $minAmount);
        }

        $maxAmount = $filters['max_amount'] ?? $filters['maxAmount'] ?? null;
        if ($maxAmount !== null && $maxAmount !== '') {
            $query->where('max_amount', '<=', $maxAmount);
        }

        $termMonths = $filters['term_months'] ?? $filters['termMonths'] ?? null;
        if ($termMonths !== null && $termMonths !== '') {
            $query->where('term_months', $termMonths);
        }

        return $query->orderBy('id', 'asc');
    }
}
