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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('commande_id');
            $table->string('transaction_id')->nullable(); // Peut être NULL si nécessaire
            $table->string('payment_method'); // Méthode de paiement (en_ligne, livraison, etc.)
            $table->decimal('amount', 10, 2); // Montant du paiement
            $table->string('payment_status'); // Statut du paiement (en_attente, complété, annulé)
            $table->timestamps();

            // Définir la clé étrangère pour `commande_id`
            $table->foreign('commande_id')->references('id')->on('commandes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
