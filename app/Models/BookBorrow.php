<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookBorrow extends Model
{

    use HasFactory;
    function book_borrow_books()
    {
        return $this->hasMany(BookBorrowBook::class);
    }
}
