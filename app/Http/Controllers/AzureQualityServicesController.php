<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Helpers\CustomerCache;
use App\Valuta;
use App\AzureQualityServices;

class AzureQualityServicesController extends Controller
{
    protected $survey_info;
    
    public function __construct(){}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //survey case
        $customer_case = \Auth::user()->guid; 
        $survey_info = \Cache::get('survey-info_'.$customer_case); //dd($survey_info);
    
        $azure_Quality = new AzureQualityServices();
        $quality_of_services = $azure_Quality->Quality_Of_Services_Aspects($survey_info);

        $customer_currency_code = $survey_info['CUSTOMER_CURRENCY']->answer;
        $customer_currency_rate = 1;

        $valuta_model = new Valuta();
        $currency = $valuta_model->changeCurrentRate($survey_info['CUSTOMER_CURRENCY']->answer);
        $customer_currency_rate = $currency->rate;
        //return to view
        return view("azure-quality-services", compact(['quality_of_services', 'customer_currency_code', 'customer_currency_rate']));
    }
}
