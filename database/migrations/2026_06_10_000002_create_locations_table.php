<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('type')->default('city');
            $table->string('name');
            $table->string('slug');
            $table->string('region')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('intro_text')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('indexable')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['country_id', 'parent_id', 'slug'], 'locations_scope_slug_unique');
            $table->index(['country_id', 'type', 'active']);
        });

        Schema::create('photographer_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photographer_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['photographer_profile_id', 'location_id'], 'photographer_location_unique');
        });

        DB::table('cities')->orderBy('id')->get()->each(function ($city) {
            $locationId = DB::table('locations')->insertGetId([
                'country_id' => $city->country_id,
                'city_id' => $city->id,
                'type' => 'city',
                'name' => $city->name,
                'slug' => $city->slug,
                'region' => $city->region,
                'meta_title' => $city->meta_title,
                'meta_description' => $city->meta_description,
                'intro_text' => $city->intro_text,
                'active' => $city->active,
                'indexable' => DB::table('photographer_city')->where('city_id', $city->id)->exists(),
                'sort_order' => $city->sort_order,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('photographer_city')->where('city_id', $city->id)->orderBy('id')->get()->each(
                fn ($pivot) => DB::table('photographer_location')->insertOrIgnore([
                    'photographer_profile_id' => $pivot->photographer_profile_id,
                    'location_id' => $locationId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photographer_location');
        Schema::dropIfExists('locations');
    }
};
