<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProduitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $produits = [
            ['libelle' => 'Produit 1', 'image' => 'image1.jpg', 'description' => 'Description du produit 1', 'quantite' => 10, 'prix' => 100, 'statut' => 'en stock', 'user_id' => 1, 'categorie_produit_id' => 1],
            ['libelle' => 'Produit 2', 'image' => 'image2.jpg', 'description' => 'Description du produit 2', 'quantite' => 20, 'prix' => 200, 'statut' => 'en stock', 'user_id' => 1, 'categorie_produit_id' => 2],
            // Ajoutez 18 autres produits ici
        ];

        for ($i = 3; $i <= 20; $i++) {
            $produits[] = [
                'libelle' => 'Produit ' . $i,
                'image' => 'image' . $i . '.jpg',
                'description' => 'Description du produit ' . $i,
                'quantite' => rand(1, 100),
                'prix' => rand(10, 1000),
                'statut' => 'en stock',
                'user_id' =>19,
                'categorie_produit_id' => rand(1, 5),
               
            ];
        }

        DB::table('produits')->insert($produits);
    
    }
}
