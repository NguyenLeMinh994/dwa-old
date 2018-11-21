<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AsrCategories extends Model
{
    //
    protected $table = 'asr_categories';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Id', 
        'MeterCategory', 
        'MeterSubCategory', 
        'MeterName',
        'MeterRates',
        'Cost',
        'Unit',
        'created_at',
        'updated_at'
    ];
}
