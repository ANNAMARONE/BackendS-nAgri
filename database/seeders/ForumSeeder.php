<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ForumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $forums = [];

        for ($i = 1; $i <= 10; $i++) {
            $forums[] = [
                'libelle' => 'Forum ' . $i,
                'description' => 'Description du forum ' . $i,
                'date' => now()->addDays($i),
                'user_id' => 1, // Assurez-vous que cet ID d'utilisateur existe
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ];
        }

        DB::table('forums')->insert($forums);
    
    }
}
