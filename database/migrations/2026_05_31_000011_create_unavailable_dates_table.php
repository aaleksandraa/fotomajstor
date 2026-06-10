<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unavailable_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photographer_profile_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['photographer_profile_id', 'date'], 'unavailable_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unavailable_dates');
    }
};
