<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;


    public function get_review_status()
    {
        if ($this->review_status == 1) {
            return '<span class="badge" style="background-color: red;">Done</span>';
        } else if ($this->review_status == 2) {
            return '<span class="badge" style="background-color: yellow; color: black;">Partially Done</span>';
        } else if ($this->review_status == 3) {
            return '<span class="badge" style="background-color: red;">Not Done</span>';
        }
        return '<span class="badge" >Pending</span>';
    }

    public function get_status()
    {
        if ($this->submision_status == 0) {
            if (Carbon::parse($this->submit_before)) {
                return '<span class="badge" style="background-color: red;">Missed</span>';
            } else {
                return '<span class="badge" style="background-color: yellow; color: black;">Not submitted</span>';
            }
        } else {
            if ($this->submision_status != 1) {
                return '<span class="badge" style="background-color: green;">Submitted</span>';
            } else {
                return '<span class="badge" style="background-color: yellow; color: black;">Submitted late</span>';
            }
        }
        return '<span class="badge" >Pending</span>';
    }

    public function assignedTo()
    {
        return $this->belongsTo(Administrator::class, 'assigned_to');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(Administrator::class, 'assigned_by');
    }
}
