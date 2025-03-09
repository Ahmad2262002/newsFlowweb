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
    Schema::create('article', function (Blueprint $table) {
        $table->id('article_id');
        $table->string('title');
        $table->text('content');
        $table->string('source_name')->nullable();
        $table->timestamp('published_date');
        $table->string('author_name');
        $table->tinyInteger('status');
        $table->foreignId('employee_id')->constrained('employee', 'employee_id');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article');
    }
};
