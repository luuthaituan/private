<?php

namespace App\Models;

use App\Services\CalculateToDateService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Task Model
 *
 * @property int $id
 * @property string $title
 * @property int $duration
 * @property string $jira_id
 * @property float $progress
 * @property string $start_date
 * @property int $is_renewed_plan
 * @property int $project_id
 * @property string $created_at
 * @property string $updated_at
 * @method static static find($id)
 * @method static \Illuminate\Support\Collection get($columns = ['*'])
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder whereNull($columns, $boolean = 'and', $not = false)
 */
class Task extends Model
{
    use HasFactory;

    // Task mark as done statuses
    const DONE_STATUSES = [
        'Closed',
        'Resolved',
        'Done',
    ];

    const TASK_TYPES = [
        'conduct_brd' => 'Conduct BRD',
        'dev' => 'Develop',
        'test' => 'Test',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime:F j, Y H:i:s',
        'updated_at' => 'datetime:F j, Y H:i:s',
        'start_date' => 'date:Y-m-d',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'title',
        'jira_id',
        'start_date',
        'duration',
        'progress',
        'project_id',
        'parent_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'end_date'
    ];

    /**
     * Add project to filter tasks
     *
     * @param $project
     * @return Builder
     */
    public static function addProjectFilter($project): Builder
    {
        $projectId = $project;
        if ($project instanceof Project) {
            $projectId = $project->id;
        }

        return static::where('project_id', '=', $projectId);
    }

    /**
     * Get the assignees for the task.
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'assignees');
    }

    /**
     * Get children tasks
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id', 'id');
    }

    /**
     * Get parent task
     *
     * @return HasOne
     */
    public function parent(): HasOne
    {
        return $this->hasOne(Task::class, 'id', 'parent_id');
    }

    /**
     * Get project which task assigned
     *
     * @return HasOne
     */
    public function project(): HasOne
    {
        return $this->hasOne(Project::class, 'id', 'project_id');
    }

    /**
     * Get end date of task
     *
     * @return ?string
     * @throws BindingResolutionException
     */
    public function getEndDateAttribute(): ?string
    {
        if ($this->start_date && $this->duration) {
            $calculateService = app()->make(CalculateToDateService::class);
            return $calculateService->calculateToDate($this->start_date, $this->duration);
        }

        return null;
    }
}
