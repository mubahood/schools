<?php

namespace App\Models;

use Carbon\Carbon;
use Dompdf\Positioner\Fixed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixedAssetRecord extends Model
{
    use HasFactory;

    //boot
    public static function boot()
    {
        parent::boot();
        self::deleting(function ($m) {
        });
        self::creating(function ($m) {
            $m = self::prepare($m);
            return $m;
        });
        self::updating(function ($m) {
            $m = self::prepare($m);
            return $m;
        });

        //created
        self::created(function ($m) {
            $asset = FixedAsset::find($m->fixed_asset_id);
            $asset->current_value = $m->current_value;
            $asset->status = $m->status;
            $asset->last_seen = Carbon::now();
            $asset->save();
        });

        //updated
        self::updated(function ($m) {
            $asset = FixedAsset::find($m->fixed_asset_id);
            $asset->current_value = $m->current_value;
            $asset->status = $m->status;
            $asset->last_seen = Carbon::now();
            $asset->save();
        });
    }

    //static prepare
    public static function prepare($model)
    {
        $asset = FixedAsset::find($model->fixed_asset_id);
        if ($asset == null) {
            throw new \Exception("Asset not found.", 1);
        }
        //get difference
        $diff = $model->current_value - $asset->current_value;
        $model->amount = $diff;
        $model->type = $diff > 0 ? 'Appreciation' : 'Depreciation';
        //valid status
        $statuses = [
            'Active',
            'Disposed',
            'Lost',
            'Damaged',
        ];
        if (!in_array($model->status, $statuses)) {
            throw new \Exception("Invalid status.", 1);
        }
        return $model;
    }

    //belongs to fixed asset
    public function fixed_asset()
    {
        return $this->belongsTo(FixedAsset::class);
    }
}
