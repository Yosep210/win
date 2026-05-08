<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('package_count')->default(1);
            $table->unsignedInteger('bv')->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('sponsor_percent', 5, 2)->default(0);
            $table->decimal('passup_percent', 5, 2)->default(0);
            $table->decimal('pairing_percent', 5, 2)->default(0);
            $table->decimal('pairing_nominal', 15, 2)->default(0);
            $table->unsignedInteger('pairing_max')->default(0);
            $table->unsignedInteger('pairing_point')->default(0);
            $table->decimal('reward_point', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_register')->default(false);
            $table->boolean('is_order')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->char('iso', 2)->unique();
            $table->string('name');
            $table->string('nice_name');
            $table->char('iso3', 3)->nullable()->index();
            $table->unsignedSmallInteger('numcode')->nullable();
            $table->unsignedInteger('phone_code')->default(0);
            $table->boolean('status')->default(true)->index();
        });

        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->string('name');
            // $table->string('code')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['city', 'regency'])->nullable();
            // $table->string('code')->nullable()->index();
            // $table->string('postal_code', 10)->nullable();
            // $table->string('external_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->string('name');
            // $table->string('postal_code', 10)->nullable();
            // $table->string('external_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->string('name');
            $table->string('postal_code', 10)->nullable();
            // $table->string('external_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('banks');
        Schema::dropIfExists('ranks');
        Schema::dropIfExists('packages');
    }
};
