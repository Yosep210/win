<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->string('cart_code')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type')->nullable();
            $table->string('grouping', 50);
            $table->unsignedBigInteger('grouping_id');
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('package_id')->nullable()->index();
            $table->unsignedBigInteger('variant_id')->nullable()->index();
            $table->string('name')->nullable();
            $table->decimal('weight', 15, 2)->nullable();
            $table->decimal('bv', 15, 2);
            $table->decimal('price', 15, 2)->nullable();
            $table->integer('qty')->nullable();
            $table->decimal('subtotal', 15, 2)->nullable();
            $table->integer('total_item');
            $table->text('items')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->index(['user_id', 'grouping']);
            $table->index(['grouping_id', 'product_id', 'package_id', 'variant_id']);
        });

        Schema::create('ewallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('source_id');
            $table->string('source', 20);
            $table->string('category', 50)->default('commission');
            $table->decimal('nominal', 15, 2);
            $table->unsignedTinyInteger('percent');
            $table->decimal('auto_ro', 15, 2);
            $table->decimal('tax', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->string('type', 5);
            $table->boolean('status')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'source_id', 'source'], 'ewallet_unique');
        });

        Schema::create('auto_ro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('source_id');
            $table->string('source', 30)->nullable();
            $table->decimal('nominal', 15, 2);
            $table->unsignedTinyInteger('percent');
            $table->decimal('amount', 15, 2);
            $table->boolean('status')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'source_id', 'source'], 'auto_ro_unique');
        });

        Schema::create('auto_ro_out', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('source_id');
            $table->string('source', 20);
            $table->decimal('amount', 15, 2);
            $table->boolean('status')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'source_id', 'source'], 'auto_ro_out_unique');
        });

        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('key')->nullable();
            $table->unsignedTinyInteger('level')->default(1);
            $table->boolean('ignore_limits')->default(false);
            $table->boolean('is_private_key')->default(false);
            $table->string('ip_addresses')->nullable();
            $table->timestamps();
        });

        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index();
            $table->timestamp('logged_at');
            $table->string('status', 50);
            $table->text('description')->nullable();
        });

        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index();
            $table->string('status', 50);
            $table->string('username', 20)->nullable();
            $table->string('ip', 50)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('platform')->nullable();
            $table->text('description')->nullable();
            $table->string('assum')->nullable()->default('');
            $table->string('assum_staff')->nullable()->default('');
            $table->timestamp('logged_at')->nullable();
        });

        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index();
            $table->string('status', 50);
            $table->string('username', 20)->nullable();
            $table->string('ip', 50)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('platform')->nullable();
            $table->text('description')->nullable();
            $table->text('token')->nullable();
            $table->timestamp('logged_at')->nullable();
        });

        Schema::create('cron_logs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index();
            $table->string('status', 50);
            $table->text('description')->nullable();
            $table->timestamp('logged_at')->nullable();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index();
            $table->string('status', 50);
            $table->text('description')->nullable();
            $table->timestamp('logged_at')->nullable();
        });

        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('password', 100);
            $table->string('name', 100);
            $table->string('email', 50);
            $table->string('phone', 20);
            $table->string('photo')->nullable();
            $table->string('access', 10);
            $table->text('role')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('email', 50)->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('upgrades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upgrader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('package_before', 50);
            $table->string('package_after', 50);
            $table->string('upgrade', 100);
            $table->unsignedBigInteger('omzet')->default(0);
            $table->unsignedBigInteger('amount')->default(0);
            $table->unsignedSmallInteger('point')->default(0);
            $table->text('pins')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->text('url');
            $table->integer('sequence');
            $table->text('image');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('video_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('video_id')->constrained('videos')->cascadeOnDelete();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('withdraw_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 30)->nullable();
            $table->foreignId('bank_id')->constrained('banks')->cascadeOnDelete();
            $table->string('bank_type', 50)->nullable();
            $table->string('bank_code', 20)->nullable()->default('');
            $table->string('bank_name', 150)->nullable();
            $table->string('account_number', 100);
            $table->string('account_name', 100);
            $table->decimal('amount', 15, 2);
            $table->decimal('amount_received', 15, 2);
            $table->decimal('tax', 15, 2);
            $table->decimal('auto_ro', 15, 2);
            $table->decimal('admin_fee', 15, 2);
            $table->tinyInteger('status')->default(0);
            $table->string('external_id', 50)->nullable()->default('');
            $table->string('inquiry_reference', 100)->nullable();
            $table->string('inquiry_status', 100)->nullable()->default('');
            $table->string('payment_reference', 100)->nullable();
            $table->string('payment_status', 100)->nullable()->default('');
            $table->timestamp('confirmed_at')->nullable();
            $table->string('confirmed_by', 50)->nullable()->default('');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdraw_requests');
        Schema::dropIfExists('video_progress');
        Schema::dropIfExists('videos');
        Schema::dropIfExists('upgrades');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('options');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('cron_logs');
        Schema::dropIfExists('api_logs');
        Schema::dropIfExists('action_logs');
        Schema::dropIfExists('logs');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('auto_ro_out');
        Schema::dropIfExists('auto_ro');
        Schema::dropIfExists('ewallets');
        Schema::dropIfExists('cart_items');
    }
};
