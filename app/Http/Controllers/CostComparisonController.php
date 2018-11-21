<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\CostComparison;

use App\Helpers\CustomerCache;

class CostComparisonController extends Controller
{
    public function __construct(){
    }
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
        
        $survey_info    = $this->survey_info;
        $currency_rate    = $customer_setup_config['currency']['currency_rate'];
        $currency_code    = $customer_setup_config['currency']['currency_code'];

        $customer_case_id = $survey_info['case_id']; //dd($survey_info);

        $costComparisonModel = new CostComparison();
        $cost_comparison = $costComparisonModel->CostComparisonCalculation($survey_info); //dd($cost_comparison);
        //return to view
        return view("cost-comparison", compact(['survey_info', 'cost_comparison', 'currency_code', 'currency_rate']));
    }
}
