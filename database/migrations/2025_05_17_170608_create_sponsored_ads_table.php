<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sponsored_ads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_url');
            $table->string('target_url');
            $table->string('sponsor_name')->nullable();
            $table->string('sponsor_logo')->nullable();
            $table->string('cta_text')->nullable()->default('Learn More');
            $table->string('background_color')->nullable()->default('#fc8019');
            $table->string('text_color')->nullable()->default('#FFFFFF');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('impressions_count')->default(0);
            $table->integer('clicks_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsored_ads');
    }
};
