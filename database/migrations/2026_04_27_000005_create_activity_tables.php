<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('period_type', 20)->index();
            $table->date('period_date')->index();
            $table->decimal('package_omzet', 15, 2)->default(0);
            $table->decimal('omzet', 15, 2)->default(0);
            $table->unsignedInteger('bv')->default(0);
            $table->decimal('sponsor_point', 15, 2)->default(0);
            $table->decimal('pairing_point', 15, 2)->default(0);
            $table->decimal('reward_point', 15, 2)->default(0);
            $table->decimal('ro_point', 15, 2)->default(0);
            $table->decimal('left_point', 15, 2)->default(0);
            $table->decimal('right_point', 15, 2)->default(0);
            $table->decimal('qualified_left', 15, 2)->default(0);
            $table->decimal('qualified_right', 15, 2)->default(0);
            $table->foreignId('rank_id')->nullable()->constrained('ranks')->nullOnDelete();
            $table->string('summary_type', 20)->default('snapshot')->index();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'period_type', 'period_date', 'summary_type'], 'member_performance_period_unique');
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('activity_type', 50)->index();
            $table->string('source_type', 50)->nullable()->index();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('activity_date')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('stockist_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_type', 30)->index();
            $table->string('status')->default('draft')->index();
            $table->unsignedInteger('total_bv')->default(0);
            $table->unsignedInteger('total_qty')->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->unsignedInteger('unique_code')->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('shipping_discount', 15, 2)->default(0);
            $table->decimal('fee', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('handling_fee', 15, 2)->default(0);
            $table->decimal('insurance_fee', 15, 2)->default(0);
            $table->decimal('additional_cost', 15, 2)->default(0);
            $table->decimal('auto_ro_amount', 15, 2)->default(0);
            $table->decimal('total_checkout', 15, 2)->default(0);
            $table->decimal('total_payment', 15, 2)->default(0);
            $table->decimal('payment_remain', 15, 2)->default(0);
            $table->decimal('total_omzet', 15, 2)->default(0);
            $table->decimal('voucher_amount', 15, 2)->default(0);
            $table->decimal('eproduct_amount', 15, 2)->default(0);
            $table->decimal('shipping_balance_amount', 15, 2)->default(0);
            $table->decimal('shipping_subsidy_amount', 15, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->string('payment_shipping_method')->nullable();
            $table->string('payment_shipping_status')->nullable();
            $table->string('bank_code')->nullable();
            $table->string('account_number')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('recipient_email')->nullable();
            $table->foreignId('province_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->string('province_name')->nullable();
            $table->string('city_name')->nullable();
            $table->string('district_name')->nullable();
            $table->string('village_name')->nullable();
            $table->text('address')->nullable();
            $table->string('postcode', 10)->nullable();
            $table->string('courier')->nullable();
            $table->string('service')->nullable();
            $table->unsignedInteger('weight')->default(0);
            $table->string('tracking_number')->nullable();
            $table->boolean('pin_transfer')->default(false);
            $table->text('description')->nullable();
            $table->string('created_by')->nullable();
            $table->string('confirmed_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('stockist_confirmed_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('product_variant_id')->nullable()->index();
            $table->string('item_type', 30)->nullable()->index();
            $table->unsignedInteger('weight')->default(0);
            $table->decimal('point', 15, 2)->default(0);
            $table->unsignedInteger('bv')->default(0);
            $table->decimal('omzet', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('cart_price', 15, 2)->default(0);
            $table->decimal('additional_cost', 15, 2)->default(0);
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->unsignedInteger('subtotal_bv')->default(0);
            $table->decimal('subtotal_omzet', 15, 2)->default(0);
            $table->unsignedInteger('subtotal_weight')->default(0);
            $table->decimal('subtotal_cost', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('code')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('register_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('registered_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('product_variant_id')->nullable()->index();
            $table->string('pin_type', 50)->nullable()->index();
            $table->unsignedInteger('bv')->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->string('used_type')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pin_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pin_id')->constrained('pins')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('product_variant_id')->nullable()->index();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('member_price', 15, 2)->default(0);
            $table->string('transfer_type', 50)->default('transfer_pin')->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pin_transfers');
        Schema::dropIfExists('pins');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('performances');
    }
};
