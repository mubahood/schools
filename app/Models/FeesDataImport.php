<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeesDataImport extends Model
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


    // Getter for services_columns attribute with try-catch
    public function getServicesColumnsAttribute($value)
    {
        try {
            return $value ? json_decode($value, true) : [];
        } catch (\Exception $e) {
            // Optionally log the error
            return [];
        }
    }

    // Setter for services_columns attribute with try-catch
    public function setServicesColumnsAttribute($value)
    {
        try {
            $this->attributes['services_columns'] = is_array($value) ? json_encode($value) : $value;
        } catch (\Exception $e) {
            // Optionally log the error
            $this->attributes['services_columns'] = null;
        }
    }
}
