<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Project Model
 *
 * @property int $id
 * @property string $name
 * @property string $identifier
 * @property string $last_visited
 * @property string $created_at
 * @property string $updated_at
 * @method static find($id)
 * @method static \Illuminate\Database\Eloquent\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class Project extends Model
{
    use HasFactory;

    protected $casts = [
        'created_at' => 'datetime:F j, Y H:i:s',
        'updated_at' => 'datetime:F j, Y H:i:s',
    ];

    /**
     * @param string $identifier
     * @param string[] $columns
     * @return Model|static|null
     */
    public static function findByIdentifier(string $identifier, array $columns = ['*']): Model|static|null
    {
        return static::where('identifier', '=', $identifier)->first($columns);
    }

    /**
     * Get the comments for the blog post.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = []): bool
    {
        if (!$this->id && !$this->identifier) {
            $this->identifier = Str::slug($this->name);
        }
        return parent::save($options);
    }
}
