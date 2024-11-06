<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
           // Réinitialiser les rôles et permissions en cache
           app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

           // Créer des permissions
           Permission::create(['name' => 'acherter_produit']);
           Permission::create(['name' => 'voire_panier']);
           Permission::create(['name'=> 'faire_un_payement']);
           Permission::create(['name'=> 'consulter_catalogue']);
           Permission::create(['name' => 'valider_commande']);
           Permission::create(['name' => 'faire_paiement']);
           Permission::create(['name' => 'gestion_produit']);
           Permission::create(['name' => 'gestion_commande']);
           Permission::create(['name' => 'gestion_utilisateur']);
           Permission::create(['name' => 'gestion_ressource']);
           Permission::create(['name' => 'gestion_evenement']);
           Permission::create(['name' => 'gestion_article']);
           Permission::create(['name' => 'gestion_profil']);
           Permission::create(['name'=> 'gestion_role']);
           Permission::create(['name'=> 'gestion_permission']);
           Permission::create(['name'=> 'gestion_catégorie']);
           Permission::create(['name'=> 'gestion_secteur']);
           Permission::create(['name'=> 'voire_ressource']);
           Permission::create(['name'=> 'voire_evenement']);
           Permission::create(['name'=> 'voire_article']);
           Permission::create(['name'=> 'accéder_forum']);
           Permission::create(['name'=> 'ajouter_commentaire']);
           Permission::create(['name'=> 'voire_paiment']);
   
           // Créer des rôles et assigner les permissions créées
           $role = Role::create(['name' => 'client']);
           $role->givePermissionTo('acherter_produit');
           $role->givePermissionTo('valider_commande');
           $role->givePermissionTo('voire_panier');
            $role->givePermissionTo('consulter_catalogue');
            $role->givePermissionTo('gestion_profil');
            $role->givePermissionTo('faire_un_payement');
            $role->givePermissionTo('voire_paiment');
            $role->givePermissionTo('accéder_forum');
           $role = Role::create(['name' => 'producteur']);
           $role->givePermissionTo('gestion_produit');
           $role->givePermissionTo('gestion_commande');
           $role->givePermissionTo('ajouter_commentaire');
           $role->givePermissionTo('accéder_forum');
           $role->givePermissionTo('voire_evenement');
           $role->givePermissionTo('voire_ressource');
           $role->givePermissionTo('voire_article');
           $role->givePermissionTo('voire_paiment');
           $role->givePermissionTo('gestion_profil');
   
           $role = Role::create(['name' => 'admin']);
           
           $role->givePermissionTo(Permission::all());
       }
    
}

