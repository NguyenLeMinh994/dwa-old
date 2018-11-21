<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StorageTypes extends Model
{
    //
    protected $table = 'storage_types';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Id', 
        'type_name', 
        'created_at',
        'updated_at'
    ];
}
