<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'countries' => ['meta_title', 'meta_description', 'intro_text'],
            'cities' => ['meta_title', 'meta_description', 'intro_text'],
            'categories' => ['meta_title', 'meta_description', 'intro_text'],
            'locations' => ['meta_title', 'meta_description', 'intro_text'],
            'blog_posts' => ['meta_title', 'meta_description'],
            'photographer_blog_posts' => ['meta_title', 'meta_description'],
        ];

        foreach ($tables as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::table($table)
                    ->where(fn ($query) => $query
                        ->where($column, 'like', '%FotoMreža%')
                        ->orWhere($column, 'like', '%Foto.Mreža%')
                        ->orWhere($column, 'like', '%Foto Mreža%')
                        ->orWhere($column, 'like', '%Pronađi Fotografa%'))
                    ->update([
                        $column => DB::raw(
                            "REPLACE(REPLACE(REPLACE(REPLACE({$column}, 'FotoMreža', 'FotoMajstor'), 'Foto.Mreža', 'FotoMajstor'), 'Foto Mreža', 'FotoMajstor'), 'Pronađi Fotografa', 'FotoMajstor')"
                        ),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Rebranding existing SEO content is not safely reversible.
    }
};
