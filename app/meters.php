<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class meters extends Model
{
    //
    protected $fillable = [
        'RateCardId', 
        'MeterId', 
        'MeterName', 
        'MeterCategory', 
        'MeterSubCategory', 
        'Unit', 
        'MeterRates', 
        'EffectiveDate', 
        'MeterRegion', 
        'IncludedQuantity'
    ];
}
