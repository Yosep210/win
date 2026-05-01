<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('birth_date')->nullable();
            $table->string('id_number')->nullable()->unique();
            $table->string('npwp')->nullable()->unique();
            $table->text('address')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('province_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->string('photo')->nullable();
            $table->string('id_card_photo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_securities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->string('password_pin')->nullable();
            $table->string('password_transaction')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_networks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('sponsor_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('position')->nullable()->comment('0:root, 1:left, 2:right');
            $table->unsignedInteger('generation')->default(0);
            $table->unsignedInteger('level')->default(0);
            $table->unsignedInteger('group')->default(0);
            $table->foreignId('user_hu_id')->index()->constrained('users')->cascadeOnDelete();
            $table->longText('tree')->nullable();
            $table->longText('tree_sponsor')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['parent_id', 'position']);
            $table->index('sponsor_id');
        });

        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('type', 50);
            $table->string('code', 6);
            $table->string('ip_address', 45)->nullable();
            $table->string('platform', 50)->nullable();
            $table->string('browser', 100)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->unsignedBigInteger('references_id')->nullable();
            $table->string('references_type')->nullable();
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'type'], 'otp_codes_user_id_type_index');
            $table->index(['email', 'type'], 'otp_codes_email_type_index');
            $table->index(['phone', 'type'], 'otp_codes_phone_type_index');
            $table->index(['code', 'type'], 'otp_codes_code_index');
            $table->index('expired_at', 'otp_codes_expired_at_index');
            $table->index('is_used', 'otp_codes_is_used_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('user_securities');
        Schema::dropIfExists('user_networks');
        Schema::dropIfExists('otp_codes');
    }
};
