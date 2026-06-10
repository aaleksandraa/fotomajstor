<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photographer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('profile_type')->default('individual');
            $table->string('service_type')->default('photographer');
            $table->string('display_name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_tax_number')->nullable();
            $table->string('slug')->unique();
            $table->string('profile_image')->nullable();
            $table->string('cover_image')->nullable();
            $table->text('about')->nullable();
            $table->integer('experience_years')->nullable();
            $table->string('phone')->nullable();
            $table->string('secondary_phone')->nullable();
            $table->string('public_email')->nullable();
            $table->string('website')->nullable();
            $table->foreignId('primary_country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('primary_city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->boolean('verified')->default(false);
            $table->boolean('active')->default(false);
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('profile_views')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photographer_profiles');
    }
};
