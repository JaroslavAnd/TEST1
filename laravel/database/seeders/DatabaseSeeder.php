<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Client;
use App\Models\Credit;
use App\Models\CreditType;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Clients
        $client1 = Client::create([
            'first_name' => 'Іван',
            'last_name' => 'Коваленко',
            'email' => 'ivan.kovalenko@example.com',
            'phone' => '+380501112233',
            'tax_id' => '1234567890',
        ]);

        $client2 = Client::create([
            'first_name' => 'Олена',
            'last_name' => 'Петренко',
            'email' => 'olena.petrenko@example.com',
            'phone' => '+380672223344',
            'tax_id' => '0987654321',
        ]);

        // 2. Bank Accounts
        $acc1 = BankAccount::create([
            'client_id' => $client1->id,
            'account_number' => 'UA213052990000026001234567891',
            'balance' => 150000.00,
            'currency' => 'UAH',
            'status' => 'active',
        ]);

        $acc2 = BankAccount::create([
            'client_id' => $client2->id,
            'account_number' => 'UA213052990000026009876543212',
            'balance' => 85000.00,
            'currency' => 'UAH',
            'status' => 'active',
        ]);

        // 3. Credit Types
        $creditType1 = CreditType::create([
            'name' => 'Споживчий кредит',
            'interest_rate' => 18.50,
            'min_amount' => 5000.00,
            'max_amount' => 100000.00,
            'term_months' => 12,
        ]);

        $creditType2 = CreditType::create([
            'name' => 'Автокредит',
            'interest_rate' => 14.00,
            'min_amount' => 100000.00,
            'max_amount' => 800000.00,
            'term_months' => 36,
        ]);

        // 4. Credits
        $credit1 = Credit::create([
            'client_id' => $client1->id,
            'bank_account_id' => $acc1->id,
            'credit_type_id' => $creditType1->id,
            'amount' => 50000.00,
            'remaining_amount' => 40000.00,
            'start_date' => Carbon::today()->subMonths(2)->toDateString(),
            'end_date' => Carbon::today()->addMonths(10)->toDateString(),
            'status' => 'active',
        ]);

        // 5. Payments
        Payment::create([
            'credit_id' => $credit1->id,
            'amount' => 10000.00,
            'payment_date' => Carbon::today()->subMonth(),
            'payment_method' => 'bank_transfer',
            'status' => 'completed',
        ]);
    }
}
