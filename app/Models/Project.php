<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    public function head()
    {
        $e = Administrator::find($this->head_of_project);
        if ($e == null) {
            $this->head_of_project = 1;
            $this->save();
        }
        return $this->belongsTo(Administrator::class,'head_of_project');
    }
}
