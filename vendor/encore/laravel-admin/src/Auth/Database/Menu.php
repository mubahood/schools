<?php

namespace Encore\Admin\Auth\Database;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

/**
 * Class Menu.
 *
 * @property int $id
 *
 * @method where($parent_id, $id)
 */
class Menu extends Model
{
    use DefaultDatetimeFormat;
    use ModelTree {
        ModelTree::boot as treeBoot;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['parent_id', 'order', 'title', 'icon', 'uri', 'permission'];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.menu_table'));

        parent::__construct($attributes);
    }

    /**
     * A Menu belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $pivotTable = config('admin.database.role_menu_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'menu_id', 'role_id');
    }

    /**
     * @return array
     */
    public function allNodes(): array
    {
        $connection = config('admin.database.connection') ?: config('database.default');
        $orderColumn = DB::connection($connection)->getQueryGrammar()->wrap($this->orderColumn);

        $byOrder = 'ROOT ASC,' . $orderColumn;

        $query = static::query();

        if (config('admin.check_menu_roles') !== false) {
            $query->with('roles');
        }

        return $query->selectRaw('*, ' . $orderColumn . ' ROOT')->orderByRaw($byOrder)->get()->toArray();
    }

    /**
     * determine if enable menu bind permission.
     *
     * @return bool
     */
    public function withPermission()
    {
        return (bool) config('admin.menu_bind_permission');
    }

    /**
     * Detach models from the relationship.
     *
     * @return void
     */
    protected static function boot()
    {
        static::treeBoot();

        static::deleting(function ($model) {
            $model->roles()->detach();
        });
    }


    /**
     * Get the access_by attribute as an array.
     *
     * @param  string|null  $value
     * @return array
     */
    public function getAccessByAttribute($value)
    {
        if (!empty($value) && is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * Set the access_by attribute from an array.
     *
     * @param  array|string|null  $value
     * @return void
     */
    public function setAccessByAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['access_by'] = json_encode($value);
        } else {
            $this->attributes['access_by'] = $value;
        }
    }
}
