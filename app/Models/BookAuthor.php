<?php

namespace App\Models;

use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookAuthor extends Model
{
    use HasFactory;


    public static function sub_categories()
    {
        return BooksCategory::where(
            'enterprise_id',
            Admin::user()->enterprise_id
        )
            ->get();
    }
}
