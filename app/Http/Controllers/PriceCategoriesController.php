<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\PriceCategories;

use App\Helpers\CustomerCache;

class PriceCategoriesController extends Controller
{
    protected $currency_code;
    protected $region;
    protected $USD_EUR_rate;

    public function __construct(){}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
        $customer_case = \Auth::user()->guid; 
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        $this->region = $customer_setup_config['azure_locale'];
        $this->currency_rate    = $customer_setup_config['currency']['currency_rate'];
        $this->currency_code    = $customer_setup_config['currency']['currency_code'];
        
        $currency_code  = $this->currency_code;
        $currency_rate  = $this->currency_rate;
        $region         = $this->region;

        $categories = DB::table('view_vm_categories')->where('MeterRegion', $this->region)->groupBy('MeterTypes','OperationSystem','Id')->get();

        $chartDv3_data = DB::table('view_vm_categories')->where('MeterRegion', $this->region)->where('MeterTypes','Dv3')->where('OperationSystem','Windows')->groupBy('MeterTypes','OperationSystem','Id')->get();

        $chartEv3_data = DB::table('view_vm_categories')->where('MeterRegion', $this->region)->where('MeterTypes','Ev3')->where('OperationSystem','Windows')->groupBy('MeterTypes','OperationSystem','Id')->get();
        
        //build json 
        $chart_Dv3 = array();
        $chart_Ev3 = array();
        
        foreach ($chartDv3_data as $item)
        {
            $temp = array();
            $vm_name = substr($item->MeterSubCategory,strpos($item->MeterSubCategory,'D'),strpos($item->MeterSubCategory,' ')-strpos($item->MeterSubCategory,'D'));
            $temp['category'] =  str_replace('_',' ',$vm_name);
            $temp['cores'] = $item->Cores;
            $temp['ram'] = $item->RAM;

            //calculate the price per GB/RAM 
            $cost = 0;
            $totalHoursPerMonth = 744; 
            if($item->MeterRates != null)
            {
                $arr_rates = explode(";", $item->MeterRates);
                $rates = explode(":", (string)$arr_rates[0]);
                $cost = $rates[1];
            }
            
            $price = $cost * $totalHoursPerMonth;
            $gbRam_Price = $price / (float)$item->RAM;
        
            $temp['price'] = number_format($gbRam_Price,2);

            $chart_Dv3 [] = $temp;
        }

        foreach ($chartEv3_data as $item)
        {
            $temp = array();
            $vm_name = substr($item->MeterSubCategory,strpos($item->MeterSubCategory,'E'),strpos($item->MeterSubCategory,' ')-strpos($item->MeterSubCategory,'E'));
            $temp['category'] =  str_replace('_',' ',$vm_name);
            $temp['cores'] = $item->Cores;
            $temp['ram'] = $item->RAM;

            //calculate the price per GB/RAM 
            $cost = 0;
            $totalHoursPerMonth = 744; 
            if($item->MeterRates != null)
            {
                $arr_rates = explode(";", $item->MeterRates);
                $rates = explode(":", (string)$arr_rates[0]);
                $cost = $rates[1];
            }
            
            $price = $cost * $totalHoursPerMonth;
            $gbRam_Price = $price / (float)$item->RAM;
        
            $temp['price'] = number_format($gbRam_Price,2);

            $chart_Ev3 [] = $temp;
        }

        //dd($chart_Dv3);

        $json_Dv3_data = json_encode($chart_Dv3);

        $json_Ev3_data = json_encode($chart_Ev3);

        return view("price-categories", compact(['categories', 'region' ,'json_Dv3_data', 'json_Ev3_data', 'currency_rate', 'currency_code']));
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
        try{
        //
        PriceCategories::create($request->all());
        //return $priceCategories;
        }catch(Exception $ex){
            echo $ex->getMessage(); exit;
        }
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
