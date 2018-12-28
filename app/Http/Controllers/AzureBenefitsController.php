<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Helpers\CustomerCache;

use App\AzureBenefit;
use App\CpuBenchmarks;
use App\AzureCostComparison;
use App\DashboardCalculation;
use App\dwa_reserved_instance_allocation;
use App\dwa_pricing_variables_input;

class AzureBenefitsController extends Controller
{
    protected $survey_info;
    protected $currency_code;
    protected $region;
    protected $currency_rate;

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
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        
        $this->region           = $customer_setup_config['azure_locale'];
        $this->currency_rate    = $customer_setup_config['currency']['currency_rate'];
        $this->currency_code    = $customer_setup_config['currency']['currency_code'];
        $this->currency_symbol  = $customer_setup_config['currency']['currency_symbol'];

        $survey_info            = $this->survey_info;
        $region                 = $this->region;
        $currency_code          = $this->currency_code;
        $currency_symbol        = $this->currency_symbol;
        $currency_rate          = $this->currency_rate;
        $customer_case_id       = $survey_info['case_id']; //dd($survey_info);

        $azureBenefit = new AzureBenefit();
        $optimising_the_storage_usage_when_migrating_to_azure                   = $azureBenefit->Optimising_Storage_Usage_When_Migrating_To_Azure($this->survey_info);
        $trimming_benefits_of_switching_on_off_vms                              = $azureBenefit->Trimming_The_Benefits_Of_Switching_On_Off_VMs($this->survey_info);
        $trimming_benefits_by_optimization_vms_sizes                            = $azureBenefit->Trimming_Benefits_By_Optimization_VMs_Sizes($this->survey_info);
        
        $allocation_of_reserved_instances                                       = $azureBenefit->Allocation_Of_Reserved_Instances($this->survey_info); //dd($allocation_of_reserved_instances);
        $end_customer_pricing_variables                                         = $azureBenefit->end_customer_pricing_variables($this->survey_info);

        $pre_payment_reserved_instances                                         = $azureBenefit->Pre_Payment_Reserved_Instances($this->survey_info, $this->region);

        $partner_margin_after_apply_RI_benefits                                 = $azureBenefit->partner_margin_after_apply_RI_benefits($this->survey_info, $this->region);
        $partner_margin_after_apply_all_benefits                                = $azureBenefit->partner_margin_after_apply_all_benefits($this->survey_info, $this->region);
        $partner_margin_after_apply_switching_optimization                      = $azureBenefit->partner_margin_after_apply_switching_optimization($this->survey_info, $this->region);        

        $benefitData = array();
        $benefitData['optimising_the_storage_usage_when_migrating_to_azure']    = $optimising_the_storage_usage_when_migrating_to_azure;
        $benefitData['trimming_benefits_of_switching_on_off_vms']               = $trimming_benefits_of_switching_on_off_vms;
        $benefitData['trimming_benefits_by_optimization_vms_sizes']             = $trimming_benefits_by_optimization_vms_sizes;
        $benefitData['allocation_of_reserved_instances']                        = $allocation_of_reserved_instances;
        $benefitData['end_customer_pricing_variables']                          = $end_customer_pricing_variables; //dd($end_customer_pricing_variables);
        $benefitData['pre_payment_reserved_instances']                          = $pre_payment_reserved_instances;
        $benefitData['partner_margin_after_apply_RI_benefits']                  = $partner_margin_after_apply_RI_benefits;
        $benefitData['partner_margin_after_apply_all_benefits']                 = $partner_margin_after_apply_all_benefits;
        $benefitData['partner_margin_after_apply_switching_optimization']       = $partner_margin_after_apply_switching_optimization;

        //chart data
        $dwaCalculationModel = new DashboardCalculation();
        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $dwaCalculationModel->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($this->survey_info, $this->region);
        //dd($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost);
        $chartData = $this->updateChartData($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost);
        
        //return to view
        $return_data = array();
        $return_data['chart_data'] = $chartData;
        return view("azure-benefits", compact(['survey_info', 'customer_case_id', 'currency_rate', 'currency_code', 'currency_symbol', 'benefitData', 'return_data']));
    }

    private function updateChartData($chartData)
    {
        $customer_case = \Auth::user()->guid; 
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);

        $customer_setup_config = session('customer_setup_config');
        $currency_rate  = $customer_setup_config['currency']['currency_rate'];

        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $chartData;
        
        $chart13 = array();
        $chart13[] = array(
            'cost_type' =>'Total Customer Cost', 
            'value'     =>((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['customer_cost'] * $currency_rate),
            'color'     =>'#ff6600'
        );
    
        $chart13[] = array(
            'cost_type' =>'Azure Net Total Cost', 
            'value'     =>((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['azure_base_cost'] * $currency_rate),
            'color'     =>'#0080ff'
        );
    
        $chart13[] = array(
            'cost_type' =>'Total Cost By Actively Switching On/Off', 
            'value'     =>((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['benefit_by_paying_real_usage_per_hour'] * $currency_rate),
            'color'     =>'#57abff'
        );
        $chart13_json = json_encode($chart13);
    
        //CHART 3
        $chart3 = array();
        $chart3[] = array(
            'cost_type' =>'Total Customer Cost', 
            'value'     =>((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['customer_cost'] * $currency_rate),
            'color'     =>'#ff6600'
        );
    
        $chart3[] = array(
            'cost_type' =>'Azure Net Total Cost', 
            'value'     =>((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['azure_base_cost'] * $currency_rate),
            'color'     =>'#0080ff'
        );
    
        $chart3[] = array(
            'cost_type' =>'Total Cost By Actively Switching On/Off', 
            'value'     =>((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['benefit_by_paying_real_usage_per_hour'] * $currency_rate),
            'color'     =>'#57abff'
        );
    
        $chart3[] = array(
            'cost_type'     =>'Total Cost After Optimization', 
            'value'         =>((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['benefit_by_optimizing_utilization_of_the_processor_capacity'] * $currency_rate),
            'color'         =>'#29c7ff'
        );
        $chart3_json = json_encode($chart3);

        $chart12 = array();
        $chart12[] = array(
            'without_ri' => ((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['customer_cost'] * $currency_rate),
            'cost_type' => 'Customer Cost'
        );
    
        $chart12[] = array(
            'ri' =>((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['azure_base_cost_adjusted_by_RI'] * $currency_rate),
            'without_ri' =>((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['azure_base_cost'] * $currency_rate),
            'cost_type' => 'Azure Net Total Cost'
        );
    
        $chart12[] = array(
            'ri' =>((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['corrected_switching_on_off_after_RI'] * $currency_rate),
            'without_ri' => ((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['benefit_by_paying_real_usage_per_hour'] * $currency_rate),
            'cost_type' => 'Total Cost By Actively Switching On/Off'
        );

        $chart12[] = array(
            'ri' =>((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['optimizing_effect_on_the_RI_correct_costs'] * $currency_rate),
            'without_ri' =>((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['benefit_by_optimizing_utilization_of_the_processor_capacity'] * $currency_rate),
            'cost_type' => 'Total Cost After Optimization'
        );
        
        // $chart12[] = array(
        //                 'cost_type' =>'Impact of Reserved Instances', 
        //                 'value'     =>((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['impact_reserved_instances_price_after_optimizations'] * $currency_rate),
        //                 'color'     =>'#00ffff'
        // );
        $chart12_json = json_encode($chart12); //dd($chart12_json);

        //CHART 2 - CPU Benchmark
        $cpuBenchmarks = new CpuBenchmarks();
        $cpuBenchmarks_data = $cpuBenchmarks->Processor_Capacity_Compare($this->survey_info);
        unset($cpuBenchmarks_data['average_customer_benchmark']);
        unset($cpuBenchmarks_data['relative_improve']);
        
        $chart_cpuBenchmarks = array();
        $count_row = 0;
        foreach ($cpuBenchmarks_data as $item){
            $chart_cpuBenchmarks[$count_row]['name'] = $item['name'];
            $chart_cpuBenchmarks[$count_row]['value'] = $item['benchmarks'];
            
            switch($count_row){
                case '0':
                    $chart_cpuBenchmarks[$count_row]['color']= '#fdd400';
                    break;
                case '1':
                    $chart_cpuBenchmarks[$count_row]['color']= '#84b761';
                    break;
                case '2':
                    $chart_cpuBenchmarks[$count_row]['color']= '#ff6600';
                    break;
                case '3':
                    $chart_cpuBenchmarks[$count_row]['color']= '#0080ff';
                    break;
            }
            $count_row++;
        }
        $chart2_json = json_encode($chart_cpuBenchmarks);

        return array(
                    'chart13'   =>$chart13_json, 
                    'chart3'    =>$chart3_json, 
                    'chart12'   =>$chart12_json, 
                    'chart2'    =>$chart2_json
        );
    }

    public function updateTrimmingVms(Request $request)
    {
        $customer_case = \Auth::user()->guid;
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        $this->region = $customer_setup_config['azure_locale'];
        
        $params_request = $request->all();
        $uid = $params_request['uid'];

        $update_st = 0;
        if($uid == $this->survey_info['case_id'])
        {
            if($params_request['trimming_adjusted_reduction'] != null){
                $update_st = AzureBenefit::where(['section_name'=>'trimming_the_benefits_of_switching_on_off_vms', 'name' => 'adjusted_reduction_advantage_of_switching_on_off_vms', 'uid' => $uid])
                                    ->update(['value' => ($params_request['trimming_adjusted_reduction']/100)]);
            }
            // if($params_request['trimming_adjusted_optimization'] != null){
            //     $update_st = AzureBenefit::where(['section_name'=>'trimming_benefits_by_optimization_vms_sizes', 'name' => 'adjusted_optimization_results_after_further_analysis', 'uid' => $uid])
            //                         ->update(['value' => (string)($params_request['trimming_adjusted_optimization']/100)]);
            // }
        }

        //clear old cache
        $CustomerCache = new CustomerCache();
        $CustomerCache->refreshCacheData($this->survey_info, 'azure_benefits_values_input');

        //refresh chart data
        $chartData = array();
        if($update_st > 0){
            $dwaCalculationModel = new DashboardCalculation();
            $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $dwaCalculationModel->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($this->survey_info, $this->region);
            
            $chartData = $this->updateChartData($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost);

            $azureBenefit = new AzureBenefit();
            
            //Reload partner_margin
            $partner_margin_after_apply_RI_benefits  = $azureBenefit->partner_margin_after_apply_RI_benefits($this->survey_info, $this->region);
            $partner_margin_after_apply_all_benefits = $azureBenefit->partner_margin_after_apply_all_benefits($this->survey_info, $this->region);
            $partner_margin_after_apply_switching_optimization = $azureBenefit->partner_margin_after_apply_switching_optimization($this->survey_info, $this->region);
        }

        return response()->json(array(
            'update_status' => $update_st,
            'partner_margin_after_apply_RI_benefits'  => $partner_margin_after_apply_RI_benefits,
            'partner_margin_after_apply_all_benefits' => $partner_margin_after_apply_all_benefits,
            'partner_margin_after_apply_switching_optimization' => $partner_margin_after_apply_switching_optimization,
            'chart_data'    => $chartData
        )); 
        //return response()->json($update_st);
    }

    public function updateOptimizationBenefits(Request $request)
    {
        $customer_case = \Auth::user()->guid;
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        $this->region = $customer_setup_config['azure_locale'];

        $params_request = $request->all();
        $uid = $params_request['uid'];

        $update_st = 0;
        if($uid == $this->survey_info['case_id']){
            if($params_request['trimming_adjusted_optimization'] != null){
                 $update_st = AzureBenefit::where(['section_name'=>'trimming_benefits_by_optimization_vms_sizes', 'name' => 'adjusted_optimization_results_after_further_analysis', 'uid' => $uid])
                                     ->update(['value' => ($params_request['trimming_adjusted_optimization']/100)]);
            }

            if($params_request['optimization_effect_primary_storage'] != null){
                $update_st = AzureBenefit::where(['section_name'=>'optimising_the_storage_usage_when_migrating_to_azure', 'name' => 'optimization_effect_primary_storage', 'uid' => $uid])
                                    ->update(['value' => ($params_request['optimization_effect_primary_storage']/100)]);
            }

            if($params_request['compression_ratio_back_up_storage'] != null){
                $update_st = AzureBenefit::where(['section_name'=>'optimising_the_storage_usage_when_migrating_to_azure', 'name' => 'compression_ratio_of_the_back_up_storage', 'uid' => $uid])
                                    ->update(['value' => ($params_request['compression_ratio_back_up_storage']/100)]);
            }
        }

        //clear old cache
        $CustomerCache = new CustomerCache();
        $CustomerCache->refreshCacheData($this->survey_info, 'azure_benefits_values_input');

        //refresh chart data
        $chartData = array();
        if($update_st > 0){
            $dwaCalculationModel = new DashboardCalculation();
            $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $dwaCalculationModel->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($this->survey_info, $this->region);
            $chartData = $this->updateChartData($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost);

            $azureBenefit = new AzureBenefit();
            
            //Reload partner_margin
            $partner_margin_after_apply_RI_benefits  = $azureBenefit->partner_margin_after_apply_RI_benefits($this->survey_info, $this->region);
            $partner_margin_after_apply_all_benefits = $azureBenefit->partner_margin_after_apply_all_benefits($this->survey_info, $this->region);
            $partner_margin_after_apply_switching_optimization = $azureBenefit->partner_margin_after_apply_switching_optimization($this->survey_info, $this->region);
        }

        return response()->json(array(
            'update_status' => $update_st,
            'partner_margin_after_apply_RI_benefits'  => $partner_margin_after_apply_RI_benefits,
            'partner_margin_after_apply_all_benefits' => $partner_margin_after_apply_all_benefits,
            'partner_margin_after_apply_switching_optimization' => $partner_margin_after_apply_switching_optimization,
            'chart_data'    => $chartData
        )); 
        //return response()->json($update_st);
    }

    public function updateAllocationReservedInstance(Request $request)
    {
        $customer_case = \Auth::user()->guid;
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        $this->region = $customer_setup_config['azure_locale'];

        $params_request = $request->all(); //dd($params_request);
        parse_str($params_request['allocation-reserved-instances-inputs'], $params_input); //dd($params_input);
        
        $azureBenefit = new AzureBenefit();
        $allocation_of_reserved_instances = $azureBenefit->Allocation_Of_Reserved_Instances($this->survey_info); 

        if(count($params_input) > 0)
        {
            $GP_params = array();
            $MO_params = array();

            //GP
            foreach($allocation_of_reserved_instances['GP_allocation'] as $value)
            {    
                $vm_type_name = $value['vm_type_name'];

                $GP_params[$vm_type_name]['vm_type_id']      = $value['vm_type_id'];
                $GP_params[$vm_type_name]['ri_one_year']     = $value['ri_one_year'];
                $GP_params[$vm_type_name]['ri_three_year']   = $value['ri_three_year'];
                $GP_params[$vm_type_name]['ri_hybrid']       = $value['ri_hybrid'];
                        
                foreach($params_input as $item => $new_value)
                {
                    $filter = explode('_', $item);
                    
                    $p_allocation_type = $filter[0];
                    $p_RI_type = $filter[1];
                    $p_vm_type_name = $filter[2];

                    if($p_allocation_type == 'gp'){
                        if($vm_type_name == $p_vm_type_name && $p_RI_type == 'ri1y'){
                            $GP_params[$vm_type_name]['ri_one_year'] = $new_value;
                        }
                        if($vm_type_name == $p_vm_type_name && $p_RI_type == 'ri3y'){
                            $GP_params[$vm_type_name]['ri_three_year'] = $new_value;
                        }
                        if($vm_type_name == $p_vm_type_name && $p_RI_type == 'hybrid'){
                            $GP_params[$vm_type_name]['ri_hybrid'] = $new_value;
                        }
                    }
                }
            } //dd($GP_params);

            //MO
            foreach($allocation_of_reserved_instances['MO_allocation'] as $value)
            {    
                $vm_type_name = $value['vm_type_name'];

                $MO_params[$vm_type_name]['vm_type_id']      = $value['vm_type_id'];
                $MO_params[$vm_type_name]['ri_one_year']     = $value['ri_one_year'];
                $MO_params[$vm_type_name]['ri_three_year']   = $value['ri_three_year'];
                $MO_params[$vm_type_name]['ri_hybrid']       = $value['ri_hybrid'];
                        
                foreach($params_input as $item => $new_value)
                {
                    $filter = explode('_', $item);
                    
                    $p_allocation_type = $filter[0];
                    $p_RI_type = $filter[1];
                    $p_vm_type_name = $filter[2];

                    if($p_allocation_type == 'mo'){
                        if($vm_type_name == $p_vm_type_name && $p_RI_type == 'ri1y'){
                            $MO_params[$vm_type_name]['ri_one_year'] = $new_value;
                        }
                        if($vm_type_name == $p_vm_type_name && $p_RI_type == 'ri3y'){
                            $MO_params[$vm_type_name]['ri_three_year'] = $new_value;
                        }
                        if($vm_type_name == $p_vm_type_name && $p_RI_type == 'hybrid'){
                            $MO_params[$vm_type_name]['ri_hybrid'] = $new_value;
                        }
                    }
                }
            } //dd($MO_params);
            
            //update GP allocation
            $result_gp_update = 0;
            foreach($GP_params as $key => $value){
                
                $where_conditions = array();
                $where_conditions['uid'] = $this->survey_info['case_id'];
                $where_conditions['vm_type_id'] = $value['vm_type_id'];
                $where_conditions['allocation_type'] = 'GP';
                //dd($where_conditions);
                $update_value = array();
                $update_value['ri_one_year']    = ($value['ri_one_year'] > 0)?($value['ri_one_year']/100):$value['ri_one_year'];
                $update_value['ri_three_year']  = ($value['ri_three_year'] > 0)?($value['ri_three_year']/100):$value['ri_three_year'];
                $update_value['ri_hybrid']      = ($value['ri_hybrid'] > 0)?($value['ri_hybrid']/100):$value['ri_hybrid'];

                $result_gp_update = dwa_reserved_instance_allocation::where($where_conditions)->update($update_value); 
                
            }

            //update GP allocation
            $result_mo_update = 0;
            foreach($MO_params as $key => $value){
                
                $where_conditions = array();
                $where_conditions['uid'] = $this->survey_info['case_id'];
                $where_conditions['vm_type_id'] = $value['vm_type_id'];
                $where_conditions['allocation_type'] = 'MO';
                //dd($where_conditions);
                $update_value = array();
                $update_value['ri_one_year']    = ($value['ri_one_year'] > 0)?($value['ri_one_year']/100):$value['ri_one_year'];
                $update_value['ri_three_year']  = ($value['ri_three_year'] > 0)?($value['ri_three_year']/100):$value['ri_three_year'];
                $update_value['ri_hybrid']      = ($value['ri_hybrid'] > 0)?($value['ri_hybrid']/100):$value['ri_hybrid'];

                $result_mo_update = dwa_reserved_instance_allocation::where($where_conditions)->update($update_value); 
                
            }

            //clear old cache
            $CustomerCache = new CustomerCache();
            $CustomerCache->refreshCacheData($this->survey_info, 'dwa_reserved_instance_allocation');

            //refresh chart data
            $chartData = array();
            if($result_gp_update > 0 && $result_mo_update > 0){
                //Chart Data
                $dwaCalculationModel = new DashboardCalculation();
                $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $dwaCalculationModel->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($this->survey_info, $this->region);
                $chartData = $this->updateChartData($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost);
                
                $azureBenefit = new AzureBenefit();
                //Reload Pre-payment for all RI
                $pre_payment_reserved_instances          = $azureBenefit->Pre_Payment_Reserved_Instances($this->survey_info, $this->region);
                //Reload partner_margin
                $partner_margin_after_apply_RI_benefits  = $azureBenefit->partner_margin_after_apply_RI_benefits($this->survey_info, $this->region);
                $partner_margin_after_apply_all_benefits = $azureBenefit->partner_margin_after_apply_all_benefits($this->survey_info, $this->region);
            }

            return response()->json(array(
                'update_gp'     => $result_gp_update, 
                'update_mo'     => $result_mo_update,
                'pre_payment_reserved_instances'          => $pre_payment_reserved_instances,
                'partner_margin_after_apply_RI_benefits'  => $partner_margin_after_apply_RI_benefits,
                'partner_margin_after_apply_all_benefits' => $partner_margin_after_apply_all_benefits,
                'chart_data'    => $chartData
            ));
        }
        else
            return response()->json(['update_gp' => 0, 'update_mo' => 0]);
    }

    public function updateAdjustedReversedInstance(Request $request)
    {
        $customer_case = \Auth::user()->guid;
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        $this->region = $customer_setup_config['azure_locale'];

        $params_request = $request->all();
        $id = $params_request['id'];
        $adjusted_reverse_instance = $params_request['adjusted_reverse_instance'];

        if($id == $this->survey_info['case_id'])
        {
            $update_st = dwa_pricing_variables_input::where(['pricing_variables' => 'discount_when_buying_reserved_instances','uid' => $id])->update(['adjusted_value' => ($adjusted_reverse_instance/100)]);
            
            //clear old cache value
            $CustomerCache = new CustomerCache();
            $CustomerCache->refreshCacheData($this->survey_info, 'dwa_pricing_variables_input');

            //refresh chart data
            $chartData = array();
            if($update_st > 0){
                $dwaCalculationModel = new DashboardCalculation();
                $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $dwaCalculationModel->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($this->survey_info, $this->region);
                $chartData = $this->updateChartData($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost);

                $azureBenefit = new AzureBenefit();
                //Reload Pre-payment for all RI
                $pre_payment_reserved_instances          = $azureBenefit->Pre_Payment_Reserved_Instances($this->survey_info, $this->region);
                //Reload partner_margin
                $partner_margin_after_apply_RI_benefits  = $azureBenefit->partner_margin_after_apply_RI_benefits($this->survey_info, $this->region);
                $partner_margin_after_apply_all_benefits = $azureBenefit->partner_margin_after_apply_all_benefits($this->survey_info, $this->region);
            }

            return response()->json(array(
                'update_st'                               => $update_st,
                'pre_payment_reserved_instances'          => $pre_payment_reserved_instances,
                'partner_margin_after_apply_RI_benefits'  => $partner_margin_after_apply_RI_benefits,
                'partner_margin_after_apply_all_benefits' => $partner_margin_after_apply_all_benefits,
                'chart_data'                     => $chartData
            ));
        }
    }
}
