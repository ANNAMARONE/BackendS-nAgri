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
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('notifiable_id')->after('id');
            $table->string('notifiable_type')->after('notifiable_id'); // Pour lier le modèle (par exemple, User ou Producteur)
            $table->boolean('is_read')->default(false)->after('message'); // Si la notification est lue ou non
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['notifiable_id', 'notifiable_type', 'is_read']);
        });
    }
};
