<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PriceInput extends Model
{
    //
    protected $table = 'price_input';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Id', 
        'RateCardId', 
        'MeterId', 
        'MeterName', 
        'MeterCategory', 
        'MeterSubCategory', 
        'MeterTypes',
        'MeterFunction',
        'Unit', 
        'MeterRates', 
        'EffectiveDate', 
        'MeterRegion', 
        'IncludedQuantity',
        'Cores',
        'RAM',
        'Ratio',
        'Currency',
        'Cost',
        'created_at',
        'updated_at'
    ];
}
