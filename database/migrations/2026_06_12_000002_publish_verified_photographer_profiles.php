<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('photographer_profiles')
            ->whereIn('user_id', DB::table('users')
                ->select('id')
                ->where('role', 'photographer')
                ->whereNotNull('email_verified_at'))
            ->update([
                'active' => true,
                'verified' => true,
            ]);
    }

    public function down(): void
    {
        // Publishing a verified profile is not safely reversible.
    }
};
