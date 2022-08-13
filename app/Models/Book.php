<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    function author()
    {
        return $this->belongsTo(BookAuthor::class, 'book_author_id');
    }
    function category()
    {
        return $this->belongsTo(BooksCategory::class,'books_category_id');
    }
}
