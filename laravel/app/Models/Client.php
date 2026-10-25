<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'tax_id',
    ];

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    public function credits(): HasMany
    {
        return $this->hasMany(Credit::class);
    }

    /**
     * Фільтрація по кожному полю таблиці clients
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        $firstName = $filters['first_name'] ?? $filters['firstName'] ?? null;
        if ($firstName !== null && $firstName !== '') {
            $query->where('first_name', 'like', '%' . $firstName . '%');
        }

        $lastName = $filters['last_name'] ?? $filters['lastName'] ?? null;
        if ($lastName !== null && $lastName !== '') {
            $query->where('last_name', 'like', '%' . $lastName . '%');
        }

        if (!empty($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (!empty($filters['phone'])) {
            $query->where('phone', 'like', '%' . $filters['phone'] . '%');
        }

        $taxId = $filters['tax_id'] ?? $filters['taxId'] ?? null;
        if ($taxId !== null && $taxId !== '') {
            $query->where('tax_id', 'like', '%' . $taxId . '%');
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
