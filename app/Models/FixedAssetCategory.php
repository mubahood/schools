<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedAssetCategory extends Model
{
    use HasFactory;

    use Uuid;
    protected $keyType = 'string';

    //creating 
    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            //category with same name should not exist
            $existing = FixedAssetCategory::where('name', $model->name)
                ->first();
            if ($existing) {
                throw new \Exception('Fixed Asset Category with same name already exists');
            }
            $existing = FixedAssetCategory::where('code', $model->code)
                ->where('id', '!=', $model->id)
                ->where('enterprise_id', $model->enterprise_id) 
                ->first();
            if ($existing) {
                throw new \Exception('Fixed Asset Category with same code already exists');
            }
            $code = strtoupper($model->code);
            $model->code = $code;
            $model->purchase_price = 0;
            $model->current_value = 0;
            $model->id = $model->generateUuid();
        });
        //updating
        self::updating(function ($model) {
            //category with same name should not exist
            $existing = FixedAssetCategory::where('name', $model->name)
                ->where('id', '!=', $model->id)
                ->where('enterprise_id', $model->enterprise_id)
                ->first();
            if ($existing) {
                throw new \Exception('Fixed Asset Category with same name already exists');
            }
            //unique code
            $existing = FixedAssetCategory::where('code', $model->code)
                ->where('id', '!=', $model->id)
                ->where('enterprise_id', $model->enterprise_id)
                ->first();
            if ($existing) {
                throw new \Exception('Fixed Asset Category with same code already exists');
            }
            $code = strtoupper($model->code);
            $model->code = $code;
        });

        static::deleting(function ($category) {
            throw new \Exception('Deleting not allowed');
        });
    }


    public static function update_purchase_price($category_id)
    {
        try {
            $category = FixedAssetCategory::find($category_id);
            if ($category == null) {
                return;
            }
            //purchase_price sum of all active assets 
            $purchase_price = FixedAsset::where('category', $category_id)
                ->where('status', 'Active')
                ->sum('purchase_price');
            $current_value = FixedAsset::where('category', $category_id)
                ->where('status', 'Active')
                ->sum('current_value');
            $category->purchase_price = $purchase_price;
            $category->current_value = $current_value;
            $category->save();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
