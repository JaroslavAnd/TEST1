<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('interest_rate', 5, 2);
            $table->decimal('min_amount', 15, 2);
            $table->decimal('max_amount', 15, 2);
            $table->integer('term_months');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_types');
    }
};
