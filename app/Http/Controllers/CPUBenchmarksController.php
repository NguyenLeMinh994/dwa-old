<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Helpers\CustomerCache;

use App\CpuBenchmarks;
use App\AzureBenefit;

class CPUBenchmarksController extends Controller
{
    protected $survey_info;
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
        $survey_info   = \Cache::get('survey-info_'.$customer_case);
        
        $cpuBenchmarks = new CpuBenchmarks();
        $cpuBenchmarks_data = $cpuBenchmarks->Processor_Capacity_Compare($survey_info);
        
        $average_customer_benchmark = $cpuBenchmarks_data['average_customer_benchmark'];
        unset($cpuBenchmarks_data['average_customer_benchmark']);
        unset($cpuBenchmarks_data['relative_improve']);

        $chart_cpuBenchmarks = array();

        $count_row = 0;
        foreach ($cpuBenchmarks_data as $item){
            $chart_cpuBenchmarks[$count_row]['name'] = $item['name'];
            $chart_cpuBenchmarks[$count_row]['value'] = $item['benchmarks'];
            
            switch($count_row){
                case '0':
                    $chart_cpuBenchmarks[$count_row]['color']= '#67b7dc';
                    break;
                case '1':
                    $chart_cpuBenchmarks[$count_row]['color']= '#84b761';
                    break;
                case '2':
                    $chart_cpuBenchmarks[$count_row]['color']= '#fdd400';
                    break;
                case '3':
                    $chart_cpuBenchmarks[$count_row]['color']= '#cc4748';
                    break;
            }
            $count_row++;
        }

        $json_cpuBenchmarks = json_encode($chart_cpuBenchmarks);
        
        $azureBenefit = new AzureBenefit();
        $trimming_benefits_by_optimization_vms_sizes = $azureBenefit->Trimming_Benefits_By_Optimization_VMs_Sizes($survey_info);

        return view('cpu-benchmarks', compact(['cpuBenchmarks_data', 'average_customer_benchmark', 'trimming_benefits_by_optimization_vms_sizes', 'json_cpuBenchmarks']));
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
