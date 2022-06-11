<?php

namespace App\Models;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BooksCategory extends Model
{
    use HasFactory;

    use ModelTree {
        ModelTree::boot as treeBoot;
    }

    public static function sub_categories()
    {
        return BooksCategory::where(
            'enterprise_id',
            Admin::user()->enterprise_id
        )
            ->where('parent_id', '!=', 0)
            ->get();
    }

    protected $fillable = ['parent_id', 'order', 'title', 'enterprise_id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    protected static function boot()
    {
        static::treeBoot();

        static::creating(function ($model) {
            $model->enterprise_id = Admin::user()->enterprise_id;
        });
    }
}
