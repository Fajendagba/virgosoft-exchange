<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('passport:client', [
            '--personal' => true,
            '--name' => 'Exchange Personal Access Client',
            '--no-interaction' => true,
        ]);

        $this->command->info('Passport Personal Access Client created successfully.');

        $users = [
            [
                'name' => 'Alice Trader',
                'email' => 'alice@example.com',
                'password' => bcrypt('password'),
                'balance' => 50000.00,
            ],
            [
                'name' => 'Bob Investor',
                'email' => 'bob@example.com',
                'password' => bcrypt('password'),
                'balance' => 25000.00,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create($userData);

            Asset::create([
                'user_id' => $user->id,
                'symbol' => 'BTC',
                'amount' => rand(1, 5) + (rand(0, 99999999) / 100000000),
                'locked_amount' => 0,
            ]);

            Asset::create([
                'user_id' => $user->id,
                'symbol' => 'ETH',
                'amount' => rand(10, 50) + (rand(0, 99999999) / 100000000),
                'locked_amount' => 0,
            ]);
        }
    }
}