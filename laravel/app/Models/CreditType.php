<?php

namespace App\Models;

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
}
