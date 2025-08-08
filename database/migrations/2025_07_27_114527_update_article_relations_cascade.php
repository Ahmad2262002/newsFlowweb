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
    // Comments table
    Schema::table('comments', function (Blueprint $table) {
        $table->dropForeign(['article_id']);
        $table->foreign('article_id')
              ->references('article_id')->on('articles')
              ->onDelete('cascade');
    });

    // Likes table
    Schema::table('likes', function (Blueprint $table) {
        $table->dropForeign(['article_id']);
        $table->foreign('article_id')
              ->references('article_id')->on('articles')
              ->onDelete('cascade');
    });

    // Shares table
    Schema::table('shares', function (Blueprint $table) {
        $table->dropForeign(['article_id']);
        $table->foreign('article_id')
              ->references('article_id')->on('articles')
              ->onDelete('cascade');
    });

    // Feedbacks table
    Schema::table('feedbacks', function (Blueprint $table) {
        $table->dropForeign(['article_id']);
        $table->foreign('article_id')
              ->references('article_id')->on('articles')
              ->onDelete('cascade');
    });

    // article_category table
    Schema::table('article_category', function (Blueprint $table) {
        $table->dropForeign(['article_id']);
        $table->foreign('article_id')
              ->references('article_id')->on('articles')
              ->onDelete('cascade');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
