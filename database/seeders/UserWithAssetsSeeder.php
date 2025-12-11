<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserWithAssetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $user1 = User::create([
            'name' => 'Alice',
            'email' => 'alice@test.com',
            'password' => Hash::make('password'),
            'balance' => 100000.00,
        ]);

        $user2 = User::create([
            'name' => 'Bob',
            'email' => 'bob@test.com',
            'password' => Hash::make('password'),
            'balance' => 50000.00,
        ]);

        Asset::create(['user_id' => $user1->id, 'symbol' => 'BTC', 'amount' => 5.0]);
        Asset::create(['user_id' => $user1->id, 'symbol' => 'ETH', 'amount' => 50.0]);
        Asset::create(['user_id' => $user2->id, 'symbol' => 'BTC', 'amount' => 2.0]);
        Asset::create(['user_id' => $user2->id, 'symbol' => 'ETH', 'amount' => 100.0]);
    }
}
