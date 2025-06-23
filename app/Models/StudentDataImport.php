<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDataImport extends Model
{
    use HasFactory;


    //creator belongs to User
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    } 

    //enterprise belongs to Enterprise
    public function enterprise()
    {
        return $this->belongsTo(Enterprise::class); 
    }

    
}
