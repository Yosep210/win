<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30);
            $table->string('code', 10)->nullable()->default('');
        });

        Schema::create('auto_ro_entries', function (Blueprint $table) {
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
            $table->unique(['user_id', 'source_id', 'source'], 'auto_ro_entries_unique');
        });

        Schema::create('auto_ro_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('source_id');
            $table->string('source', 20);
            $table->decimal('amount', 15, 2);
            $table->boolean('status')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'source_id', 'source'], 'auto_ro_outs_unique');
        });

        Schema::create('eproducts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('source_id');
            $table->string('source', 20);
            $table->string('category', 20)->default('product');
            $table->decimal('amount', 15, 2);
            $table->string('type', 5);
            $table->boolean('status')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('ewallet_entries', function (Blueprint $table) {
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
        });

        Schema::create('ewallet_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_code', 30)->unique();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->string('sender_username');
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->string('receiver_username');
            $table->decimal('amount', 15, 2);
            $table->decimal('amount_received', 15, 2);
            $table->decimal('admin_fee', 15, 2);
            $table->boolean('status')->default(true);
            $table->text('description')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('notif_logs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index();
            $table->string('status', 50);
            $table->text('description')->nullable();
            $table->timestamp('logged_at')->nullable();
        });

        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('news');
        Schema::dropIfExists('notif_logs');
        Schema::dropIfExists('ewallet_transfers');
        Schema::dropIfExists('ewallet_entries');
        Schema::dropIfExists('eproducts');
        Schema::dropIfExists('auto_ro_outs');
        Schema::dropIfExists('auto_ro_entries');
        Schema::dropIfExists('areas');
    }
};
