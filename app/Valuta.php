<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Valuta extends Model
{
    protected $table = 'currencies_rates';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id', 
        'currency_code',
        'currency_symbol', 
        'currency_name', 
        'rate',
        'status',
        'created_at',
        'updated_at'
    ];

    // public function changeCurrentRate($currencyCode)
    // {
    //     $currencyRate = DB::table('currencies_rates')
    //                         ->where('currency_code', $currencyCode)
    //                         ->where('status', 'ACTIVED')
    //                         ->get();
    //     //dd($currencyRate);
    //     if(isset($currencyRate[0]->rate))
    //         return $currencyRate[0]->rate;
    //     else
    //         abort(406, 'Sorry, customer currency '.$currencyCode.' is not supported. Please go back to portal to recheck again');
    // }

    public function changeCurrentRate($currencyCode)
    {
        $currency = DB::table('currencies_rates')
                            ->where('currency_code', $currencyCode)
                            ->where('status', 'ACTIVED')
                            ->first();
        //dd($currencyRate);
        if(isset($currency->rate))
            return $currency;
        else
            abort(406, 'Sorry, customer currency '.$currencyCode.' is not supported. Please go back to portal to recheck again');
    }
}
