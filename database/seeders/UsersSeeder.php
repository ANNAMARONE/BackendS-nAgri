<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [];

        for ($i = 1; $i <= 10; $i++) {
            $users[] = [
                'name' => 'User ' . $i,
                'profile' => 'Profile ' . $i,
                'adresse' => Str::random(10) . ' St',
                'telephone' => '0' . rand(600000000, 999999999),
                'secteur_id' => rand(1, 2), // Assurez-vous que ces secteurs existent dans la table 'secteurs'
                'email' => 'user' . $i . '@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('users')->insert($users);
    
}
}