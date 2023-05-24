<?php

namespace App\Models;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierProduct extends Model
{
    use HasFactory;


    public static function get_items($u){
        $items = [];
        foreach (SupplierProduct::where('enterprise_id',$u->enterprise_id)
        ->orderBy('name','asc')
        ->get() as $key => $v) {
            $items[$v->id] = $v->name.", PRICE: ".$v->price." - By ".$v->supplier->name;
        }
        return $items;
    }
    public function getImagesAttribute($pictures)
    {
        if ($pictures != null)
            return json_decode($pictures, true);
    }


    public function supplier(){
        return $this->belongsTo(Administrator::class,'administrator_id');
    }
    public function setImagesAttribute($pictures)
    {
        if (is_array($pictures)) {
            $this->attributes['images'] = json_encode($pictures);
        }
    }




}
