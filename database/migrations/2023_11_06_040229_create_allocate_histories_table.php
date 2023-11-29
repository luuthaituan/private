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
        Schema::create('allocate_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('resource_id')->unsigned();
            $table->integer('project_id')->unsigned();
            $table->date('from')->nullable(true);
            $table->date('to')->nullable(true);
            $table->foreign('resource_id')->references('id')->on('resources')
                ->onDelete('cascade')
                ->onUpdate('cascade')
            ;
            $table->foreign('project_id')->references('id')->on('projects')
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
        Schema::dropIfExists('allocate_histories');
    }
};
