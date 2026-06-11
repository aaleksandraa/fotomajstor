<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photographer_profiles', function (Blueprint $table) {
            $table->timestamp('onboarding_completed_at')->nullable()->after('active');
        });

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
        Schema::table('photographer_profiles', function (Blueprint $table) {
            $table->dropColumn('onboarding_completed_at');
        });
    }
};
