<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CommentaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $commentaires = [];

        for ($i = 1; $i <= 10; $i++) {
            $commentaires[] = [
                'description' => 'Commentaire ' . $i,
                'user_id' => 1, // Assurez-vous que cet ID d'utilisateur existe
                'forum_id' => 1, // Assurez-vous que cet ID de forum existe
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('commentaires')->insert($commentaires);
    
    }
}
