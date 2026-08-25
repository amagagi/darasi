<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();

            $table->string('author_name');
            // Ex. "Développeuse Mobile", "Apprenante, promotion 2024".
            $table->string('author_role')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('content');
            $table->unsignedTinyInteger('rating')->nullable();

            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'display_order'], 'testimonials_actifs_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
