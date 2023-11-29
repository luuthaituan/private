<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
 * @method static \Illuminate\Database\Eloquent\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class Setting extends Model
{
    use HasFactory;

    // Define scopes
    const SCOPE_DEFAULT = 'default';

    // Define config paths
    const HOLIDAYS_CONFIG_PATH = 'holidays';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'scope',
        'scope_id',
        'path',
        'value',
    ];

    /**
     * @param string $path
     * @param string $scope
     * @param int $scope_id
     * @return Model|static|null
     */
    public static function find(string $path, string $scope = self::SCOPE_DEFAULT, int $scope_id = 0): Model|static|null
    {
        return static::where('path', '=', $path)
            ->where('scope', '=', $scope)
            ->where('scope_id', '=', $scope_id)
            ->first()
        ;
    }
}
