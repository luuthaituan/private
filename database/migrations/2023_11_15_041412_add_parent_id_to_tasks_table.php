<?php

use App\Models\Task;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->truncateJiraId();

        Schema::table('tasks', function (Blueprint $table) {
            $table->date('start_date')->nullable()->default(null)->change();
            $table->integer('duration')->default(0)->change();
            $table->integer('progress')->default(0)->change();
            $table->string('jira_id')->nullable()->unique()->change();
            $table->integer('parent_id')->unsigned()->nullable();
            $table->foreign('parent_id')->references('id')->on('tasks')
                ->onDelete('cascade')
                ->onUpdate('cascade')
            ;
        });

        $this->migrate();
    }

    /**
     * Set all jira_id records to null
     */
    private function truncateJiraId()
    {
        DB::statement("UPDATE tasks SET jira_id = NULL;");
    }

    /**
     * Migrate duration and progress data to new structure
     */
    private function migrate()
    {
        DB::beginTransaction();
        try {
            $tasks = Task::with(['assignees'])->get();
            foreach ($tasks as $task) {
                $devTask = new Task([
                    'title'      => 'dev',
                    'start_date' => $task->start_date,
                    'duration'   => $task->duration,
                    'progress'   => $task->progress,
                    'project_id' => $task->project_id,
                    'parent_id'  => $task->id,
                ]);
                $devTask->save();
                foreach ($task->assignees as $resourceId) {
                    $devTask->assignees()->attach($resourceId);
                }

                $task->assignees()->detach();
                $task->duration = 0;
                $task->progress = 0;
                $task->save();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
};
