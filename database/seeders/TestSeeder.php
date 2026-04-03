<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{ Client, BillingCycleType, PaymentMethod, TransactionType, User, Invoice };
use Database\Factories\ServiceVoucherFactory;
use Database\Factories\ReceiptVoucherFactory;
use Illuminate\Support\Facades\Hash;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // UsersSeeder

        $execUsersSeeder = true;
        
        if($execUsersSeeder){
            $password = Hash::make('password');

            for($i = 0; $i < 10; $i++){
                User::create([
                    'code' => sprintf('%04d', $i),
                    'name' => fake()->lastName(),
                    'email' => fake()->unique()->safeEmail(),
                    'password' => $password,
                ]);
            }
        }

        // ClientsSeeder

        $execClientsSeeder = true;

        if($execClientsSeeder){
            for($i = 0; $i < 10; $i++){
                $billing_cycle_type_id = BillingCycleType::inRandomOrder()->value('id');

                Client::create([
                    'code' => sprintf('%04d', $i), 
                    'name' => fake()->company(), 
                    'postal' => fake()->randomNumber(3, true).'-'.fake()->randomNumber(4, true), 
                    'address' => fake()->address(), 
                    'tel' => fake()->phoneNumber(), 
                    'fax' => fake()->phoneNumber(), 
                    'email' => fake()->unique()->safeEmail(),
                    'website' => fake()->url(), 
                    'initial_previous_invoice_amount' => fake()->randomNumber(5, true), 
                    'billing_cycle_type_id' => $billing_cycle_type_id, 
                    'billing_day' => ($billing_cycle_type_id === 2) ? fake()->numberBetween(1, 28) : null,
                    'payment_method_id' => PaymentMethod::inRandomOrder()->value('id'), 
                    'transaction_type_id' => TransactionType::inRandomOrder()->value('id'), 
                    'name_suffix_id' => 1, 
                    'allow_login' => fake()->boolean(), 
                    'user_id' => User::inRandomOrder()->value('id')
                ]);
            }
        }
    }
}
