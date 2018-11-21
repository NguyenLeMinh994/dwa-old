<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StorageCategories extends Model
{
    //
    protected $table = 'storage_categories';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Id', 
        'StorageType_Id',
        'MeterCategory', 
        'MeterSubCategory', 
        'MeterName',
        'RAM',
        'Unit',
        'MeterRates',
        'Cost',
        'created_at',
        'updated_at'
    ];
}
