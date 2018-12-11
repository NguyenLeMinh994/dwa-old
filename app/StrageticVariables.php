<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\AzureBenefit;
use App\AzureCostComparison;
use App\DashboardCalculation;

class StrageticVariables extends Model
{
    //Reserved Instance Discounts
    public function reserved_instance_discounts($survey_info)
    {
        $vm_cache_name = 'dwa_reserved_instance_discounts_'.$survey_info['case_id'];
        if (\Cache::has($vm_cache_name) == false)
        {
            $vm_list = DB::table('dwa_reserved_instance_discounts')
                    ->select(array('dwa_reserved_instance_discounts.*', 'virtual_machine_types.type_name'))            
                    ->leftJoin('virtual_machine_types', 'dwa_reserved_instance_discounts.vm_type_id', '=', 'virtual_machine_types.id')
                    ->where('dwa_reserved_instance_discounts.uid', $survey_info['case_id'])
                    ->get(); //dd($vm_list);
            \Cache::put($vm_cache_name, $vm_list, 30);
        }
        else
            $vm_list = \Cache::get($vm_cache_name);
        //return
        $return_data = array();

        $windows_discount = array();
        $linux_discount = array();
        foreach($vm_list as $value)
        {
            if($value->type_name == 'Nc' || $value->type_name == 'Nv') //group Nc & Nv to N
                $value->type_name = 'N';

            if($value->os_type=='windows'){
                $windows_discount[$value->type_name]['vm_type_id'] = $value->vm_type_id;
                $windows_discount[$value->type_name]['one_year'] = $value->one_year;
                $windows_discount[$value->type_name]['three_year'] = $value->three_year;
                $windows_discount[$value->type_name]['hybrid'] = $value->hybrid;
            }
            if($value->os_type=='linux'){
                $linux_discount[$value->type_name]['vm_type_id'] = $value->vm_type_id;
                $linux_discount[$value->type_name]['one_year'] = $value->one_year;
                $linux_discount[$value->type_name]['three_year'] = $value->three_year;
                $linux_discount[$value->type_name]['hybrid'] = $value->hybrid;
            }
        }

        $return_data['windows'] = $windows_discount;
        $return_data['linux'] = $linux_discount;
        
        //dd($return_data);
        return $return_data;
    }

    public function reserved_instance_discounts_customer($survey_info)
    {
        $azure_RI_instance_discounts = $this->reserved_instance_discounts($survey_info); //dd($azure_RI_instance_discounts);

        $azureCalculation = new AzureCostComparison();
        $input_of_pricing_variables = $azureCalculation->Input_Of_Pricing_Variables($survey_info); //dd($input_of_pricing_variables);
        
        $adjusted_discount_when_buying_reserved_instances = $input_of_pricing_variables['discount_when_buying_reserved_instances']->adjusted_value;
        $adjusted_managed_service_margin_on_azure = $input_of_pricing_variables['managed_service_margin_on_azure']->adjusted_value;
        
        $azureBenefitModel = new AzureBenefit();
        $instances_allocation = $azureBenefitModel = $azureBenefitModel->Allocation_Of_Reserved_Instances($survey_info); //dd($instances_allocation);

        $window_discount = array();
        foreach($azure_RI_instance_discounts['windows'] as $key => $value)
        {
            $window_discount[$key]['vm_type_id']        = $value['vm_type_id'];
            if ($key == 'Av2' || $key == 'Amv2')
                $window_discount[$key]['one_year']['ri']    = $value['one_year'];
            else
            $window_discount[$key]['one_year']['ri']    = $value['one_year'] + $adjusted_discount_when_buying_reserved_instances - $adjusted_managed_service_margin_on_azure;

            $window_discount[$key]['three_year']['ri']  = $value['three_year'] + $adjusted_discount_when_buying_reserved_instances - $adjusted_managed_service_margin_on_azure;
            $window_discount[$key]['hybrid']['ri']      = $value['hybrid'] + $adjusted_discount_when_buying_reserved_instances - $adjusted_managed_service_margin_on_azure;

            $RI_weight = 0;
            $total_allocated_weight = 0;

            foreach($instances_allocation['GP_allocation'] as $GP_allocation_item){
                if($GP_allocation_item['vm_type_name'] == $key){
                    $window_discount[$key]['one_year']['weight']    = $GP_allocation_item['ri_one_year'];
                    $window_discount[$key]['three_year']['weight']  = $GP_allocation_item['ri_three_year'];
                    $window_discount[$key]['hybrid']['weight']      = $GP_allocation_item['ri_hybrid'];

                    // $RI_weight = ($window_discount[$key]['one_year']['ri'] * $window_discount[$key]['one_year']['weight'])
                    //     + ($window_discount[$key]['three_year']['ri'] * $window_discount[$key]['three_year']['weight'])
                    //     + ($window_discount[$key]['hybrid']['ri'] * $window_discount[$key]['hybrid']['weight']);

                    //$total_allocated_weight = $window_discount[$key]['one_year']['weight'] + $window_discount[$key]['three_year']['weight'] + $window_discount[$key]['hybrid']['weight'];    
                }
            }

            foreach($instances_allocation['MO_allocation'] as $MO_allocation_item){
                if($MO_allocation_item['vm_type_name'] == $key){
                    if(!isset($window_discount[$key]['one_year']['weight']))
                        $window_discount[$key]['one_year']['weight'] = $MO_allocation_item['ri_one_year'];
                    
                    if(!isset($window_discount[$key]['three_year']['weight']))
                        $window_discount[$key]['three_year']['weight'] = $MO_allocation_item['ri_three_year'];
                    
                    if(!isset($window_discount[$key]['hybrid']['weight']))
                        $window_discount[$key]['hybrid']['weight'] = $MO_allocation_item['ri_hybrid'];
                }
            }

            //set RI Weight = 0 if value still NULL
            if(!isset($window_discount[$key]['one_year']['weight']))
                $window_discount[$key]['one_year']['weight'] = 0;
            
            if(!isset($window_discount[$key]['three_year']['weight']))
                $window_discount[$key]['three_year']['weight'] = 0;
            
            if(!isset($window_discount[$key]['hybrid']['weight']))
                $window_discount[$key]['hybrid']['weight'] = 0;

            $RI_weight = ($window_discount[$key]['one_year']['ri'] * $window_discount[$key]['one_year']['weight']) 
                + ($window_discount[$key]['three_year']['ri'] * $window_discount[$key]['three_year']['weight']) 
                + ($window_discount[$key]['hybrid']['ri'] * $window_discount[$key]['hybrid']['weight']);    

            $total_allocated_weight = $window_discount[$key]['one_year']['weight'] 
                                    + $window_discount[$key]['three_year']['weight'] 
                                    + $window_discount[$key]['hybrid']['weight'];

            $window_discount[$key]['ri-weight'] = $RI_weight;
            $window_discount[$key]['total-allocated-weight'] = $total_allocated_weight;
        }

        $windows_total_ri_one_year = 0;
        $windows_total_ri_three_year = 0;
        $windows_total_ri_hybrid = 0; //dd($window_discount);
        
        $average_count_one_year = 0;
        $average_count_three_year = 0;
        $average_count_hybrid = 0;
        foreach($window_discount as $key => $val)
        {
            $one_year_weight = 0;
            $three_year_weight = 0;
            $hybrid_weight = 0;

            if(isset($val['one_year']['weight']))
                $one_year_weight = $val['one_year']['weight'];

            if(isset($val['three_year']['weight']))
                $three_year_weight = $val['three_year']['weight'];

            if(isset($val['hybrid']['weight']))
                $hybrid_weight = $val['hybrid']['weight'];

            if($one_year_weight > 0){
                $windows_total_ri_one_year += $one_year_weight;
                $average_count_one_year += 1;
            }
            if($three_year_weight > 0){
                $windows_total_ri_three_year += $three_year_weight;
                $average_count_three_year += 1;
            }
            if($three_year_weight > 0){
                $windows_total_ri_hybrid += $hybrid_weight;
                $average_count_hybrid += 1;
            }
        }

        $windows_average_weight_ri = array();
        $windows_average_weight_ri_count = 0;
        
        $windows_average_weight_ri['total_weight_one_year'] = ($average_count_one_year > 0)?($windows_total_ri_one_year / $average_count_one_year):0;
        $windows_average_weight_ri['total_weight_three_year'] = ($average_count_three_year > 0) ? ($windows_total_ri_three_year / $average_count_three_year): 0;
        $windows_average_weight_ri['total_weight_hybrid'] = ($average_count_hybrid > 0)?($windows_total_ri_hybrid / $average_count_hybrid): 0;
        
        if ($windows_average_weight_ri['total_weight_one_year'])
            $windows_average_weight_ri_count++;
        if ($windows_average_weight_ri['total_weight_three_year'])
            $windows_average_weight_ri_count++;
        if ($windows_average_weight_ri['total_weight_hybrid'])
            $windows_average_weight_ri_count++;
        
        $windows_average_weight_ri['average_weight'] = $windows_average_weight_ri_count > 0?($windows_average_weight_ri['total_weight_one_year'] + $windows_average_weight_ri['total_weight_three_year'] + $windows_average_weight_ri['total_weight_hybrid']) / $windows_average_weight_ri_count:0;

        //LINUX
        $linux_discount = array();
        foreach($azure_RI_instance_discounts['linux'] as $key => $value)
        {
            $linux_discount[$key]['vm_type_id'] = $value['vm_type_id'];

            $linux_discount[$key]['one_year']['ri'] = $value['one_year'] + $adjusted_discount_when_buying_reserved_instances - $adjusted_managed_service_margin_on_azure;
            $linux_discount[$key]['three_year']['ri'] = $value['three_year'] + $adjusted_discount_when_buying_reserved_instances - $adjusted_managed_service_margin_on_azure;
            $linux_discount[$key]['hybrid']['ri'] = $value['hybrid'] + $adjusted_discount_when_buying_reserved_instances - $adjusted_managed_service_margin_on_azure;

            $RI_weight = 0;
            $total_allocated_weight = 0;
            foreach($instances_allocation['GP_allocation'] as $GP_allocation_item)
            {
                if($GP_allocation_item['vm_type_name'] == $key){
                    $linux_discount[$key]['one_year']['weight']    = $GP_allocation_item['ri_one_year'];
                    $linux_discount[$key]['three_year']['weight']  = $GP_allocation_item['ri_three_year'];
                    $linux_discount[$key]['hybrid']['weight']      = $GP_allocation_item['ri_hybrid'];
                }
            }

            foreach($instances_allocation['MO_allocation'] as $MO_allocation_item)
            {
                if($MO_allocation_item['vm_type_name'] == $key){
                    if(!isset($linux_discount[$key]['one_year']['weight']))
                        $linux_discount[$key]['one_year']['weight'] = $MO_allocation_item['ri_one_year'];
                    
                    if(!isset($linux_discount[$key]['three_year']['weight']))
                        $linux_discount[$key]['three_year']['weight'] = $MO_allocation_item['ri_three_year'];
                    
                    if(!isset($linux_discount[$key]['hybrid']['weight']))
                        $linux_discount[$key]['hybrid']['weight'] = $MO_allocation_item['ri_hybrid'];

                    if ($key == 'Bms') {
                        $linux_discount[$key]['one_year']['weight']   = $MO_allocation_item['ri_one_year'];
                        $linux_discount[$key]['three_year']['weight'] = $MO_allocation_item['ri_three_year'];
                        $linux_discount[$key]['hybrid']['weight']     = $MO_allocation_item['ri_hybrid'];   
                    }
                }
            }

            //set RI Weight = 0 if value still NULL
            if(!isset($linux_discount[$key]['one_year']['weight']))
                $linux_discount[$key]['one_year']['weight'] = 0;
            if(!isset($linux_discount[$key]['three_year']['weight']))
                $linux_discount[$key]['three_year']['weight'] = 0;            
            if(!isset($linux_discount[$key]['hybrid']['weight']))
                $linux_discount[$key]['hybrid']['weight'] = 0;
            else
                $linux_discount[$key]['hybrid']['weight'] = 0;
            // FOR LINUX - Not Calculate Hybrid
            $RI_weight = ($linux_discount[$key]['one_year']['ri'] * $linux_discount[$key]['one_year']['weight']) 
                            + ($linux_discount[$key]['three_year']['ri'] * $linux_discount[$key]['three_year']['weight']); 
                            //+ ($linux_discount[$key]['hybrid']['ri'] * $linux_discount[$key]['hybrid']['weight']);
            
            $total_allocated_weight = $linux_discount[$key]['one_year']['weight'] 
                                    + $linux_discount[$key]['three_year']['weight'];
                                   // + $linux_discount[$key]['hybrid']['weight'];
            
            $linux_discount[$key]['ri-weight'] = $RI_weight;
            $linux_discount[$key]['total-allocated-weight'] = $total_allocated_weight;
        }
        
        //return
        $reserved_instance_discounts_customer = array();
        $reserved_instance_discounts_customer['windows'] = $window_discount;
        $reserved_instance_discounts_customer['linux'] = $linux_discount;
        $reserved_instance_discounts_customer['average_weight_reversed_instances'] = $windows_average_weight_ri;

        return $reserved_instance_discounts_customer;
    }

    public function reserved_instance_pre_payment($survey_info, $region)
    {
        $dwaModel = new DashboardCalculation();
        $azureCalculation = new AzureCostComparison();

        $input_pricing_variables = $azureCalculation->Input_Of_Pricing_Variables($survey_info); //dd($input_pricing_variables);

        $calculate_price_difference_result_reserved_instance_allocation_WIN = $dwaModel->Calculation_Price_Difference_As_Result_Of_Reserved_Instance_Allocation($survey_info, $region, 'Windows');
        $calculate_price_difference_result_reserved_instance_allocation_LINUX = $dwaModel->Calculation_Price_Difference_As_Result_Of_Reserved_Instance_Allocation($survey_info, $region, 'Linux');

        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $dwaModel->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($survey_info, $region);
        //dd($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost);

        $totals_presented_pre_payments = array();
        $totals_presented_pre_payments['one_year']   = ($calculate_price_difference_result_reserved_instance_allocation_WIN['average_price_GP']['average_ri_one_year_GP']
                                                        + $calculate_price_difference_result_reserved_instance_allocation_WIN['average_price_MO']['average_ri_one_year_MO']
                                                        + $calculate_price_difference_result_reserved_instance_allocation_LINUX['average_price_GP']['average_ri_one_year_GP']
                                                        + $calculate_price_difference_result_reserved_instance_allocation_LINUX['average_price_MO']['average_ri_one_year_MO']) 
                                                        * (1 - (float)$input_pricing_variables['discount_when_buying_reserved_instances']->adjusted_value) * (1 + (float)$input_pricing_variables['managed_service_margin_on_azure']->adjusted_value);

        $totals_presented_pre_payments['three_year'] = ($calculate_price_difference_result_reserved_instance_allocation_WIN['average_price_GP']['average_ri_three_year_GP']
                                                        + $calculate_price_difference_result_reserved_instance_allocation_WIN['average_price_MO']['average_ri_three_year_MO']
                                                        + $calculate_price_difference_result_reserved_instance_allocation_LINUX['average_price_GP']['average_ri_three_year_GP']
                                                        + $calculate_price_difference_result_reserved_instance_allocation_LINUX['average_price_MO']['average_ri_three_year_MO']) 
                                                        * (1 - (float)$input_pricing_variables['discount_when_buying_reserved_instances']->adjusted_value) * (1 + (float)$input_pricing_variables['managed_service_margin_on_azure']->adjusted_value);
        
        $totals_presented_pre_payments['hybrid']     = ($calculate_price_difference_result_reserved_instance_allocation_WIN['average_price_GP']['average_ri_hybrid_GP']
                                                        + $calculate_price_difference_result_reserved_instance_allocation_WIN['average_price_MO']['average_ri_hybrid_MO']
                                                        + $calculate_price_difference_result_reserved_instance_allocation_LINUX['average_price_GP']['average_ri_hybrid_GP']
                                                        + $calculate_price_difference_result_reserved_instance_allocation_LINUX['average_price_MO']['average_ri_hybrid_MO']) 
                                                        * (1 - (float)$input_pricing_variables['discount_when_buying_reserved_instances']->adjusted_value) * (1 + (float)$input_pricing_variables['managed_service_margin_on_azure']->adjusted_value);

        $totals_presented_pre_payments['total'] = $totals_presented_pre_payments['one_year'] + $totals_presented_pre_payments['three_year'] + $totals_presented_pre_payments['hybrid'];
//dd($totals_presented_pre_payments);

        $buyers_net_pre_payment = array();
        $buyers_net_pre_payment['one_year'] = ($calculate_price_difference_result_reserved_instance_allocation_WIN['average_price_GP']['average_ri_one_year_GP']
                                                + $calculate_price_difference_result_reserved_instance_allocation_WIN['average_price_MO']['average_ri_one_year_MO']
                                                + $calculate_price_difference_result_reserved_instance_allocation_LINUX['average_price_GP']['average_ri_one_year_GP']
                                                + $calculate_price_difference_result_reserved_instance_allocation_LINUX['average_price_MO']['average_ri_one_year_MO']) 
                                                * (1 - (float)$input_pricing_variables['discount_when_buying_reserved_instances']->input_value);

        $buyers_net_pre_payment['three_year'] = ($calculate_price_difference_result_reserved_instance_allocation_WIN['average_price_GP']['average_ri_three_year_GP']
                                                + $calculate_price_difference_result_reserved_instance_allocation_WIN['average_price_MO']['average_ri_three_year_MO']
                                                + $calculate_price_difference_result_reserved_instance_allocation_LINUX['average_price_GP']['average_ri_three_year_GP']
                                                + $calculate_price_difference_result_reserved_instance_allocation_LINUX['average_price_MO']['average_ri_three_year_MO']) 
                                                * (1 - (float)$input_pricing_variables['discount_when_buying_reserved_instances']->input_value);

        $buyers_net_pre_payment['hybrid']     = ($calculate_price_difference_result_reserved_instance_allocation_WIN['average_price_GP']['average_ri_hybrid_GP']
                                                + $calculate_price_difference_result_reserved_instance_allocation_WIN['average_price_MO']['average_ri_hybrid_MO']
                                                + $calculate_price_difference_result_reserved_instance_allocation_LINUX['average_price_GP']['average_ri_hybrid_GP']
                                                + $calculate_price_difference_result_reserved_instance_allocation_LINUX['average_price_MO']['average_ri_hybrid_MO']) 
                                                * (1 - (float)$input_pricing_variables['discount_when_buying_reserved_instances']->input_value);

        $buyers_net_pre_payment['total'] = $buyers_net_pre_payment['one_year'] + $buyers_net_pre_payment['three_year'] + $buyers_net_pre_payment['hybrid'];

        $buyers_pre_payment_after_benefit = array();
        
        if((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['vms_including_all_other_costs_except_storage']['benefit_by_paying_real_usage_per_hour'] != 0)
        {
            $buyers_pre_payment_after_benefit['one_year']   = (float)$buyers_net_pre_payment['one_year'] * ((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['vms_including_all_other_costs_except_storage']['benefit_by_optimizing_utilization_of_the_processor_capacity'] / (float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['vms_including_all_other_costs_except_storage']['benefit_by_paying_real_usage_per_hour']);
            $buyers_pre_payment_after_benefit['three_year'] = (float)$buyers_net_pre_payment['three_year'] * ((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['vms_including_all_other_costs_except_storage']['benefit_by_optimizing_utilization_of_the_processor_capacity'] / (float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['vms_including_all_other_costs_except_storage']['benefit_by_paying_real_usage_per_hour']);
            $buyers_pre_payment_after_benefit['hybrid']     = (float)$buyers_net_pre_payment['hybrid'] * ((float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['vms_including_all_other_costs_except_storage']['benefit_by_optimizing_utilization_of_the_processor_capacity'] / (float)$comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['vms_including_all_other_costs_except_storage']['benefit_by_paying_real_usage_per_hour']);
        }
        else{ // avoid PHP error : devide by Zero
            $buyers_pre_payment_after_benefit['one_year']   = 0;
            $buyers_pre_payment_after_benefit['three_year'] = 0;
            $buyers_pre_payment_after_benefit['hybrid']     = 0;
        }

        $buyers_pre_payment_after_benefit['total']      = (float)$buyers_pre_payment_after_benefit['one_year'] + (float)$buyers_pre_payment_after_benefit['three_year'] + (float)$buyers_pre_payment_after_benefit['hybrid'];
        

        //return
        $reserved_instance_pre_payment = array();
        $reserved_instance_pre_payment['totals_presented_pre_payments'] = $totals_presented_pre_payments;
        $reserved_instance_pre_payment['buyers_net_pre_payment'] = $buyers_net_pre_payment;
        $reserved_instance_pre_payment['buyers_pre_payment_after_benefit'] = $buyers_pre_payment_after_benefit;
        return $reserved_instance_pre_payment;
    }

    public function adjusted_partner_benefits($survey_info, $region)
    {
        $azureCalculation = new AzureCostComparison();
        $dwaModel = new DashboardCalculation();
        $input_pricing_variables = $azureCalculation->Input_Of_Pricing_Variables($survey_info); //dd($input_pricing_variables);

        $cost_price_of_customer_required_infrastructure = $dwaModel->Cost_Price_of_Customer_Required_Infrastructure($survey_info, $region);
        $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost = $dwaModel->Comparison_Customer_Overall_Infrastructure_Costs_And_Azure_Infrastructure_Of_This_Capacity_Cost($survey_info, $region);
        //dd($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost);
        //C113
        $total_azure_net = $cost_price_of_customer_required_infrastructure['total_azure_net_cost']['windows'] + $cost_price_of_customer_required_infrastructure['total_azure_net_cost']['linux']; //+ $cost_price_of_customer_required_infrastructure['vms_under_ASR']['azure_net_cost'];
        
        //D141
        $storage_azure_base_cost                        = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['storage_cost']['azure_base_cost'];
        $storage_benefit_optimizing_processor_capacity  = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['storage_cost']['benefit_by_optimizing_utilization_of_the_processor_capacity'];

        $vms_azure_base_cost                            = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['vms_including_all_other_costs_except_storage']['azure_base_cost'];
        $vms_benefit_optimizing_processor_capacity      = $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['vms_including_all_other_costs_except_storage']['benefit_by_optimizing_utilization_of_the_processor_capacity'];

        $CSPDisc            = $input_pricing_variables['applicable_CSP_or_EA_discount']->adjusted_value;
        $CSPRebate          = $input_pricing_variables['applicable_CSP_or_EA_rebate']->adjusted_value;

        $input_CSPDisc      = $input_pricing_variables['applicable_CSP_or_EA_discount']->input_value;
        $input_CSPRebate    = $input_pricing_variables['applicable_CSP_or_EA_rebate']->input_value;

        $managed_service_margin_on_azure = $input_pricing_variables['managed_service_margin_on_azure']->adjusted_value;

        
        $VMs_brut_azure_cost_price                  = $total_azure_net / (1 - ($CSPDisc + $CSPRebate)) * (1 - $managed_service_margin_on_azure);
        $VMs_azure_net_buy_price_partner            = $VMs_brut_azure_cost_price * (1 - ($input_CSPDisc + $input_CSPRebate));
        $Vms_buyer_net_after_azure_benefit          = ($vms_azure_base_cost > 0)?($VMs_azure_net_buy_price_partner * ($vms_benefit_optimizing_processor_capacity / $vms_azure_base_cost)) : 0;
        $Vms_buyer_net_for_RI_before_azure_benefit  = $cost_price_of_customer_required_infrastructure['total_cost_compare']['buyer_for_RI'];
        $Vms_buyer_net_for_RI_after_azure_benefit   = $Vms_buyer_net_for_RI_before_azure_benefit - ($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['vms_including_all_other_costs_except_storage']['azure_base_cost_adjusted_by_RI'] - $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['vms_including_all_other_costs_except_storage']['optimizing_effect_on_the_RI_correct_costs']);


        $Storage_brut_azure_cost_price              = $storage_azure_base_cost / (1 - ($CSPDisc + $CSPRebate)) * (1 - $managed_service_margin_on_azure);
        
        $Storage_azure_net_buy_price_partner        = ($Storage_brut_azure_cost_price > 0) ? ($Storage_brut_azure_cost_price * (1 - ($input_CSPDisc + $input_CSPRebate))) : 0;
        
        $Storage_buyer_net_after_azure_benefit      = ($Storage_azure_net_buy_price_partner > 0) ? ($Storage_azure_net_buy_price_partner * ($storage_benefit_optimizing_processor_capacity / $storage_azure_base_cost)) : 0;
        
        $Storage_buyer_for_RI_before_azure_benefit  = $Storage_azure_net_buy_price_partner;
        
        $Storage_buyer_for_RI_after_azure_benefit   = ($Storage_azure_net_buy_price_partner > 0) ? ($Storage_azure_net_buy_price_partner * ($comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['storage_cost']['benefit_by_optimizing_utilization_of_the_processor_capacity'] / $comparison_customer_infrastructure_costs_and_azure_infrastructure_capacity_cost['storage_cost']['benefit_by_paying_real_usage_per_hour'])) : 0;

        //return
        $adjusted_partner_benefits = array();
        $adjusted_partner_benefits['vms']['brut_azure_cost_price']                      = $VMs_brut_azure_cost_price;
        $adjusted_partner_benefits['vms']['azure_net_buy_price_partner']                = $VMs_azure_net_buy_price_partner;
        $adjusted_partner_benefits['vms']['buyer_net_after_azure_benefit']              = $Vms_buyer_net_after_azure_benefit;
        $adjusted_partner_benefits['vms']['buyer_net_for_RI_before_azure_benefit']      = $Vms_buyer_net_for_RI_before_azure_benefit;
        $adjusted_partner_benefits['vms']['buyer_net_for_RI_after_azure_benefit']       = $Vms_buyer_net_for_RI_after_azure_benefit;

        $adjusted_partner_benefits['storage']['brut_azure_cost_price']                  = $Storage_brut_azure_cost_price;
        $adjusted_partner_benefits['storage']['azure_net_buy_price_partner']            = $Storage_azure_net_buy_price_partner;
        $adjusted_partner_benefits['storage']['buyer_net_after_azure_benefit']          = $Storage_buyer_net_after_azure_benefit;
        $adjusted_partner_benefits['storage']['buyer_net_for_RI_before_azure_benefit']  = $Storage_buyer_for_RI_before_azure_benefit;
        $adjusted_partner_benefits['storage']['buyer_net_for_RI_after_azure_benefit']   = $Storage_buyer_for_RI_after_azure_benefit;

        $adjusted_partner_benefits['total']['brut_azure_cost_price']                    = $VMs_brut_azure_cost_price + $Storage_brut_azure_cost_price;
        $adjusted_partner_benefits['total']['azure_net_buy_price_partner']              = $VMs_azure_net_buy_price_partner + $Storage_azure_net_buy_price_partner;
        $adjusted_partner_benefits['total']['buyer_net_after_azure_benefit']            = $Vms_buyer_net_after_azure_benefit + $Storage_buyer_net_after_azure_benefit;
        $adjusted_partner_benefits['total']['buyer_net_for_RI_before_azure_benefit']    = $Vms_buyer_net_for_RI_before_azure_benefit + $Storage_buyer_for_RI_before_azure_benefit;
        $adjusted_partner_benefits['total']['buyer_net_for_RI_after_azure_benefit']     = $Vms_buyer_net_for_RI_after_azure_benefit + $Storage_buyer_for_RI_after_azure_benefit;
        
        return $adjusted_partner_benefits;
    }

}
