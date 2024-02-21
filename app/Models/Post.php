<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    //has many post views
    public function views()
    {
        return $this->hasMany(PostView::class, 'post_id');
    }
}
