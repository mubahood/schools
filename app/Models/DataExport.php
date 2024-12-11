<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataExport extends Model
{
    use HasFactory;

    public function setClassesAttribute($value)
    {
        $this->attributes['classes'] = json_encode($value);
    } 


    public function getClassesAttribute($value)
    {
        return json_decode($value);
    }

    public function setUsersAttribute($value)
    {
        $this->attributes['users'] = json_encode($value);
    }

    //getter for users
    public function getUsersAttribute($value)
    {
        return json_decode($value);
    }

    //get_users
    public function get_users()
    {
        $users = [];
        $user_ids = [];
        if ($this->target_type == 'employees') {
            $user_ids = User::where('user_type', 'employee')
                ->where('enterprise_id', $this->enterprise_id) 
                ->pluck('id');
        } elseif ($this->target_type == 'classes') {
            $classes = $this->classes;
            $user_ids = [];
            foreach ($classes as $key => $v) {
                $student_ids = User::where([
                    'current_class_id' => $v, 
                ])->pluck('id')->toArray();
                $user_ids = array_merge($user_ids, $student_ids); 
            }
        } elseif ($this->target_type == 'users') {
            $user_ids = $this->users;
        }

        foreach ($user_ids as $key => $v) {
            $u = User::find($v);
            if ($u == null) continue; 
            $created = Carbon::parse($u->created_at);
            $year = $created->format('Y');
            $u->user_number = $u->ent->short_name . "-" . $year . "-" . $u->id;
            $u->qr_code =  Utils::generate_qrcode($u->user_number);
            $users[] = $u;
        }
        return $users;
    }

    //name_text
    public function name_text()
    {
        $name = '';
        if ($this->target_type == 'employees') {
            $name = 'All Employees';
        } elseif ($this->target_type == 'classes') {
            $classes = $this->classes;
            $name = '';
            foreach ($classes as $key => $v) {
                $name .= AcademicClass::find($v)->name_text . ', ';
            }
            $name = rtrim($name, ', ');
        } elseif ($this->target_type == 'users') {
            $users = $this->users;
            $name = '';
            foreach ($users as $key => $v) {
                $name .= User::find($v)->name . ', ';
            }
            $name = rtrim($name, ', ');
        }
        return $name;
    } 
}
