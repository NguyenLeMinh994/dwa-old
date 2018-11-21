<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Valuta extends Model
{
    protected $table = 'currencies_rates';
    protected $primaryKey = 'id';

    public function changeCurrentRate($currencyCode)
    {
        $currencyRate = DB::table('currencies_rates')
                            ->where('currency_code', $currencyCode)
                            ->where('status', 'ACTIVED')
                            ->get();
        //dd($currencyRate);
        return $currencyRate[0]->rate;
    }
}
