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
    Schema::create('employees', function (Blueprint $table) {
        $table->id('employee_id');
        $table->foreignId('staff_id')->constrained('staffs', 'staff_id');
        $table->string('position');
        $table->date('hire_date');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee');
    }
};
