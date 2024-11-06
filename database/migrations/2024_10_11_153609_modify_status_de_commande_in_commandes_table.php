<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->enum('status_de_commande', ['invalide', 'en_attente', 'en_cours', 'expediee', 'livree'])
            ->default('invalide')
            ->change();
  });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            
        Schema::table('commandes', function (Blueprint $table) {
            $table->string('status_de_commande')->default('invalide')->change();
        });
        });
    }
};
