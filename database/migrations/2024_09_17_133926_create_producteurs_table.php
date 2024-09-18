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
        Schema::create('producteurs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('acteur', ['Agriculteurs','Jardiniers']);
            $table->enum('region', [
                'Dakar',
                'Diourbel',
                'Fatick',
                'Kaffrine',
                'Kaolack',
                'Kédougou',
                'Kolda',
                'Louga',
                'Matam',
                'Saint-Louis',
                'Sédhiou',
                'Tambacounda',
                'Thiès',
                'Ziguinchor'
            ]);
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producteurs');
    }
};
