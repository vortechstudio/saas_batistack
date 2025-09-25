<?php

namespace App\Models\Product;

use App\Models\Customer\CustomerServiceModule;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Feature extends Model
{
    /** @use HasFactory<\Database\Factories\Product\FeatureFactory> */
    use HasFactory;
    protected $guarded = [];
    public $appends = ['media'];
    
    public function products()
    {
        return $this->belongsToMany(Product::class, 'feature_product');
    }

    public function customerServiceModules()
    {
        return $this->hasMany(CustomerServiceModule::class);
    }

    public function getMediaAttribute()
    {
        return Storage::url('/modules/'.$this->slug.'.png');
    }
}
