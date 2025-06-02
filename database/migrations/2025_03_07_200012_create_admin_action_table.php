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
    Schema::create('admin_action', function (Blueprint $table) {
        $table->id('action_id');
        $table->string('action_type');
        $table->dateTime('action_date'); // Make sure this exists if in fillable
        $table->text('description');
        $table->foreignId('admin_id')->constrained('admins', 'admin_id'); 
        $table->foreignId('target_staff_id')->nullable()->constrained('staffs', 'staff_id'); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_action');
    }
};
