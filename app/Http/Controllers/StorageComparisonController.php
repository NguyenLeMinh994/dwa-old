<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\PriceCategories;

class StorageComparisonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //filter data value
        $regions = DB::table('meters')->select('MeterRegion')->where('MeterRegion', '<>', '')->groupBy('MeterRegion')->get();
        $currencies = DB::table('currencies')->select(array('title', 'code'))->orderBy('title')->get();
        $exchange_rates = DB::table('currencies_rates')->select(array('currency_code', 'currency_name', 'rate'))->orderBy('currency_code')->get()->keyBy('currency_code');
        
        //default parameters
        $currency_code = 'USD';
        $exchange_rate = 1;
        $region = 'EU West';
        $vm_type = '';

        if(request()->has('MeterRegion'))
            $region = request('MeterRegion');
        if(request()->has('VMTypes') && request('VMTypes') != 'all')
            $vm_type = request('VMTypes');    

        if(request()->has('MeterCurrency') && request('MeterCurrency') != ''){
            if(request('MeterCurrency') != 'USD'){
                $currency_code = request('MeterCurrency');
                $exchange_item = $exchange_rates[$currency_code];
                $exchange_rate = $exchange_item->rate;
            }
        }

        $conditions = array(
            $region,
            $vm_type,
            $currency_code,
            $exchange_rate
        );
        
        //$comparison = DB::select('CALL procedure_vm_comparison_v2');
        $comparison = DB::select("CALL procedure_vm_comparison_v2(?,?,?,?)", $conditions);
        $json_comparison_data = json_encode($comparison);
        
        $categories = new PriceCategories;
        $meterTypes = $categories->select(array('MeterTypes'))->groupBy('MeterTypes','MeterFunction')->get();
        
        //return to view
        return view("storage-comparison", compact(['comparison', 'regions', 'currencies', 'exchange_rates', 'meterTypes', 'json_comparison_data']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        PriceCategories::create($request->all());
        //return $priceCategories;
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        return PriceCategories::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
        
        $priceCategories = PriceCategories::findOrFail($request->category_id);
        $priceCategories->update($request->all());

        //return $priceCategories;
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
        $priceCategories = PriceCategories::findOrFail($request->category_id);
        $priceCategories->delete();
        //return '';
        return back();
    }
}
