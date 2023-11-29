<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assignees', function (Blueprint $table) {
            $table->integer('resource_id')->unsigned();
            $table->integer('task_id')->unsigned();
            $table->primary(['resource_id', 'task_id']);
            $table->foreign('resource_id')->references('id')->on('resources')
                ->onDelete('cascade')
                ->onUpdate('cascade')
            ;
            $table->foreign('task_id')->references('id')->on('tasks')
                ->onDelete('cascade')
                ->onUpdate('cascade')
            ;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignees');
    }
};
