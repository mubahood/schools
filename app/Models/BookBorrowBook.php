<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookBorrowBook extends Model
{
    use HasFactory;

    protected $fillable = ['enterprise_id', 'book_borrow_id', 'returned','book_id', 'is_lost','borrowed_by'];

    function book_borrow()
    {
        return $this->belongsTo(BookBorrow::class);
    }
}
