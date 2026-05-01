<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('commission_balance', 15, 2)->default(0);
            $table->decimal('eproduct_balance', 15, 2)->default(0);
            $table->decimal('shipping_balance', 15, 2)->default(0);
            $table->decimal('shipping_subsidy_balance', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('wallet_type', 30)->index();
            $table->string('direction', 10)->index();
            $table->string('source_type', 50)->nullable()->index();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('balance_before', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('posted')->index();
            $table->timestamps();
        });

        Schema::create('bonus_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('bonus_code')->nullable()->index();
            $table->string('bonus_type', 50)->index();
            $table->string('source_type', 50)->nullable()->index();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->foreignId('downline_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('generation')->nullable();
            $table->unsignedInteger('level')->nullable();
            $table->decimal('omzet', 15, 2)->default(0);
            $table->decimal('percentage', 8, 2)->default(0);
            $table->decimal('gross_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->date('bonus_date')->nullable()->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->string('withdraw_type', 30)->index();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('amount_received', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('admin_fee', 15, 2)->default(0);
            $table->decimal('auto_ro_amount', 15, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->string('external_reference')->nullable()->index();
            $table->string('inquiry_reference')->nullable()->index();
            $table->string('inquiry_status')->nullable();
            $table->string('payment_reference')->nullable()->index();
            $table->string('payment_status')->nullable();
            $table->string('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('bonus_transactions');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};
