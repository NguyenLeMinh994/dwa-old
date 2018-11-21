<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\DashboardCalculation;
use App\CurrentCostStructure;
use App\CostComparison;
use App\Helpers\CustomerCache;

class CurrentCostStructureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    protected $survey_info;
    protected $currency_code;
    protected $region;
    protected $USD_EUR_rate;

    public function __construct(){}
    
    public function index()
    {
        $customer_case = \Auth::user()->guid; 
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        $this->region = $customer_setup_config['azure_locale'];

        $this->currency_rate = $customer_setup_config['currency']['currency_rate'];
        $this->currency_code = $customer_setup_config['currency']['currency_code'];
        
        $survey_info    = $this->survey_info;
        $region         = $this->region;
        $currency_code  = $this->currency_code;
        $currency_rate  = $this->currency_rate;
        
        $modelCurrentCostStructure          = new CurrentCostStructure($survey_info);
        $summaryOfTheInputs                 = $modelCurrentCostStructure->SummaryOfTheInputs($survey_info, null);
        //$compute_original_input_ratio = $modelCurrentCostStructure->ComputeOriginalInputRatio($survey_info);
        
        //get values from Calculation Part
        $dwaCalculation                     = new DashboardCalculation();
        //$memory_optimized_vms = $dwaCalculation->Calculation_Memory_Optimized_VMs($survey_info);
        $memory_optimized_correction_vms = $dwaCalculation->Calculation_Correction_Mem_Optimised($survey_info);
        $calculation_total_over_aged        = $dwaCalculation->Calculation_Total_Over_Aged($survey_info);
        $premise_costs                      = $dwaCalculation->Premise_Costs($survey_info, null);
        $customer_windows_vs_linux_split    = $dwaCalculation->Customer_Windows_vs_Linux_Split($survey_info);
        
        //chart 11 data
        $chart11 = array();
        $chart11[] = array('customer_cost'=>"General Purpose VMs", 'value'=>$memory_optimized_correction_vms['percentage_compute']['GP'], 'color'=>'#67b6dc');
        $chart11[] = array('customer_cost'=>"Memory Optimised VMs", 'value'=>$memory_optimized_correction_vms['percentage_compute']['MO'], 'color'=>'#fdd400');
        $chart11_json = json_encode($chart11);

        $costComparisonModel = new CostComparison();
        $costComparison = $costComparisonModel->CostComparisonCalculation($survey_info);

        //chart 7 data
        $chart7 = array();
        $chart7[0]['categories'] = "Customer Cost Structure";
        $chart7[0]['network']                           = $costComparison['network']['customer_cost_structure']*100;
        $chart7[0]['co-location']                       = $costComparison['co_location']['customer_cost_structure']*100;
        $chart7[0]['total_all-in_FTE_costs_per_month']  = $costComparison['total_all_in_FTE_costs_per_month']['customer_cost_structure']*100;
        $chart7[0]['primary_storage']                   = $costComparison['primary_storage']['customer_cost_structure']*100;
        $chart7[0]['auxiliary_storage']                 = $costComparison['auxiliary_storage']['customer_cost_structure']*100;
        $chart7[0]['general_purpose_VMs']               = $costComparison['general_purpose_VMs']['customer_cost_structure']*100;
        $chart7[0]['memory_optimised_VMs']              = $costComparison['memory_optimised_VMs']['customer_cost_structure']*100;

        $chart7[0]['compute_optimised_VMs']             = $costComparison['compute_optimised_VMs']['customer_cost_structure']*100;
        $chart7[0]['high_performance_VMs']              = $costComparison['high_performance_VMs']['customer_cost_structure']*100;
        $chart7[0]['gpu_VMs']                           = $costComparison['gpu_VMs']['customer_cost_structure']*100;
        $chart7[0]['winOS_&_HypVisor_licenses']         = $costComparison['winos_hypvisor_licenses']['customer_cost_structure']*100;
        $chart7[0]['linux_licenses']                    = $costComparison['linux_licenses']['customer_cost_structure']*100;

        //Benchmark
        $chart7[1]['categories']                        = "Benchmark Cost Structure";
        $chart7[1]['network']                           = $costComparison['network']['benchmark_percentage']*100;
        $chart7[1]['co-location']                       = $costComparison['co_location']['benchmark_percentage']*100;
        $chart7[1]['total_all-in_FTE_costs_per_month']  = $costComparison['total_all_in_FTE_costs_per_month']['benchmark_percentage']*100;
        $chart7[1]['primary_storage']                   = $costComparison['primary_storage']['benchmark_percentage']*100;
        $chart7[1]['auxiliary_storage']                 = $costComparison['auxiliary_storage']['benchmark_percentage']*100;
        $chart7[1]['general_purpose_VMs']               = $costComparison['general_purpose_VMs']['benchmark_percentage']*100;
        $chart7[1]['memory_optimised_VMs']              = $costComparison['memory_optimised_VMs']['benchmark_percentage']*100;

        $chart7[1]['compute_optimised_VMs']             = $costComparison['compute_optimised_VMs']['benchmark_percentage']*100;
        $chart7[1]['high_performance_VMs']              = $costComparison['high_performance_VMs']['benchmark_percentage']*100;
        $chart7[1]['gpu_VMs']                           = $costComparison['gpu_VMs']['benchmark_percentage']*100;
        $chart7[1]['winOS_&_HypVisor_licenses']         = $costComparison['winos_hypvisor_licenses']['benchmark_percentage']*100;
        $chart7[1]['linux_licenses']                    = $costComparison['linux_licenses']['benchmark_percentage']*100;

        $chart7_json = json_encode($chart7);
        
        //chart 14 data
        $chart14 = array();
        if((float)$costComparison['linux_licenses']['monthly_infrastructure_related_costs'] > 0)
            $chart14[] = array('customer_cost'=>"Linux licenses", 'value'=>((float)$costComparison['linux_licenses']['monthly_infrastructure_related_costs']*$this->currency_rate), 'color'=>'#448e4d');
        
        if((float)$costComparison['winos_hypvisor_licenses']['monthly_infrastructure_related_costs'] > 0)
            $chart14[] = array('customer_cost'=>"WinOS & HypVisor licenses", 'value'=>((float)$costComparison['winos_hypvisor_licenses']['monthly_infrastructure_related_costs']*$this->currency_rate), 'color'=>'#2f4074');
        
        if((float)$costComparison['gpu_VMs']['monthly_infrastructure_related_costs'] > 0)
            $chart14[] = array('customer_cost'=>"GPU VM's", 'value'=>((float)$costComparison['gpu_VMs']['monthly_infrastructure_related_costs']*$this->currency_rate), 'color'=>'#84b761');
        
        if((float)$costComparison['high_performance_VMs']['monthly_infrastructure_related_costs'] > 0)
            $chart14[] = array('customer_cost'=>"High Performance VM's", 'value'=>((float)$costComparison['high_performance_VMs']['monthly_infrastructure_related_costs']*$this->currency_rate), 'color'=>'#cc4748');
        
        if((float)$costComparison['compute_optimised_VMs']['monthly_infrastructure_related_costs'] > 0)
            $chart14[] = array('customer_cost'=>"Compute Optimised VM's", 'value'=>((float)$costComparison['compute_optimised_VMs']['monthly_infrastructure_related_costs']*$this->currency_rate), 'color'=>'#cd82ad');
        
        if((float)$costComparison['memory_optimised_VMs']['monthly_infrastructure_related_costs'] > 0)
            $chart14[] = array('customer_cost'=>"Memory Optimised VM's", 'value'=>((float)$costComparison['memory_optimised_VMs']['monthly_infrastructure_related_costs']*$this->currency_rate), 'color'=>'#fdd400');
        
        if((float)$costComparison['general_purpose_VMs']['monthly_infrastructure_related_costs'] > 0)
            $chart14[] = array('customer_cost'=>"General Purpose VM's", 'value'=>((float)$costComparison['general_purpose_VMs']['monthly_infrastructure_related_costs']*$this->currency_rate), 'color'=>'#67b6dc');
        
        if((float)$costComparison['auxiliary_storage']['monthly_infrastructure_related_costs'] > 0)
            $chart14[] = array('customer_cost'=>'Auxiliary Storage', 'value'=>((float)$costComparison['auxiliary_storage']['monthly_infrastructure_related_costs']*$this->currency_rate), 'color'=>'#b7b83f');
        
        if((float)$costComparison['primary_storage']['monthly_infrastructure_related_costs'] > 0)
            $chart14[] = array('customer_cost'=>'Primary Storage', 'value'=>((float)$costComparison['primary_storage']['monthly_infrastructure_related_costs']*$this->currency_rate), 'color'=>'#b9783f');
        
        if((float)$costComparison['total_all_in_FTE_costs_per_month']['monthly_infrastructure_related_costs'] > 0)
            $chart14[] = array('customer_cost'=>'Total all-in FTE costs per month', 'value'=>((float)$costComparison['total_all_in_FTE_costs_per_month']['monthly_infrastructure_related_costs']*$this->currency_rate), 'color'=>'#b93e3d');
        
        if((float)$costComparison['co_location']['monthly_infrastructure_related_costs'] > 0)
            $chart14[] = array('customer_cost'=>'Co-location', 'value'=>((float)$costComparison['co_location']['monthly_infrastructure_related_costs']*$this->currency_rate), 'color'=>'#913167');
        
        if((float)$costComparison['network']['monthly_infrastructure_related_costs'] > 0)
            $chart14[] = array('customer_cost'=>'Network', 'value'=>((float)$costComparison['network']['monthly_infrastructure_related_costs']*$this->currency_rate), 'color'=>'#2ad6ac');
        
        $chart14_json = json_encode($chart14);

        $chart1 = array();

        $chart1[] = array('customer_cost'=>"Windows", 'value'=>(float)($customer_windows_vs_linux_split['average']['windows']*100),'color'=>'#67b6dc');
        $chart1[] = array('customer_cost'=>"Linux", 'value'=>(float)($customer_windows_vs_linux_split['average']['linux']*100),'color'=>'#fdd400');
        $chart1_json = json_encode($chart1);
        
        //vm-comparison
        $conditions = array(
            $region,
            '',
            $currency_code,
            $currency_rate
        );
        
        $comparison = DB::select("CALL procedure_vm_comparison_v2(?,?,?,?)", $conditions);
        $json_comparison_data = json_encode($comparison);

        //vm-categories
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

            $totalHoursPerMonth = 744; 
            $price = $item->Cost * $totalHoursPerMonth;
            $gbRam_Price = $price / (float)$item->RAM;

            $temp['price'] = number_format($gbRam_Price * $this->currency_rate, 2);

            $chart_Dv3 [] = $temp;
        }

        foreach ($chartEv3_data as $item)
        {
            $temp = array();
            $vm_name = substr($item->MeterSubCategory,strpos($item->MeterSubCategory,'E'),strpos($item->MeterSubCategory,' ')-strpos($item->MeterSubCategory,'E'));
            $temp['category'] =  str_replace('_',' ',$vm_name);
            $temp['cores'] = $item->Cores;
            $temp['ram'] = $item->RAM;

            $totalHoursPerMonth = 744;             
            $price = $item->Cost * $totalHoursPerMonth;
            $gbRam_Price = $price / (float)$item->RAM;

            $temp['price'] = number_format($gbRam_Price * $this->currency_rate, 2);

            $chart_Ev3 [] = $temp;
        }

        $json_Dv3_data = json_encode($chart_Dv3);
        $json_Ev3_data = json_encode($chart_Ev3);

        //return to view
        return view("current-cost-structure", compact([
                                                'survey_info',
                                                'currency_code',
                                                'currency_rate',
                                                'region',
                                                'summaryOfTheInputs',
                                                'memory_optimized_correction_vms',
                                                'calculation_total_over_aged', 
                                                'premise_costs',
                                                'chart1_json',
                                                'chart14_json', 
                                                'chart7_json', 
                                                'chart11_json',
                                                'json_comparison_data',
                                                'json_Dv3_data',
                                                'json_Ev3_data']));
    }
}
