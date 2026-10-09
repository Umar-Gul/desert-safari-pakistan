<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $this->seo($table);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('deserts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $this->seo($table);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('desert_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->decimal('duration_hours', 4, 1)->default(0);
            $table->decimal('base_price_adult', 10, 2);
            $table->decimal('base_price_child', 10, 2)->default(0);
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->string('price_label', 50)->default('per person');
            $table->decimal('rating_cached', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count_cached')->default(0);
            $table->boolean('has_dinner')->default(false);
            $table->boolean('has_breakfast')->default(false);
            $table->boolean('has_guide')->default(false);
            $table->boolean('has_transfer')->default(false);
            $table->boolean('has_itinerary')->default(false);
            $table->unsignedSmallInteger('max_group_size')->nullable();
            $table->unsignedTinyInteger('min_age')->nullable();
            $table->json('badges')->nullable();
            $table->longText('cancellation_policy')->nullable();
            $this->seo($table);
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_best_selling')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('category_tour', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'tour_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_tour');
        Schema::dropIfExists('tours');
        Schema::dropIfExists('deserts');
        Schema::dropIfExists('categories');
    }

    private function seo(Blueprint $table): void
    {
        $table->string('meta_title')->nullable();
        $table->string('meta_description', 320)->nullable();
        $table->string('canonical')->nullable();
        $table->string('og_image')->nullable();
    }
};