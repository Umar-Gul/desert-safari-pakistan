<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 32);
            $table->string('provider_id');
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
            $table->unique(['user_id', 'provider']);
        });

        if (Schema::hasColumn('users', 'google_id')) {
            DB::table('users')->whereNotNull('google_id')->orderBy('id')->each(function ($user) {
                DB::table('social_accounts')->insert([
                    'user_id'     => $user->id,
                    'provider'    => 'google',
                    'provider_id' => $user->google_id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            });

            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['google_id']);
                $table->dropColumn('google_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('email');
        });

        DB::table('social_accounts')->where('provider', 'google')->orderBy('id')->each(function ($row) {
            DB::table('users')->where('id', $row->user_id)->update(['google_id' => $row->provider_id]);
        });

        Schema::dropIfExists('social_accounts');
    }
};