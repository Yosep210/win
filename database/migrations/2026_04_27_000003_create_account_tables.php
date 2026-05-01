<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->foreignId('rank_id')->nullable()->constrained('ranks')->nullOnDelete();
            $table->string('as_stockist')->default('member')->index();
            $table->boolean('is_stockist_central')->default(false);
            $table->string('stockist_name')->nullable();
            $table->foreignId('stockist_province_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->foreignId('stockist_city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('stockist_district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->string('stockist_village')->nullable();
            $table->text('stockist_address')->nullable();
            $table->string('wd_status')->default('manual');
            $table->decimal('wd_min', 15, 2)->default(0);
            $table->boolean('is_ro_enabled')->default(false);
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('upgraded_at')->nullable();
            $table->timestamp('stockist_at')->nullable();
            $table->timestamp('last_ro_at')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bank_id')->constrained('banks')->cascadeOnDelete();
            $table->string('account_number');
            $table->string('account_name');
            $table->boolean('is_primary')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'account_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('memberships');
    }
};
