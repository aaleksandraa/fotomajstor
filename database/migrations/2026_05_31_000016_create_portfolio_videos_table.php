<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_album_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('url');
            $table->string('provider', 20);
            $table->string('provider_video_id', 80);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_videos');
    }
};
