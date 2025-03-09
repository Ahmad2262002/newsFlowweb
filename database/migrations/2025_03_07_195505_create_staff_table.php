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
    Schema::create('staff', function (Blueprint $table) {
        $table->id('staff_id');
        $table->foreignId('role_id')->constrained('role', 'role_id');
        $table->string('username');
        $table->string('email');
        $table->string('password_hash');
        $table->boolean('is_locked');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
