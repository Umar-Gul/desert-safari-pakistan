<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->date('date')->index();
            $table->unsignedInteger('capacity')->default(0);
            $table->unsignedInteger('booked_count')->default(0);
            $table->boolean('is_closed')->default(false);
            $table->decimal('price_override', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['tour_id', 'date']);
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('type', 10)->default('percent');
            $table->decimal('value', 10, 2);
            $table->decimal('min_amount', 12, 2)->default(0);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->foreignId('tour_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('customer_name');
            $table->string('email')->index();
            $table->string('phone', 50);
            $table->string('whatsapp', 50)->nullable();
            $table->string('nationality', 100)->nullable();

            $table->date('tour_date')->index();
            $table->string('pickup_time', 10)->nullable();
            $table->string('pickup_location')->nullable();
            $table->text('pickup_address')->nullable();
            $table->string('city', 100)->nullable();

            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);

            $table->foreignId('camp_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->text('special_requests')->nullable();

            // Price snapshot (never recomputed after creation)
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('addons_total', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->string('currency', 3)->default('PKR');

            $table->string('status', 20)->default('pending')->index();
            $table->string('payment_status', 20)->default('unpaid')->index();
            $table->string('source', 20)->default('web');
            $table->text('admin_notes')->nullable();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('booking_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('addon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name_snapshot');
            $table->decimal('price_snapshot', 10, 2);
            $table->unsignedSmallInteger('quantity')->default(1);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('method', 20);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('PKR');
            $table->string('status', 20)->default('pending')->index();
            $table->string('transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('booking_addons');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('availabilities');
    }
};