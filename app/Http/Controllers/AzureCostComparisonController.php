<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Helpers\CustomerCache;

use App\DashboardCalculation;
use App\AzureCostComparison;
use App\dwa_pricing_variables_input;
use App\virtual_machine_for_compute;

class AzureCostComparisonController extends Controller
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
        $customer_case = \Auth::user()->guid;
        $survey_info   = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        
        $this->currency_rate    = $customer_setup_config['currency']['currency_rate'];
        $this->currency_code    = $customer_setup_config['currency']['currency_code'];
        $this->region           = $customer_setup_config['azure_locale'];

        $region                 = $this->region;
        $currency_rate          = $this->currency_rate;
        $currency_code          = $this->currency_code;
        $customer_case_id       = $survey_info['case_id'];
        
        $azureCalculation = new AzureCostComparison();
        $weighted_primary_storage_usage_allocation  = $azureCalculation->Weighted_Primary_Storage_Usage_Allocation($survey_info);
        $spread_of_GP_MO_compute                    = $azureCalculation->Spread_Of_GP_MO_Compute($survey_info);
        $weighted_backup_storage                    = $azureCalculation->Weighted_Backup_Storage_LRS($survey_info);
        $adjusting_azure_outbound_traffic_cost      = $azureCalculation->Adjusting_Azure_Outbound_Traffic_Cost($survey_info);
        $input_of_pricing_variables                 = $azureCalculation->Input_Of_Pricing_Variables($survey_info); //dd($input_of_pricing_variables);
        $azure_site_recovery                        = $azureCalculation->Azure_Site_Recovery($survey_info);
        $partner_margin_for_end_customer            = $azureCalculation->Partner_Margin_For_End_Customer($survey_info, $region);
        $corrected_compute_ratio                    = $azureCalculation->Corrected_Compute_Ratio($survey_info);

        $calculations_azure = array();
        $calculations_azure['input_of_pricing_variables']                   = $input_of_pricing_variables;
        $calculations_azure['weighted_primary_storage_usage_allocation']    = $weighted_primary_storage_usage_allocation;
        $calculations_azure['weighted_backup_storage']                      = $weighted_backup_storage;
        $calculations_azure['adjusting_azure_outbound_traffic_cost']        = $adjusting_azure_outbound_traffic_cost;
        $calculations_azure['spread_of_GP_MO_compute']                      = $spread_of_GP_MO_compute;
        $calculations_azure['azure_site_recovery']                          = $azure_site_recovery;
        $calculations_azure['corrected_compute_ratio']                      = $corrected_compute_ratio;
        $calculations_azure['partner_margin_for_end_customer']              = $partner_margin_for_end_customer;
        
        $dwaCalculation = new DashboardCalculation();
        $cost_price_of_customer_required_infrastructure                                     = $dwaCalculation->Cost_Price_of_Customer_Required_Infrastructure($survey_info, $region);
        $cost_comparison_between_customer_storage_costs_and_azure_storage_cost              = $dwaCalculation->Cost_Comparison_Between_Customer_Storage_Costs_and_Azure_Storage_Cost($survey_info);
        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost    = $dwaCalculation->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($survey_info, $region);

        $calculations_data = array();
        $calculations_data['cost_price_of_customer_required_infrastructure'] = $cost_price_of_customer_required_infrastructure;
        $calculations_data['cost_comparison_between_customer_storage_costs_and_azure_storage_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost;
        $calculations_data['comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost;
        
        //return to view
        return view("azure-cost-comparison", compact(['survey_info', 'customer_case_id', 'currency_rate', 'currency_code', 'calculations_data', 'calculations_azure']));
    }

    private function updateChartData($calculations_data)
    {
        $customer_case = \Auth::user()->guid;
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        $this->region = $customer_setup_config['azure_locale'];

        $currency_rate = $customer_setup_config['currency']['currency_rate']; 

        $cost_price_of_customer_required_infrastructure = $calculations_data['cost_price_of_customer_required_infrastructure'];
        $cost_comparison_between_customer_storage_costs_and_azure_storage_cost = $calculations_data['cost_comparison_between_customer_storage_costs_and_azure_storage_cost'];
        
        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $calculations_data['comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost'];
        
        //Chart 10 data
        $customer_total_windows_cost = $cost_price_of_customer_required_infrastructure['total_customer_cost']['windows'];
        $customer_total_linux_cost = $cost_price_of_customer_required_infrastructure['total_customer_cost']['linux'];

        $azure_total_windows_cost = $cost_price_of_customer_required_infrastructure['total_azure_net_cost']['windows'];
        $azure_total_linux_cost = $cost_price_of_customer_required_infrastructure['total_azure_net_cost']['linux'];
        $azure_vms_under_ASR_cost = $cost_price_of_customer_required_infrastructure['vms_under_ASR']['azure_net_cost'];


        $windows_data = array();
        $windows_data['customer_cost']  = $customer_total_windows_cost * $currency_rate;
        $windows_data['azure_cost']     = $azure_total_windows_cost * $currency_rate;
        $windows_data['cost_type']      = 'All Windows OS';

        $linux_data['customer_cost']  = $customer_total_linux_cost * $currency_rate;
        $linux_data['azure_cost']     = $azure_total_linux_cost * $currency_rate;
        $linux_data['cost_type']      = 'All Linux OS';

        $total_data['customer_cost']  = $linux_data['customer_cost'] + $windows_data['customer_cost'];
        $total_data['azure_cost']     = $linux_data['azure_cost'] + $windows_data['azure_cost'];
        $total_data['cost_type']      = 'Total Cost Compared';
        
        //Chart 8 data
        $primary_storage_LRS['customer_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['primary_storage_compare_based_on_azure_LRS']['customer_storage_cost_levels'] * $currency_rate;
        $primary_storage_LRS['azure_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['primary_storage_compare_based_on_azure_LRS']['comparable_azure_cost_levels'] * $currency_rate;
        $primary_storage_LRS['cost_type'] = 'Primary Storage compare based on Azure LRS';

        $auxiliary_storage_LRS['customer_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['auxiliary_storage_compare_based_on_azure_LRS']['customer_storage_cost_levels'] * $currency_rate;
        $auxiliary_storage_LRS['azure_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['auxiliary_storage_compare_based_on_azure_LRS']['comparable_azure_cost_levels'] * $currency_rate;
        $auxiliary_storage_LRS['cost_type'] = 'Auxiliary Storage compare based on Azure LRS';

        $auxiliary_storage_GRS['customer_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['auxiliary_storage_compare_based_on_azure_GRS']['customer_storage_cost_levels'] * $currency_rate;
        $auxiliary_storage_GRS['azure_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost['auxiliary_storage_compare_based_on_azure_GRS']['comparable_azure_cost_levels'] * $currency_rate;
        $auxiliary_storage_GRS['cost_type'] = 'Auxiliary Storage compare based on Azure GRS';

        //chart 9
        $vms_including_all_other_costs_except_storage['customer_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['vms_including_all_other_costs_except_storage']['customer_cost'] * $currency_rate;
        $vms_including_all_other_costs_except_storage['azure_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['vms_including_all_other_costs_except_storage']['azure_base_cost'] * $currency_rate;
        $vms_including_all_other_costs_except_storage['cost_type'] = "All VM's Including All Other Costs, Except Storage";

        $storage_cost['customer_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['storage_cost']['customer_cost'] * $currency_rate;
        $storage_cost['azure_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['storage_cost']['azure_base_cost'] * $currency_rate;
        $storage_cost['cost_type'] = "Storage Cost";

        $total_cost_compare['customer_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['customer_cost'] * $currency_rate;
        $total_cost_compare['azure_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['total_cost_compare']['azure_base_cost'] * $currency_rate;
        $total_cost_compare['cost_type'] = "Total Costs Compared";

        $chart10 = array();
        $chart10[] = $windows_data;
        $chart10[] = $linux_data;
        $chart10[] = $total_data;
        $chart10_json = json_encode($chart10);

        $chart8 = array();
        $chart8[] = $primary_storage_LRS;
        $chart8[] = $auxiliary_storage_LRS;
        $chart8[] = $auxiliary_storage_GRS;
        $chart8_json = json_encode($chart8);

        $chart9 = array();
        $chart9[] = $vms_including_all_other_costs_except_storage;
        $chart9[] = $storage_cost;
        $chart9[] = $total_cost_compare;
        
        $chart9_json = json_encode($chart9); //dd($chart10_json);

        return array('chart8'=>$chart8_json, 'chart9'=>$chart9_json, 'chart10'=>$chart10_json);
    }

    public function updateCSPdiscount(Request $request)
    {
        $customer_case = \Auth::user()->guid;
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        $this->region = $customer_setup_config['azure_locale'];
        
        $params_request = $request->all();
        $id = $params_request['id'];
        
        $input_CSP_discount             = $params_request['input_CSP_discount'];
        $input_CSP_rebate               = $params_request['input_CSP_rebate'];

        if($params_request['input_CSP_discount']<$params_request['input_adjust_CSP_discount'])
            $input_adjust_CSP_discount  = $params_request['input_CSP_discount'];
        else
        $input_adjust_CSP_discount      = $params_request['input_adjust_CSP_discount'];

        if($params_request['input_CSP_rebate']<$params_request['input_adjust_CSP_rebate'])
            $input_adjust_CSP_rebate  = $params_request['input_CSP_rebate'];
        else
        $input_adjust_CSP_rebate        = $params_request['input_adjust_CSP_rebate'];
        
        $input_percentage_azure_cost    = $params_request['input_percentage_azure_cost'];
        $input_azure_service_margin     = $params_request['input_azure_service_margin'];

        $input_reverse_instance         = $params_request['input_reverse_instance'];

        if($id==$this->survey_info['case_id'])
        {
            //dd($input_adjust_CSP_discount);
            $update_st = dwa_pricing_variables_input::where(['pricing_variables' => 'applicable_CSP_or_EA_discount','uid' => $id])->update(['input_value' => ($input_CSP_discount/100)]); 
            $update_st = dwa_pricing_variables_input::where(['pricing_variables' => 'applicable_CSP_or_EA_rebate','uid' => $id])->update(['input_value' => ($input_CSP_rebate/100)]);
            
            $update_st = dwa_pricing_variables_input::where(['pricing_variables' => 'applicable_CSP_or_EA_discount','uid' => $id])->update(['adjusted_value' => ($input_adjust_CSP_discount/100)]); 
            $update_st = dwa_pricing_variables_input::where(['pricing_variables' => 'applicable_CSP_or_EA_rebate','uid' => $id])->update(['adjusted_value' => ($input_adjust_CSP_rebate/100)]);
            
            $update_st = dwa_pricing_variables_input::where(['pricing_variables' => 'percentage_azure_variable_costs','uid' => $id])->update(['adjusted_value' => ($input_percentage_azure_cost/100)]);
            $update_st = dwa_pricing_variables_input::where(['pricing_variables' => 'managed_service_margin_on_azure','uid' => $id])->update(['adjusted_value' => ($input_azure_service_margin/100)]);

            $update_st = dwa_pricing_variables_input::where(['pricing_variables' => 'discount_when_buying_reserved_instances','uid' => $id])->update(['input_value' => ($input_reverse_instance/100)]);
            $update_st = dwa_pricing_variables_input::where(['pricing_variables' => 'discount_when_buying_reserved_instances','uid' => $id])->update(['adjusted_value' => ($input_reverse_instance/100)]);
            
            //clear old cache value
            $CustomerCache = new CustomerCache();
            $CustomerCache->refreshCacheData($this->survey_info, 'dwa_pricing_variables_input');
            $CustomerCache->refreshCacheData($this->survey_info, 'virtual_machine_for_compute');
            //\Cache::forget('input_of_pricing_variables_'.$this->survey_info['case_id']);

            //reload partner margin for end customer data
            $azureCalculation = new AzureCostComparison();
            $partner_margin_for_end_customer            = $azureCalculation->Partner_Margin_For_End_Customer($this->survey_info, $this->region);

            //reload chart Data
            $dwaCalculation = new DashboardCalculation();
            $cost_price_of_customer_required_infrastructure                                     = $dwaCalculation->Cost_Price_of_Customer_Required_Infrastructure($this->survey_info, $this->region);
            $cost_comparison_between_customer_storage_costs_and_azure_storage_cost              = $dwaCalculation->Cost_Comparison_Between_Customer_Storage_Costs_and_Azure_Storage_Cost($this->survey_info);
            $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost    = $dwaCalculation->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($this->survey_info, $this->region);

            $pricingVariables = array();
            $pricingVariables['input_CSP_discount'] = (float)$input_CSP_discount;
            $pricingVariables['input_adjust_CSP_discount'] = (float)$input_adjust_CSP_discount;
            $pricingVariables['input_CSP_rebate'] = (float)$input_CSP_rebate;
            $pricingVariables['input_adjust_CSP_rebate'] = (float)$input_adjust_CSP_rebate;

            $calculations_data = array();
            $calculations_data['cost_price_of_customer_required_infrastructure'] = $cost_price_of_customer_required_infrastructure;
            $calculations_data['cost_comparison_between_customer_storage_costs_and_azure_storage_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost;
            $calculations_data['comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost;

            $chartData = $this->updateChartData($calculations_data);
            
            return response()->json(array(
                'update_st' => $update_st,
                'partner_margin_for_end_customer' => $partner_margin_for_end_customer,
                'pricingVariables' => $pricingVariables,
                'chartData' => $chartData
            ));
        }
    }

    public function updateWeightedBackupStorage(Request $request)
    {
        $customer_case = \Auth::user()->guid;
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        $this->region = $customer_setup_config['azure_locale'];

        $params_request = $request->all();
        $uid = $params_request['uid'];
        
        $weighted_block_blob_lrs_hot = $params_request['weighted_block_blob_lrs_hot'];
        $weighted_block_blob_lrs_cool = $params_request['weighted_block_blob_lrs_cool'];
        $weighted_block_blob_lrs_archive = $params_request['weighted_block_blob_lrs_archive'];

        if($uid == $this->survey_info['case_id']){
            $update_st = dwa_pricing_variables_input::where(['pricing_variables' => 'weighted_block_blob_LRS_HOT','uid' => $uid])->update(['input_value' => ($weighted_block_blob_lrs_hot/100)]);
            $update_st = dwa_pricing_variables_input::where(['pricing_variables' => 'weighted_block_blob_LRS_COOL','uid' => $uid])->update(['input_value' => ($weighted_block_blob_lrs_cool/100)]);
            $update_st = dwa_pricing_variables_input::where(['pricing_variables' => 'weighted_block_bob_LRS_archive','uid' => $uid])->update(['input_value' => ($weighted_block_blob_lrs_archive/100)]);
        }

        //clear old cache value
        $CustomerCache = new CustomerCache();
        $CustomerCache->refreshCacheData($this->survey_info, 'dwa_pricing_variables_input');
        $CustomerCache->refreshCacheData($this->survey_info, 'virtual_machine_for_compute');

        //reload chart Data
        $dwaCalculation = new DashboardCalculation();
        $cost_price_of_customer_required_infrastructure                                     = $dwaCalculation->Cost_Price_of_Customer_Required_Infrastructure($this->survey_info, $this->region);
        $cost_comparison_between_customer_storage_costs_and_azure_storage_cost              = $dwaCalculation->Cost_Comparison_Between_Customer_Storage_Costs_and_Azure_Storage_Cost($this->survey_info);
        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost    = $dwaCalculation->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($this->survey_info, $this->region);

        $calculations_data = array();
        $calculations_data['cost_price_of_customer_required_infrastructure'] = $cost_price_of_customer_required_infrastructure;
        $calculations_data['cost_comparison_between_customer_storage_costs_and_azure_storage_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost;
        $calculations_data['comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost;

        $chartData = $this->updateChartData($calculations_data);

        $azureCalculation = new AzureCostComparison();
        $partner_margin_for_end_customer    = $azureCalculation->Partner_Margin_For_End_Customer($this->survey_info, $this->region);
        
        return response()->json(array(
            'update_st' => $update_st,
            'chartData' => $chartData,
            'partner_margin_for_end_customer'=> $partner_margin_for_end_customer
        ));
    }

    public function updateCorrectedComputeRatio(Request $request)
    {
        $customer_case = \Auth::user()->guid;
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        $this->region = $customer_setup_config['azure_locale'];

        $params_request = $request->all();
        $uid = $params_request['uid'];
        
        $adjusting_gp_to_mo = $params_request['adjusting_gp_to_mo'];
       
        if($uid == $this->survey_info['case_id']){
            $update_st = dwa_pricing_variables_input::where(['pricing_variables' => 'adjusting_GP_to_MO', 'uid' => $uid])
                                                    ->update(['adjusted_value' => ($adjusting_gp_to_mo / 100)]);
        }

        //clear old cache value
        $CustomerCache = new CustomerCache();
        $CustomerCache->refreshCacheData($this->survey_info, 'dwa_pricing_variables_input');
        $CustomerCache->refreshCacheData($this->survey_info, 'virtual_machine_for_compute');

        //reload chart Data
        $dwaCalculation = new DashboardCalculation();
        $cost_price_of_customer_required_infrastructure                                     = $dwaCalculation->Cost_Price_of_Customer_Required_Infrastructure($this->survey_info, $this->region);
        $cost_comparison_between_customer_storage_costs_and_azure_storage_cost              = $dwaCalculation->Cost_Comparison_Between_Customer_Storage_Costs_and_Azure_Storage_Cost($this->survey_info);
        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost    = $dwaCalculation->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($this->survey_info, $this->region);

        $calculations_data = array();
        $calculations_data['cost_price_of_customer_required_infrastructure'] = $cost_price_of_customer_required_infrastructure;
        $calculations_data['cost_comparison_between_customer_storage_costs_and_azure_storage_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost;
        $calculations_data['comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost;

        $chartData = $this->updateChartData($calculations_data);
        
        $azureCalculation = new AzureCostComparison();
        $corrected_compute_ratio            = $azureCalculation->Corrected_Compute_Ratio($this->survey_info);
        $partner_margin_for_end_customer    = $azureCalculation->Partner_Margin_For_End_Customer($this->survey_info, $this->region);
            
        return response()->json(array(
            'update_st' => $update_st,
            'chartData' => $chartData,
            'corrected_compute_ratio' => $corrected_compute_ratio,
            'partner_margin_for_end_customer' => $partner_margin_for_end_customer
        ));
    }

    public function updateGPMOCompute(Request $request)
    {
        $customer_case = \Auth::user()->guid;
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        $this->region = $customer_setup_config['azure_locale'];

        $params_request = $request->all();
        $GP_values = $params_request['gp'];
        $MO_values = $params_request['mo'];

        $uid = $params_request['uid'];
        
        $update_gp = 0;
        $update_mo = 0;
        if($uid == $this->survey_info['case_id']) 
        {
            foreach ($GP_values as $key=>$value){
                $gp_vm_type_name = $key;
                $gp_value = ($value/100);

                $where_conditions = array();
                $where_conditions['uid'] = $uid;
                $where_conditions['vm_type_name'] = $gp_vm_type_name;
                $where_conditions['compute_type'] = 'GP';

                $update_value = array();
                $update_value['percentage'] = $gp_value;

                $update_gp = virtual_machine_for_compute::where($where_conditions)->update($update_value); 
                //$statement .= "UPDATE virtual_machine_for_compute SET percentage ='".$gp_value."' WHERE compute_type = 'GP' AND uid = '".$uid."' AND vm_type_name ='".$gp_vm_type_name."'; "; 
            }

            foreach ($MO_values as $key=>$value){
                $mo_vm_type_name = $key;
                $mo_value = ($value/100);

                $where_conditions = array();
                $where_conditions['uid'] = $uid;
                $where_conditions['vm_type_name'] = $mo_vm_type_name;
                $where_conditions['compute_type'] = 'MO';

                $update_value = array();
                $update_value['percentage'] = $mo_value;

                $update_mo = virtual_machine_for_compute::where($where_conditions)->update($update_value); 
            }

            //clear old cache value
            $CustomerCache = new CustomerCache();
            $CustomerCache->refreshCacheData($this->survey_info, 'dwa_pricing_variables_input');
            $CustomerCache->refreshCacheData($this->survey_info, 'virtual_machine_for_compute');

            //reload chart Data
            $dwaCalculation = new DashboardCalculation();
            $cost_price_of_customer_required_infrastructure                                     = $dwaCalculation->Cost_Price_of_Customer_Required_Infrastructure($this->survey_info, $this->region);
            $cost_comparison_between_customer_storage_costs_and_azure_storage_cost              = $dwaCalculation->Cost_Comparison_Between_Customer_Storage_Costs_and_Azure_Storage_Cost($this->survey_info);
            $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost    = $dwaCalculation->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($this->survey_info, $this->region);

            $calculations_data = array();
            $calculations_data['cost_price_of_customer_required_infrastructure'] = $cost_price_of_customer_required_infrastructure;
            $calculations_data['cost_comparison_between_customer_storage_costs_and_azure_storage_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost;
            $calculations_data['comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost;

            $chartData = $this->updateChartData($calculations_data);
            
            $azureCalculation = new AzureCostComparison();
            $partner_margin_for_end_customer    = $azureCalculation->Partner_Margin_For_End_Customer($this->survey_info, $this->region);
        
            return response()->json(array(
                'chartData' => $chartData,
                'partner_margin_for_end_customer' => $partner_margin_for_end_customer,
            ));
        }
    }

    public function updateVMCoveredWithASRNumber(Request $request)
    {
        $customer_case = \Auth::user()->guid;
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        $this->region = $customer_setup_config['azure_locale'];

        $params_request = $request->all();
        $uid = $params_request['uid'];
        
        $vm_covered_with_asr_number = $params_request['vm_covered_with_asr_number'];
       
        if($uid == $this->survey_info['case_id']){
            $update_st = dwa_pricing_variables_input::where(['pricing_variables' => 'number_of_vms_covered_with_ASR', 'uid' => $uid])
                                                    ->update(['adjusted_value' => ($vm_covered_with_asr_number)]);
        }

        //clear old cache value
        $CustomerCache = new CustomerCache();
        $CustomerCache->refreshCacheData($this->survey_info, 'dwa_pricing_variables_input');
        $CustomerCache->refreshCacheData($this->survey_info, 'virtual_machine_for_compute');

        //reload chart Data
        $dwaCalculation = new DashboardCalculation();
        $cost_price_of_customer_required_infrastructure                                     = $dwaCalculation->Cost_Price_of_Customer_Required_Infrastructure($this->survey_info, $this->region);
        $cost_comparison_between_customer_storage_costs_and_azure_storage_cost              = $dwaCalculation->Cost_Comparison_Between_Customer_Storage_Costs_and_Azure_Storage_Cost($this->survey_info);
        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost    = $dwaCalculation->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($this->survey_info, $this->region);

        $calculations_data = array();
        $calculations_data['cost_price_of_customer_required_infrastructure'] = $cost_price_of_customer_required_infrastructure;
        $calculations_data['cost_comparison_between_customer_storage_costs_and_azure_storage_cost'] = $cost_comparison_between_customer_storage_costs_and_azure_storage_cost;
        $calculations_data['comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost'] = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost;

        $chartData = $this->updateChartData($calculations_data);
        
        $azureCalculation = new AzureCostComparison();
        $partner_margin_for_end_customer    = $azureCalculation->Partner_Margin_For_End_Customer($this->survey_info, $this->region);
        
        return response()->json(array(
            'update_st' => $update_st,
            'chartData' => $chartData,
            'partner_margin_for_end_customer' => $partner_margin_for_end_customer
        ));
    }

    public function updateAzureOutboundTrafficCost(Request $request)
    {
        $customer_case = \Auth::user()->guid;
        $this->survey_info = \Cache::get('survey-info_'.$customer_case);
        
        $customer_setup_config = session('customer_setup_config');
        $this->region = $customer_setup_config['azure_locale'];

        $this->currency_rate    = $customer_setup_config['currency']['currency_rate'];
        $this->currency_code    = $customer_setup_config['currency']['currency_code'];

        $params_request = $request->all();
        $uid = $params_request['uid'];
        
        $adjust_custom_price = $params_request['adjust_custom_price'] / $this->currency_rate;
       
        if($uid == $this->survey_info['case_id']){
            $update_st = dwa_pricing_variables_input::where(['pricing_variables' => 'adjust_custom_price', 'uid' => $uid])
                                                    ->update(['adjusted_value' => ($adjust_custom_price)]);
        }

        //clear old cache value
        $CustomerCache = new CustomerCache();
        $CustomerCache->refreshCacheData($this->survey_info, 'dwa_pricing_variables_input');

        $azureCalculation = new AzureCostComparison();
        $adjusting_azure_outbound_traffic_cost    = $azureCalculation->Adjusting_Azure_Outbound_Traffic_Cost($this->survey_info);

        return response()->json(array(
            'update_st' => $update_st,
            'adjusting_azure_outbound_traffic_cost'  => $adjusting_azure_outbound_traffic_cost
        ));
    }
}
