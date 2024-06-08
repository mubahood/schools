<?php

namespace App\Models;

use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    use HasFactory;
    use Uuid;
    protected $keyType = 'string';

    //assigned_to
    public function assigned_to()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }
    //due_term
    public function category_data()
    {
        return $this->belongsTo(FixedAssetCategory::class, 'category');
    }

    //due_term
    public function due_term()
    {
        return $this->belongsTo(Term::class, 'due_term_id');
    }


    /*  
        $form->text('code', __('Code'));
        $form->textarea('qr_code', __('Qr code'));
        $form->textarea('barcode', __('Barcode'));
    */
    /* 
category	name	description	photo	status	purchase_date	warranty_expiry_date	maintenance_due_date	purchase_price	current_value	remarks	serial_number	code	qr_code	barcode	
Query results operations
*/

    static public function generate_code_number($category_id)
    {
        //code formart enterprise_short_name-category_code-year-number_of_asset_in_that_category
        $category = FixedAssetCategory::find($category_id);
        if ($category == null) {
            throw new \Exception('Category does not exist');
        }
        $code = $category->code;
        $year = date('Y');
        $count = FixedAsset::where('category', $category_id)
            ->count();
        $count++;
        $ent = Enterprise::find($category->enterprise_id);
        if ($ent == null) {
            throw new \Exception('Enterprise does not exist');
        }
        $short_name = $ent->short_name;

        //$short_name if less than 3 or null
        if (strlen($short_name) < 3) {
            //first 3 letters of name
            $short_name = substr($ent->name, 0, 3);
        }
        $short_name = strtoupper($short_name);

        $zeros = 4 - strlen($count);
        for ($i = 0; $i < $zeros; $i++) {
            $count = '0' . $count;
        }
        $code = $short_name . '-' . $code . '-' . $year . '-' . $count;
        return $code;
    }

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {

            $model->code = self::generate_code_number($model->category);
            //category with same name should not exist
            $existing = FixedAsset::where('code', $model->code)
                ->first();
            if ($existing) {
                throw new \Exception('Fixed Asset with same code already exists: ' . $model->code);
            }
            $model->id = $model->generateUuid();
            $current_value = (int)$model->current_value;
            if ($current_value == 0) {
                $model->current_value = $model->purchase_price;
            }
        });
        //updating
        self::updating(function ($model) {
        });

        static::deleting(function ($category) {
            throw new \Exception('Deleting not allowed');
        });

        //created
        self::created(function ($model) {
            $bar_code = Utils::generate_barcode($model->code);
            $model->barcode = $bar_code;
            $model->save();
            FixedAssetCategory::update_purchase_price($model->category);
        });

        //updated 
        self::updated(function ($model) {
            FixedAssetCategory::update_purchase_price($model->category);
        });
    }

    //getter for barccode
    public function getBarcodeAttribute($bar_code)
    {

        if ($bar_code == null || $bar_code == '' || !file_exists(public_path($bar_code))) {
            $bar_code = Utils::generate_barcode($this->code);
            $this->barcode = $bar_code;
            $this->save();
        }

        return str_replace('storage/', '', $bar_code);
    }
}
