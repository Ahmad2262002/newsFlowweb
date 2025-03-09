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
    Schema::create('comment', function (Blueprint $table) {
        $table->id('comment_id');
        $table->text('content');
        $table->foreignId('user_id')->constrained('user', 'user_id');
        $table->foreignId('article_id')->constrained('article', 'article_id');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment');
    }
};
