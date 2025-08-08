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
    Schema::table('articles', function (Blueprint $table) {
        $table->dropForeign(['employee_id']); // Drop existing constraint
        $table->foreign('employee_id')
              ->references('employee_id')->on('employees')
              ->onDelete('cascade'); // Add new constraint with cascade
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('articles', function (Blueprint $table) {
        $table->dropForeign(['employee_id']);
        // Recreate original constraint without cascade if needed
    });
}
};
