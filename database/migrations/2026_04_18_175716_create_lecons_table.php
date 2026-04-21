<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->onDelete('cascade');
            $table->string('titre', 200);
            $table->enum('type_contenu', ['video', 'pdf', 'article']);
            $table->text('contenu_text')->nullable();
            $table->string('url_video', 500)->nullable();
            $table->string('url_pdf', 500)->nullable();
            $table->integer('duree_video')->nullable();
            $table->integer('ordre');
            $table->timestamps();
            
            $table->index(['module_id', 'ordre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecons');
    }
};