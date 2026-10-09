<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Dropdown list in the header menu.
            $table->boolean('show_in_menu')->default(false)->index()->after('og_image');
            // The few quick links shown directly in the top bar.
            $table->boolean('is_quick_link')->default(false)->index()->after('show_in_menu');
        });

        Schema::create('trust_badges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('quick_links', function (Blueprint $table) {
            $table->id();
            $table->string('section', 50)->index();
            $table->string('label');
            $table->string('url');
            $table->string('icon', 50)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quick_links');
        Schema::dropIfExists('trust_badges');

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['show_in_menu']);
            $table->dropIndex(['is_quick_link']);
            $table->dropColumn(['show_in_menu', 'is_quick_link']);
        });
    }
};