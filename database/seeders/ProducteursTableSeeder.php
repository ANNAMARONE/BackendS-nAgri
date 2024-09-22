<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProducteursTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          // Créez quelques utilisateurs (producteurs) à partir desquels vous allez lier les producteurs
          $users = User::factory()->count(10)->create(); // Assurez-vous d'avoir une factory pour User

          // Pour chaque utilisateur, insérez des producteurs
          
          
  foreach ($users as $user) {
              DB::
            
  
     
  table('producteurs')->insert([
                  'user_id' => $user->id,
                  'acteur' => 'Agriculteurs', // Ou choisissez aléatoirement 'Jardiniers'
                  'region' => 'Dakar', // Vous pouvez choisir une région au hasard
                  'created_at' => now(),
                  
    
  'updated_at' => now(),
              ]);
          }
      
    }
}
