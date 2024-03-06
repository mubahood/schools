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
                ->first();
            if ($existing) {
                throw new \Exception('Fixed Asset Category with same name already exists');
            }
            //unique code
            $existing = FixedAssetCategory::where('code', $model->code)
                ->where('id', '!=', $model->id)
                ->first();
            if ($existing) {
                throw new \Exception('Fixed Asset Category with same code already exists');
            }
            $code = strtoupper($model->code);
            $model->code = $code;
            $model->id = $model->generateUuid();
        });

        static::deleting(function ($category) {
            throw new \Exception('Deleting not allowed');
        });
    }
}
