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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Informations de base
            $table->string('title'); // Ex: Inspection Site A
            $table->text('description'); // Détails de l'activité
            
            // Localisation (On garde texte pour la routine, + GPS optionnel)
            $table->string('location_name'); // Ex: Usine Nord
            $table->decimal('latitude', 10, 8)->nullable(); // Pour la carte
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Statut du rapport (Brouillon, Soumis, Validé, Rejeté)
            $table->string('status')->default('draft'); 
            
            // Fichiers (Photos/PJ)
            // On stockera les chemins dans une table liée ou un JSON, 
            // pour simplifier débutons avec un champ JSON pour les photos
            $table->json('photos')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
