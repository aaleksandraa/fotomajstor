<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $consolidate = function (string $canonicalSlug, string $duplicateSlug, string $label): void {
            $canonical = DB::table('categories')->where('slug', $canonicalSlug)->first();
            $duplicate = DB::table('categories')->where('slug', $duplicateSlug)->first();

            if (! $canonical || ! $duplicate) {
                return;
            }

            DB::table('photographer_category')->where('category_id', $duplicate->id)->orderBy('id')->get()->each(
                fn ($pivot) => DB::table('photographer_category')->insertOrIgnore([
                    'photographer_profile_id' => $pivot->photographer_profile_id,
                    'category_id' => $canonical->id,
                    'created_at' => $pivot->created_at,
                    'updated_at' => $pivot->updated_at,
                ])
            );
            DB::table('portfolio_albums')->where('category_id', $duplicate->id)->update(['category_id' => $canonical->id]);
            DB::table('photographer_category')->where('category_id', $duplicate->id)->delete();
            DB::table('category_aliases')->insertOrIgnore([
                'category_id' => $canonical->id,
                'slug' => $duplicateSlug,
                'label' => $label,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('categories')->where('id', $duplicate->id)->delete();
        };

        $consolidate('vjencanja', 'svadbe', 'Svadbe');
        $consolidate('snimanje-vjencanja', 'snimanje-svadbe', 'Snimanje svadbe');
    }

    public function down(): void
    {
        //
    }
};
