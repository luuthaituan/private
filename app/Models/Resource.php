<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Resource Model
 *
 * @property int $id
 * @property string $account
 * @property string $name
 * @property string $email
 * @property string $avatar
 * @property string $created_at
 * @property string $updated_at
 * @method static find($id)
 * @method static \Illuminate\Database\Eloquent\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder whereNotIn($column, $values, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
 * @method static \Illuminate\Database\Eloquent\Builder leftJoin($table, $first, $operator = null, $second = null)
 * @method static bool insert(array $values)
 */
class Resource extends Model
{
    use HasFactory;

    protected $casts = [
        'created_at' => 'datetime:F j, Y H:i:s',
        'updated_at' => 'datetime:F j, Y H:i:s',
    ];
}
