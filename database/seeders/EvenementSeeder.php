<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EvenementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $evenements = [];

        for ($i = 1; $i <= 10; $i++) {
            $evenements[] = [
                'libelle' => 'Événement ' . $i,
                'image' => 'image' . $i . '.jpg',
                'description' => 'Description de l\'événement ' . $i,
                'lien' => 'http://example.com/evenement' . $i,
                'date' => now()->addDays($i),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('evenements')->insert($evenements);
    
    }
}
