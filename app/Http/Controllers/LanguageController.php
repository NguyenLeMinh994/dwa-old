<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Valuta;
use Session;
use App\Helpers\CustomerCache;

class LanguageController extends Controller
{
    //
    /*
    public function index(Request $request){
        if($request->lang <> ''){
            app()->setLocale($request->lang);
        }
        echo __('passwords.token');
    }*/

    public function postLang(Request $request)
    {
        Session::put('locale', $request->locale);
        return redirect()->back();
    }

    public function postRegion(Request $request)
    {
        $customer_case = \Auth::user()->guid; 
        $customer_setup_config = session('customer_setup_config');
        $customer_setup_config['azure_locale'] = $request->region;
        Session::put('customer_setup_config', $customer_setup_config);

        //reset cache
        $CustomerCache = new CustomerCache();
        $CustomerCache->clearAllCache($customer_case);
        return redirect()->back();
    }

    public function postCurrency(Request $request)
    {
        $customer_case = \Auth::user()->guid; 
        
        $valuta_model = new Valuta();
        $customer_setup_config = session('customer_setup_config');
        
        if ($request->currency != $customer_setup_config['currency']['currency_code']){
            $currency = $valuta_model->changeCurrentRate($request->currency);
            
            $customer_setup_config['currency']['currency_rate'] = $currency->rate;
            
            if($currency->currency_symbol != null && $currency->currency_symbol != '')
                $customer_setup_config['currency']['currency_symbol'] = $currency->currency_symbol;
            else
                $customer_setup_config['currency']['currency_symbol'] = $currency->currency_code;

            $chkresetChartImage = $this->resetChartImageBasedUID($customer_case);
        }
        $customer_setup_config['currency']['currency_code'] = $request->currency;
        Session::put('customer_setup_config', $customer_setup_config);

        //reset cache
        $CustomerCache = new CustomerCache();
        $CustomerCache->clearAllCache($customer_case);
        
        return redirect('/current-cost-structure');
    }

    private function resetChartImageBasedUID($customer_case){
        $chkresult = DB::table('powerpoint_chart_images')->where('uid', '=', $customer_case)
                                                         ->where('chart_title', '!=', 'VM Comparison')
                                                         ->where('chart_title', '!=', 'Virtual Machine Categories')
                                                         ->where('chart_title', '!=', 'CPU Benchmarks')
                                                         ->update(['image_source' => null]);
        return $chkresult;
    }
}
