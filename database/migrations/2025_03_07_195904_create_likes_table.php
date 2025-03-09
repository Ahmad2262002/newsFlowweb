<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('likes', function (Blueprint $table) {
        $table->id('like_id');
        $table->foreignId('user_id')->constrained('users', 'user_id');
        $table->foreignId('article_id')->constrained('articles', 'article_id');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('like');
    }
};
