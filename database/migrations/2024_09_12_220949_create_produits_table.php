<?php

use App\Models\categorieProduit;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('image');
            $table->text('description');
            $table->integer('quantite');
            $table->integer('prix');
            $table->enum('statut',['en stock','en rupture'])->default('en stock');
             
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            
            $table->foreignIdFor(categorieProduit::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
