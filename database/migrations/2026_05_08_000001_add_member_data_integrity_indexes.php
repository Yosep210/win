<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->unique('user_id', 'user_profiles_user_id_unique');
        });

        Schema::table('provinces', function (Blueprint $table) {
            $table->unique(['country_id', 'name'], 'provinces_country_id_name_unique');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->unique(['province_id', 'name'], 'cities_province_id_name_unique');
        });

        Schema::table('regencies', function (Blueprint $table) {
            $table->unique(['city_id', 'name'], 'regencies_city_id_name_unique');
        });

        Schema::table('villages', function (Blueprint $table) {
            $table->unique(['regency_id', 'name'], 'villages_regency_id_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('villages', function (Blueprint $table) {
            $table->dropUnique('villages_regency_id_name_unique');
        });

        Schema::table('regencies', function (Blueprint $table) {
            $table->dropUnique('regencies_city_id_name_unique');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->dropUnique('cities_province_id_name_unique');
        });

        Schema::table('provinces', function (Blueprint $table) {
            $table->dropUnique('provinces_country_id_name_unique');
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropUnique('user_profiles_user_id_unique');
        });
    }
};
