<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annonces', function (Blueprint $table) {
            $table->id();

            $table->string('titre');
            // Message court affiché dans le bandeau d'alerte.
            $table->string('extrait', 500)->nullable();
            // Contenu long (HTML) affiché dans la section Actualités.
            $table->text('contenu')->nullable();

            // Habillage visuel du bandeau.
            $table->enum('type', ['info', 'succes', 'avertissement', 'urgent'])
                ->default('info');

            // Qui voit l'annonce : visiteurs de la landing, utilisateurs
            // connectés, ou les deux.
            $table->enum('cible', ['public', 'connectes', 'tous'])
                ->default('tous');

            // Le bandeau est indépendant de la section Actualités : une annonce
            // peut apparaître en news sans mobiliser le bandeau du haut.
            $table->boolean('afficher_banniere')->default(false);
            $table->boolean('afficher_actualites')->default(true);
            // Bandeau non masquable par l'utilisateur (maintenance, incident).
            $table->boolean('est_permanente')->default(false);

            $table->string('lien_url')->nullable();
            $table->string('lien_libelle')->nullable();
            $table->string('image')->nullable();

            $table->boolean('est_publiee')->default(false);
            $table->timestamp('publiee_le')->nullable();
            $table->timestamp('expire_le')->nullable();
            $table->unsignedInteger('priorite')->default(0);

            $table->foreignId('cree_par')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Index de la requête chaude : les annonces actives du moment.
            $table->index(['est_publiee', 'publiee_le', 'expire_le'], 'annonces_actives_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annonces');
    }
};
